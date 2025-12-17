# ADR 001 - Guardia de rutas por rol (frontend)

Fecha: 2025-12-12
Estado: Propuesto / Implementado (stub)
Contexto: Next.js App Router (admin/provider/app), sin endpoint de auth listo.

## Decisión

- Usar **middleware** (`src/middleware.ts`) para redirigir temprano a los login por segmento (`/admin/login`, `/provider/login`, `/app/login`) cuando falte cookie de sesión.
- Proveer **helper server-side** `getSessionOrRedirect(requiredRole)` en `src/lib/auth.ts` para proteger layouts/páginas bajo segmentos con rol específico.
- Crear un **layout protegido** para admin en `src/app/admin/(protected)/layout.tsx`; futuras páginas protegidas deben ubicarse dentro de ese grupo.
- Añadir **loading/error** segmentados en `src/app/admin/loading.tsx` y `src/app/admin/error.tsx` para estados de carga y fallo.

## Motivación

- Evitar CSR excesivo y controlar acceso en el borde (middleware) y en el server render (helper).
- Preparar la app para roles múltiples sin acoplarse aún al backend.
- Mantener UX consistente durante carga/errores en el segmento admin.

## Implementación actual (stub)

- `middleware.ts`: si no hay cookie `sdjr_session` y la ruta no es pública, redirige al login del segmento. Soporta `redirectTo` en query para volver tras login.
- `lib/auth.ts`: `getSessionOrRedirect` usa stub `fetchSession` (dev) y redirige si falta sesión o rol no coincide. `NEXT_PUBLIC_BYPASS_AUTH=true` permite omitir guardado en local.
- `admin/(protected)/layout.tsx`: llama a `getSessionOrRedirect("admin")`; cualquier página bajo `(protected)` queda protegida.
- `admin/loading.tsx` y `admin/error.tsx`: vistas segmentadas de carga/error con estilos alineados al login.

## Futuro / Cómo conectar backend

1) Reemplazar `fetchSession` en `lib/auth.ts` por llamada real a `/api/me` (Laravel). Validar rol y retornar usuario.
2) En `middleware.ts`, validar token/cookie ligera (sin fetch pesado) o reutilizar firma JWT para evitar round-trip.
3) En login real, setear cookie `sdjr_session` (y opcional `sdjr_role`, `sdjr_email`) para que middleware+helper funcionen.
4) Quitar `NEXT_PUBLIC_BYPASS_AUTH` en ambientes productivos.

## Manual de uso

- Páginas públicas de admin: manténganse fuera de `(protected)` (ej. `/admin/login`).
- Páginas protegidas de admin: colócalas dentro de `src/app/admin/(protected)/...` para heredar el layout con `getSessionOrRedirect`.
- Middleware: no requiere cambios por página; sólo asegurar rutas de login existen. Para tests locales sin auth, exporta `NEXT_PUBLIC_BYPASS_AUTH=true`.
- Redirecciones post-login: usar `redirectTo` query si viene desde middleware; de lo contrario, default a `/admin/dashboard`.

## Consecuencias

- Sin cookie de sesión, las rutas protegidas redirigen al login del segmento.
- Mientras no haya backend, el stub puede ser bypassed; activar auth real cuando endpoint esté listo.
- Middleware afecta también `/provider/*` y `/app/*` aunque no existan páginas todavía (no rompiente, solo redirige a login correspondiente).
