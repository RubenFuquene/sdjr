# Datos de catálogo geográfico

Datos canónicos (país → departamento → ciudad → barrio) que consumen los seeders
`CountrySeeder`, `DepartmentSeeder`, `CitySeeder` y `NeighborhoodSeeder` vía `CatalogSeeder`.

**Principio:** los datos viven aquí, **separados del código** del seeder. Agregar territorio =
agregar filas a estos archivos, sin tocar PHP. El seeder resuelve las relaciones por **clave
natural** (`code`) y hace `upsert` idempotente por lotes.

## Archivos y esquema

| Archivo | Campos | Padre (clave natural) |
|---|---|---|
| `countries.json` | `code`, `name` | — |
| `departments.json` | `code`, `name`, `country_code` | `country_code` → `countries.code` |
| `cities.json` | `code`, `name`, `department_code` | `department_code` → `departments.code` |
| `neighborhoods.json` | `code`, `name`, `city_code` | `city_code` → `cities.code` |

Notas:
- **`status` no se incluye**: es estado operativo, no dato de catálogo. El seeder lo pone activo
  al insertar y **no lo sobrescribe** en updates (para no resetear toggles de admin).
- **Nombres en title-case** (nombres propios): conectores en minúscula (`de`, `del`, `la`, `y`…),
  números romanos en mayúscula (`San Blas II`). Se normalizan al generar el archivo.

## Alcance actual (MVP)

Solo **Bogotá D.C.** (SCRUM-258):
- 1 país (Colombia `CO`), 1 depto (Bogotá D.C. `11`), 1 ciudad (Bogotá D.C. `11001`).
- **1.091 barrios** = sectores catastrales urbanos (tipo 0) + mixtos (tipo 2). Se excluyen los
  rurales/veredas (tipo 1).

La arquitectura soporta expandir a toda Colombia (32 deptos + ~1.100 municipios) reemplazando/
ampliando estos archivos, sin cambios de código.

## Fuentes oficiales

### Departamentos y municipios — DANE DIVIPOLA
- Dataset: https://www.datos.gov.co/widgets/gdxc-w37w  (Socrata)
- API: `https://www.datos.gov.co/resource/gdxc-w37w.json`
- Mapeo: `cod_dpto→departments.code`, `dpto→departments.name`, `cod_mpio→cities.code`,
  `nom_mpio→cities.name`. Bogotá D.C. = depto `11`, municipio `11001`.

### Barrios — IDECA / Catastro Distrital (Sector Catastral)
- Dataset: https://datosabiertos.bogota.gov.co/dataset/sector-catastral
- Servicio ArcGIS REST (capa 0):
  `https://serviciosgis.catastrobogota.gov.co/arcgis/rest/services/catastro/sectorcatastral/MapServer/0`
- Campos: `SCACODIGO` (código, 6 chars → `neighborhoods.code`), `SCANOMBRE` (nombre),
  `SCATIPO` (0=urbano, 1=rural, 2=mixto).
- **Licencia CC BY 4.0 — requiere atribución a la Unidad Administrativa Especial de Catastro
  Distrital (IDECA).** Actualización trimestral.

## Regeneración

Los barrios se obtienen del ArcGIS REST sin geometría (solo atributos) y se normalizan:

```
GET .../sectorcatastral/MapServer/0/query
    ?where=1=1
    &outFields=SCACODIGO,SCANOMBRE,SCATIPO
    &returnGeometry=false
    &orderByFields=SCACODIGO
    &f=json
```

Luego: filtrar `SCATIPO ∈ {0,2}`, normalizar `SCANOMBRE` a title-case, mapear a
`{ code, name, city_code:"11001" }`, ordenar por `code`.

Los departamentos/municipios se toman del endpoint Socrata de DIVIPOLA (filtrando `cod_dpto=11`
para el MVP) y se normalizan igual.
