## Sumass Frontend

Frontend unificado para panel administrativo, panel de proveedores y app del cliente. Construido con Next.js 16 (App Router) + React 19, Tailwind v4 y TypeScript estricto.

## Desarrollo local (Docker first)

1. `cd app/infra`
2. `docker-compose up -d`
3. Frontend disponible en [http://localhost:3000](http://localhost:3000)

Comandos útiles (siempre desde `app/infra/`):

```bash
docker-compose exec frontend npm run lint
docker-compose exec frontend npm run type-check
docker-compose exec frontend npm run test
docker-compose exec frontend npm run build
```

> No ejecutes `npm run dev` ni installs en el host; todo ocurre dentro del contenedor `frontend`.

## Variables relevantes

- `NEXT_PUBLIC_API_URL`: apunta al backend Laravel en Docker (`http://backend:8000` en contenedor, `http://localhost:8000` desde el host).

## Estructura clave

```
app/frontend/
├── public/
├── src/
│   ├── app/              # Rutas App Router agrupadas por rol
│   ├── components/
│   │   └── admin/
│   │       ├── layout/   # Shells y layouts protegidos
│   │       ├── shared/   # Componentes atómicos reutilizables
│   │       ├── management/
│   │       │   ├── perfiles/
│   │       │   ├── proveedores/
│   │       │   ├── usuarios/
│   │       │   └── administradores/
│   │       ├── parametrizacion/
│   │       ├── validacion-proveedores/
│   │       ├── marketing/
│   │       ├── analytics/
│   │       └── soporte/
│   ├── hooks/
│   ├── lib/
│   └── types/
└── ...
```

## Organización del panel admin

- `layout/`: componentes estructurales como `DashboardShell`.
- `shared/`: badges, actions, estados vacíos/carga, etc. consumidos por todos los módulos.
- `management/`: vistas tabulares agrupadas por dominio (Perfiles, Proveedores, Usuarios, Administradores). Cada carpeta contiene filtros, tablas y componentes específicos del módulo.
- Carpetas vacías (`parametrizacion`, `validacion-proveedores`, `marketing`, `analytics`, `soporte`) sirven como anclas para las próximas entregas y mantienen el IA del sidebar.
- Barrel `components/admin/index.ts` re-exporta los módulos principales para evitar imports frágiles.

## Diseño y assets

- Paleta y tokens oficiales en `design-reference/src/COLORES_ACTUALIZADOS.md`.
- Componentes de referencia exportados desde Figma en `design-reference/src/components/`.
- Assets (logos, ilustraciones) disponibles en `design-reference/src/assets/` y deben copiarse a `public/brand/` cuando se utilicen.

## Buenas prácticas

- Preferir Server Components y data fetching directo desde los endpoints Laravel.
- Marcar `"use client"` solo cuando exista interacción o estado local de UI.
- Mantener los estilos alineados con las medidas del diseño (alturas, radios, sombras).
- Agregar loading/error states (`loading.tsx`, `error.tsx` o componentes compartidos) para cada vista que llame a la API.

Para más contexto revisa `docs/architecture.md` y `docs/development.md`.
