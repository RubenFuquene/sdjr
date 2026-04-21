# ADR 004 - Ubicacion de hooks: feature-local vs global

## Estado

Accepted (Frontend MVP)

## Fecha

2026-04-06

## Contexto

En el proyecto conviven dos formas de organizar hooks:

- Hooks globales en `src/hooks`
- Hooks definidos junto al componente/feature

Durante el refactor de productos se introdujo `useProductFormState` dentro del feature (`components/provider/products/form`) porque su logica esta fuertemente acoplada al formulario.

Se necesita una regla clara para decidir ubicacion de hooks y evitar decisiones ad-hoc.

## Decision

Adoptar una estrategia hibrida con una regla por defecto:

1. Hook feature-local por defecto:
   - Si el hook sirve a un feature/componente especifico y depende de su modelo/flujo, se crea junto al feature.

2. Hook global por evidencia de reutilizacion:
   - Si el hook tiene al menos dos consumidores reales en features/paginas distintas y mantiene API estable, se promueve a `src/hooks`.

## Criterios de promocion a global

Promover un hook a `src/hooks` cuando cumpla al menos 2 de 3:

1. Se usa en mas de un componente/pagina de distinto feature.
2. No depende de tipos internos del componente original.
3. Tiene valor como API de dominio reutilizable (no solo de UI puntual).

## Guia de implementacion

### Hook feature-local

- Ubicar en el modulo del feature, por ejemplo:
  - `src/components/provider/products/form/use-product-form-state.ts`
- Exponer por barrel local del feature cuando aplique:
  - `src/components/provider/products/form/index.ts`

### Hook global

- Ubicar en:
  - `src/hooks/...`
- Exportar en el barrel global:
  - `src/hooks/index.ts`

## Consecuencias

### Positivas

- Menor acoplamiento transversal en MVP.
- Mejor legibilidad del feature: estado/eventos cercanos a su UI.
- Escalado ordenado: solo se globaliza lo realmente reutilizable.

### Trade-offs

- Puede haber hooks similares en etapas tempranas antes de consolidar.
- Requiere disciplina para revisar promocion cuando aparezca un segundo consumidor.

## Regla operativa para PRs

En cada PR que introduzca un hook nuevo:

1. Declarar si es feature-local o global.
2. Justificar con los criterios de promocion.
3. Si es global, actualizar `src/hooks/index.ts`.
4. Si es local, exponerlo solo en el barrel del feature (si aplica).

## Aplicacion actual

`useProductFormState` se considera feature-local valido en esta etapa porque:

- Esta acoplado al flujo del formulario de productos.
- Depende de validaciones y mapeos especificos del modulo.
- Aun no tiene un segundo consumidor real.

## Referencias

- `src/components/provider/products/form/use-product-form-state.ts`
- `src/components/provider/products/form/index.ts`
- `src/hooks/index.ts`
