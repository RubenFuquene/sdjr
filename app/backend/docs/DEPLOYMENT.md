# Manual de Despliegue — Backend (Laravel)

> Documento operativo del despliegue del backend en **Railway**. Describe qué corre
> automáticamente en cada deploy, cómo se siembra la base de datos, qué variables de
> entorno se necesitan y cómo diagnosticar fallas. Refleja el comportamiento **real**
> implementado (no aspiracional).

---

## 1. Arquitectura de despliegue

| Pieza | Rol |
|-------|-----|
| **Railway** | Plataforma de hosting. Construye la imagen y publica cada release. |
| **Dockerfile** (`app/backend/Dockerfile`) | Builder oficial. Imagen `php:8.3-cli` con extensiones (`pdo_mysql`, `redis`, `intl`, etc.), Composer y el `docker-entrypoint.sh`. |
| **`railway.json`** | Config-as-code: fija el builder a `DOCKERFILE` y define el **Pre-deploy Command**. |
| **Pre-deploy Command** (`php artisan app:deploy-release`) | Corre **una sola vez por release**, **antes** de exponer la nueva versión. Aquí ocurren migraciones + seeding de catálogo. Si falla, **el deploy se aborta**. |
| **`docker-entrypoint.sh`** | Arranque de **cada réplica** del contenedor: configura BD desde variables de Railway, espera a la BD, genera `APP_KEY` si falta, regenera documentación Swagger y arranca el servidor. **No** corre migraciones ni seeders. |
| **`app:deploy-release`** (`app/Console/Commands/DeployRelease.php`) | Comando Artisan que encapsula el release de BD: `migrate --force` + `db:seed --class=CatalogSeeder --force`. Aborta si cualquier paso falla. |

### Por qué el release de BD vive en el Pre-deploy Command (y no en el entrypoint)

- El **entrypoint corre por cada réplica**. Si las migraciones/seeders vivieran ahí, se
  ejecutarían N veces en paralelo al escalar → condiciones de carrera.
- El **Pre-deploy Command corre una sola vez por release**, de forma serializada y previa
  a la publicación. Es el lugar correcto para mutar el esquema y sembrar datos.
- Si el Pre-deploy falla, Railway **no publica** la versión rota: el servicio sigue
  sirviendo la versión anterior sana.

> ⚠️ **Builder único:** Existe un `nixpacks.toml` heredado en el repo. `railway.json` fija
> el builder a `DOCKERFILE`, por lo que `nixpacks.toml` queda como camino muerto. No se
> usa en el deploy; conviene eliminarlo en una limpieza futura para evitar ambigüedad.

---

## 2. Flujo de un deploy

```
git push / merge a la rama desplegada
        │
        ▼
Railway construye la imagen con el Dockerfile
        │
        ▼
PRE-DEPLOY:  php artisan app:deploy-release
        ├─ php artisan migrate --force         (migraciones pendientes)
        └─ php artisan db:seed --class=CatalogSeeder --force   (catálogo idempotente)
        │
        ├─ ¿falla? → deploy ABORTADO, sigue la versión anterior
        ▼
Se publica la nueva versión
        │
        ▼
ARRANQUE de cada réplica:  docker-entrypoint.sh
        ├─ configura BD desde DATABASE_URL / MYSQL* de Railway
        ├─ espera a la BD (db:show)
        ├─ genera APP_KEY si falta
        ├─ regenera documentación Swagger (l5-swagger:generate)
        └─ php artisan serve  (o scheduler si APP_RUN_MODE=cron)
```

### Primer deploy vs. deploys subsecuentes

**No hay un "modo primer deploy" especial.** El diseño hace que el primer deploy sea un
caso más del flujo idempotente:

| | Primer deploy (BD vacía) | Deploys subsecuentes (BD ya sembrada) |
|---|---|---|
| `migrate --force` | Crea todo el esquema desde cero. | Aplica solo las migraciones nuevas (Laravel rastrea las ya corridas en `migrations`). |
| `CatalogSeeder` | Crea todo el catálogo (roles, permisos, países, bancos, prioridades, superadmin…). | **No duplica** nada: `upsert`/`firstOrCreate` por clave natural. Crea solo entradas nuevas (p. ej. un permiso recién agregado) y deja el resto intacto. |

> El "disparador automático" que se pidió **es esto**: cada deploy corre el release de BD
> idempotente. Una migración nueva se aplica porque `migrate` la detecta; un permiso o
> entrada de catálogo nueva se siembra porque el `CatalogSeeder` la añade vía upsert. No
> hace falta detección manual de "hay un seeder nuevo".

---

## 3. Qué se siembra (y qué NO) automáticamente

### Catálogo — **siempre** (vía `CatalogSeeder`)

Datos de referencia que deben existir en todos los entornos, todos idempotentes:

`RolePermissionSeeder` (roles + permisos), `CountrySeeder`, `DepartmentSeeder`,
`CitySeeder`, `NeighborhoodSeeder`, `EstablishmentTypeSeeder`, `BankSeeder`,
`SupportStatusSeeder`, `PqrsTypeSeeder`, `PriorityTypeSeeder`, `ProductCategorySeeder`,
`UserSeeder` (superadmin base).

### Datos demo — **solo bajo flag** (`DemoSeeder`)

Comercios, productos, órdenes, representantes legales, documentos, comentarios, etc.
**No corren en el deploy.** Solo se generan manualmente con `DEMO_SEEDING=true` (ver §6).

---

## 4. Variables de entorno

Configúralas en **Railway → Service → Variables**. Nunca las comitees con valores reales.

### Credenciales del superadmin base (`UserSeeder`)

| Variable | Default (placeholder) | Notas |
|----------|----------------------|-------|
| `SEED_ADMIN_EMAIL` | `admin@napaapp.com` | Email del superadmin. Clave natural del `firstOrCreate`. |
| `SEED_ADMIN_PASSWORD` | `ChangeMe!Napa2026` | ⚠️ **Placeholder. DEBE sobreescribirse antes del primer deploy.** |
| `SEED_ADMIN_NAME` | `Administrator` | |
| `SEED_ADMIN_LAST_NAME` | `Ñapa App` | |
| `SEED_ADMIN_PHONE` | `3000000000` | |

> El superadmin se crea con `firstOrCreate` por email: una vez creado, re-desplegar **no
> rota la contraseña** (no clobbering). Para cambiarla, hazlo vía la app o recreando el
> usuario; cambiar `SEED_ADMIN_PASSWORD` después del primer deploy **no** la actualiza.

### Seeding

| Variable | Uso |
|----------|-----|
| `DEMO_SEEDING` | `true` para generar datos demo (solo en flujo manual `db:seed`, ver §6). En staging por defecto **no** se activa. |

### Base de datos y servicios (referencia)

Tomadas de `.env.example.prd`. Railway suele inyectar `DATABASE_URL` / `MYSQL*`, que el
`docker-entrypoint.sh` traduce a `DB_*` automáticamente.

| Grupo | Variables |
|-------|-----------|
| App | `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL` |
| BD | `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (o `DATABASE_URL` / `MYSQL*` de Railway) |
| Colas/Caché | `QUEUE_CONNECTION`, `CACHE_STORE`, `QUEUE_PROCESS_*` |
| Redis | `REDIS_CLIENT`, `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD` |
| Mail (Resend) | `MAIL_MAILER`, `MAIL_FROM_ADDRESS`, `RESEND_API_KEY` |
| Almacenamiento (MinIO/S3) | `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, `AWS_ENDPOINT`, `AWS_URL`, `AWS_USE_PATH_STYLE_ENDPOINT` |
| CORS | `CORS_ALLOWED_ORIGINS`, `CORS_SUPPORTS_CREDENTIALS`, `SANCTUM_STATEFUL_DOMAINS` |

> En `APP_ENV=production`/`prod`, el entrypoint **exige** las variables de almacenamiento
> (`AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, `AWS_URL`, `AWS_ENDPOINT`)
> y aborta el arranque si falta alguna.

### Variables **deprecadas** en el deploy (no usarlas)

| Variable / práctica | Por qué ya no participa en el deploy |
|---------------------|--------------------------------------|
| `ENABLE_SEEDING` | Solo gobierna el flujo manual `php artisan db:seed` (vía `DatabaseSeeder`). El deploy llama `db:seed --class=CatalogSeeder` **directo**, sin pasar por ese gate. |
| `FORCE_RESEED` | Solo aplica al flujo manual `DatabaseSeeder` (resetea `SeederControl`). No interviene en el release de BD. |
| `migrate:refresh` (entrypoint viejo) | **Eliminado.** Era destructivo (borraba y recreaba todas las tablas). El deploy ahora solo usa `migrate --force` (no destructivo). |

---

## 5. Runbook / Troubleshooting

### Sembrar el catálogo manualmente (idempotente)

```bash
php artisan db:seed --class=CatalogSeeder --force
```

O el release completo (migraciones + catálogo), idéntico al Pre-deploy:

```bash
php artisan app:deploy-release
```

### Cargar datos demo (solo entornos de prueba)

```bash
DEMO_SEEDING=true ENABLE_SEEDING=true php artisan db:seed --force
# o, si solo quieres los demo:
DEMO_SEEDING=true php artisan db:seed --class=DemoSeeder --force
```

### Verificar el catálogo crítico

```bash
php artisan tinker --execute="
  echo 'priority AL: '.\App\Models\PriorityType::where('code','AL')->exists().PHP_EOL;
  echo 'role superadmin: '.\Spatie\Permission\Models\Role::where('name','superadmin')->exists().PHP_EOL;
  echo 'country CO: '.\App\Models\Country::where('code','CO')->exists().PHP_EOL;
"
```

### Si el Pre-deploy Command falla

1. **El deploy se aborta solo**: el servicio sigue en la versión anterior sana. No hay
   versión rota expuesta.
2. Revisa los **logs del Pre-deploy** en Railway (Deployments → el release fallido).
3. Causas típicas:
   - **BD no alcanzable / credenciales** → revisa `DB_*` / `DATABASE_URL` / `MYSQL*`.
   - **Migración con error** → corrige la migración y vuelve a desplegar.
   - **Choque de unique en seeding** → no debería ocurrir (todo es `upsert`/`firstOrCreate`);
     si pasa, revisa duplicados preexistentes en la tabla afectada (ver Riesgos del plan).
4. Reproduce localmente: `php artisan app:deploy-release` contra una BD limpia y de nuevo
   sobre una ya sembrada (debe pasar dos veces sin error).

> ⚠️ **Formato de `preDeployCommand`:** En `railway.json` se define como string
> (`"php artisan app:deploy-release"`). Si una versión de Railway lo rechaza, envuélvelo en
> array: `"preDeployCommand": ["php artisan app:deploy-release"]`.

---

## 6. Rollback y seguridad

- **Sin pasos destructivos en el deploy.** El release usa `migrate --force` (incremental)
  y seeders idempotentes. **No** se ejecuta `migrate:refresh` ni se borran datos.
- **Rollback de aplicación:** usa el **Redeploy** de una versión anterior en Railway. Ten en
  cuenta que las migraciones de esquema **no** se revierten automáticamente; si una
  migración rompió algo, revierte con una migración correctiva (`migrate:rollback` solo si
  estás seguro de las consecuencias sobre los datos).
- **Datos demo fuera de producción:** `DemoSeeder` nunca corre en el deploy; solo bajo flag
  manual. Producción/staging quedan con catálogo limpio salvo activación explícita.
- **Secretos:** las credenciales (admin base, BD, Resend, MinIO) viven en variables de
  Railway, no en el repo. Este documento solo referencia nombres de variables.
- **Mínimo privilegio:** los roles/permisos se siembran desde `RolePermissionSeeder`; no
  ampliar permisos de `admin`/`provider` sin justificación (OWASP A04).

---

## 7. Checklist antes del primer deploy

- [ ] `SEED_ADMIN_PASSWORD` (y demás `SEED_ADMIN_*`) configurados en Railway con valores reales.
- [ ] Variables de BD válidas (o `DATABASE_URL` / `MYSQL*` inyectadas por Railway).
- [ ] Variables de almacenamiento (`AWS_*`) presentes si `APP_ENV` es production/prod.
- [ ] `railway.json` con builder `DOCKERFILE` y `preDeployCommand` correcto.
- [ ] `DEMO_SEEDING` **sin** activar (a menos que quieras datos de prueba).
- [ ] Verificado que `php artisan app:deploy-release` corre dos veces sin error en local.
