# ADR 003 - Branches: captura de latitud/longitud en MVP

## Estado

Accepted (MVP Provider Branch Form)

## Fecha

2026-03-07

## Contexto

El frontend necesita coordenadas (`latitude`, `longitude`) por sucursal para habilitar en el modulo cliente:

- listado de sucursales cercanas
- visualizacion de sucursales en mapa Leaflet

Actualmente el formulario de sucursal captura direccion textual, pero eso no garantiza coordenadas precisas.

Opciones evaluadas para MVP:

1. Geocoding automatico de direccion.
2. Seleccion manual de ubicacion en mapa por el proveedor.

## Decision

Se decide usar **seleccion manual en mapa** como estrategia principal para capturar `latitude` y `longitude` en MVP.

El geocoding automatico se posterga para una fase posterior como mejora UX, no como requisito inicial.

## Razonamiento

### Positivas

- Mayor precision operativa desde el dia 1.
- Evita dependencia de servicios externos de geocoding y sus costos/cuotas.
- Menor complejidad tecnica y de manejo de errores en MVP.
- Coherente con stack actual de mapas (Leaflet) ya considerado en frontend.

### Trade-offs

- Requiere una accion adicional del proveedor (seleccionar punto en mapa).
- No autocompleta coordenadas desde direccion en esta fase.

## Consecuencias

### Frontend

- `BranchForm` debe incorporar campos de estado `latitude`/`longitude`.
- Validacion local y de submit debe exigir coordenadas.
- Se requiere componente Client para seleccion de punto en mapa (modal o vista dedicada).

### Integracion

- El contrato API de sucursal (create/update) debe transportar `latitude` y `longitude`.
- Si backend aun no persiste estos campos, se mantiene bloqueada la persistencia completa hasta alinear contrato.

## Plan de salida / evolucion

Cuando se quiera reducir friccion en captura:

1. Agregar geocoding inicial desde direccion para sugerir marcador.
2. Mantener ajuste manual del pin como confirmacion final.
3. (Opcional) reverse geocoding para consistencia direccion <-> coordenadas.

## Referencias

- `.vscode/geolocalizacion-sucursales-mvp-decision.md`
- `.vscode/geolocalizacion-mvp-plan.md`
- `.vscode/mapas-mvp-evaluacion.md`
