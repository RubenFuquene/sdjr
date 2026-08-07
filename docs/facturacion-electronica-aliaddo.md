# Facturación Electrónica — Diseño de Integración (Contrato de Mandato)

**Origen:** SCRUM-252. **Proveedor tecnológico:** Aliaddo (API Nitro).
**Estado:** Diseño. Pendiente de contrato con Aliaddo y de revisión jurídica del T&C.
**Última actualización:** 2026-08-07.

## Cómo leer este documento

Ñapa no factura como un comercio normal. Opera bajo **Contrato de Mandato de recaudo** (Art. 438 ET,
Decreto 1625 de 2016): recauda, retiene su comisión y dispersa por cuenta de terceros, facturando
**en nombre de** sus aliados. Eso cambia casi todo respecto a una integración convencional.

- ¿Vas a implementar? Empieza por [Marco Fiscal](#marco-fiscal) y
  [Los documentos que Ñapa emite](#los-documentos-que-ñapa-emite).
- ¿Vas a negociar con Aliaddo? Ve a [Decisiones Abiertas](#decisiones-abiertas).
- ¿Eres contabilidad o legal? Las secciones marcadas con ⚠️ te necesitan.

---

## Marco Fiscal

### Identificación de Ñapa

| Dato                              | Valor                                                                                                                                 |
| --------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------- |
| Razón social                     | ÑAPA S.A.S.                                                                                                                          |
| NIT                               | 902.070.927-3                                                                                                                         |
| Representante legal               | Daniel Felipe Castro Romero                                                                                                           |
| Domicilio                         | Bogotá D.C.                                                                                                                          |
| Casilla 24 (tipo de persona)      | **1** — Persona jurídica                                                                                                      |
| Casilla 53 (responsabilidades)    | **05** (renta régimen ordinario), **07** (retención en la fuente a título de renta), **48** (responsable de IVA) |
| Autorización de numeración DIAN | **18764110603855**                                                                                                              |
| Prefijos                          | **FAN** (factura de venta) y **DSN** (documento soporte)                                                                  |

> **Sobre el código 52 (facturador electrónico):** es normal que un RUT nuevo no lo tenga. La DIAN lo
> agrega automáticamente una vez vinculado el proveedor tecnológico, corrido el set de pruebas y
> aprobada la habilitación. No es un bloqueante previo: es la consecuencia de completarla.

### Qué implica operar bajo mandato

1. **Ñapa emite bajo su propia resolución y firma**, nunca bajo la del aliado. El aliado se identifica
   dentro del documento mediante el bloque `mandante`, que dirige el ingreso y los impuestos del
   producto a su bolsa fiscal.
2. **Ñapa tributa solo sobre su comisión**, no sobre el GMV bruto. Sin contrato de mandato válido, la
   DIAN asumiría que el 100% del dinero que entra es ingreso propio de la plataforma.
3. **Las retenciones que la pasarela le practica a Ñapa no le pertenecen.** Son créditos del aliado y
   deben trasladársele. Ver [Retenciones](#retenciones-y-dispersión).
4. **Ñapa debe emitir una Certificación de Mandato mensual** a cada aliado. Obligación legal.

### Por qué no hay aprovisionamiento por aliado

La **clave técnica** de una resolución DIAN solo la puede generar su titular desde su cuenta personal
en **Muisca**. El formato 1876 contiene todo lo demás, pero no la clave técnica.

Si Ñapa emitiera bajo la resolución de cada aliado, cada uno tendría que entrar a Muisca, generarla y
compartirla antes de vender un solo producto. Inviable. Bajo mandato Ñapa usa **su propia** clave
técnica, obtenida una sola vez. **No hay nada que aprovisionar por aliado.**

### Qué pasa con el formato 1876

|                           | ¿Se necesita?                       | Para qué                                                                                                        |
| ------------------------- | ------------------------------------ | ---------------------------------------------------------------------------------------------------------------- |
| **1876 de Ñapa**   | **Sí, imprescindible**        | De ahí salen los prefijos FAN y DSN, número de autorización, rangos y vigencias con los que Ñapa firma todo. |
| **1876 del aliado** | **Ya no como fuente de datos** | No usamos su resolución ni su clave técnica. Sus datos fiscales vienen del**RUT**.                       |

**Pero se mantiene como evidencia** (decisión tomada). Es la prueba documental de que el comercio
efectivamente tiene resolución de facturación electrónica vigente — es decir, de que es Tipo A. Hoy
`StoreDocumentUploadRequest` ya lo exige solo cuando `electronic_invoicing_required` es true.

> ⚠️ La ficha `Campos para obligados y no obligados a facturar.xlsx` **queda obsoleta** (confirmado).
> Modelaba la resolución DIAN del aliado (Tabla 1) y un DSN de comisión (Tabla 2); el modelo de
> mandato invalida ambas.

---

## Clasificación del Comercio: Tipo A / Tipo B

Determina qué documento recibe el comprador. **No es una autodeclaración**: es una regla derivable.

**Tipo B — no obligado a facturar electrónicamente.** Persona natural que cumpla **simultáneamente**:

1. No ser responsable de IVA ni de INC.
2. Operar un **único** establecimiento de comercio.
3. Ingresos anuales **no superiores a 3.500 UVT** (parágrafo 3, art. 437 ET).

**Tipo A — obligado.** Todos los demás: personas jurídicas, régimen SIMPLE, responsables de IVA o INC,
y personas naturales con ingresos superiores a 3.500 UVT. **El incumplimiento de cualquiera de las
tres condiciones de Tipo B convierte al comercio en Tipo A.**

> Los establecimientos del art. 426 ET (restaurantes, cafeterías, panaderías) están excluidos de IVA
> pero gravados con INC. Para ser Tipo B deben cumplir igual las tres condiciones.

> ⚠️ **El campo actual `Commerce.electronic_invoicing_required` es un booleano autodeclarado** que
> nadie valida. La regla de arriba es derivable y auditable. Ver
> [Modelo de Datos](#modelo-de-datos-propuesto).

---

## Los Documentos que Ñapa Emite

### Árbol de decisión

La acción de Ñapa depende **únicamente del tipo del comercio**. El estado del comprador solo decide
cómo se le identifica dentro del documento, nunca qué documento se emite.

```
Orden confirmada
   │
   ├── Comercio Tipo A ──▶ FAN de venta vía mandato (electrónica, va a la DIAN)
   │                        customer = comprador identificado, o "Consumidor Final" (222222222222)
   │
   └── Comercio Tipo B ──▶ Recibo Interno de Compra (NO electrónico, no va a la DIAN)
                            Si el comprador es Tipo A, él emitirá su propio DSN — acción suya.

   Y en ambos casos, en la dispersión: FAN de comisión al comercio.
```

### A. FAN de venta vía mandato — al comprador (solo Tipo A)

| Aspecto              | Valor                                                                                                  |
| -------------------- | ------------------------------------------------------------------------------------------------------ |
| Endpoint             | `POST /v2/documents/invoices`                                                                        |
| Resolución y firma  | Las de**Ñapa** (autorización 18764110603855, prefijo FAN)                                      |
| `customer`         | Comprador identificado, o "Consumidor Final" con NIT genérico`222222222222`                         |
| `lines[].mandante` | El**comercio** — `name`, `identificationTypeCode`, `identificationNumber`, `digitCheck` |
| Impuestos de línea  | Los del**producto** (IVA/INC), imputados a la bolsa fiscal del comercio                          |

El bloque `mandante` va **por línea de producto**, dentro de `lines` — así lo expone Nitro. Es el
mecanismo que dirige ingreso e impuestos al aliado mientras el documento se firma con la resolución
de Ñapa.

### B. FAN de comisión — al comercio (siempre)

Lo que Ñapa cobra por intermediar. Se emite en **todos** los casos, en la dispersión.

| Aspecto                      | Valor                                                                           |
| ---------------------------- | ------------------------------------------------------------------------------- |
| Endpoint                     | `POST /v2/documents/invoices`                                                 |
| `code` / `typeOperation` | `01` / `10`                                                                 |
| Resolución                  | La**FAN de Ñapa**                                                        |
| `customer`                 | El**comercio** (requiere perfil fiscal completo)                          |
| Base                         | Comisión sobre el valor**bruto** de cada venta, según Tarifario vigente |
| Impuesto                     | **19% IVA** sobre la comisión neta                                       |
| `mandante`                 | No aplica — es ingreso propio de Ñapa                                         |

### C. Recibo Interno de Compra — al comprador (solo Tipo B)

No es un documento fiscal ni viaja a la DIAN. Es obligación del **Estatuto del Consumidor** (Ley 1480
de 2011, art. 50): toda plataforma de comercio electrónico debe entregar soporte detallado de la
transacción. Se genera automáticamente y se envía por correo en PDF.

**Contenido obligatorio:**

*Partes*

- Datos de Ñapa: razón social, NIT, dirección, correo — **especificando su calidad de intermediario /
  portal de contacto**.
- Datos del comercio (vendedor real): nombre o razón social y NIT/cédula. **Obligatorio por ley**:
  el comprador debe saber a quién le compra y a quién reclamarle la garantía.
- Datos del comprador: nombre, cédula/NIT, teléfono, dirección de envío.

*Comercial y logística*

- **Consecutivo interno** propio (ej. `REC-000452`). No requiere resolución DIAN.
- Fecha y hora exactas del procesamiento del pago en la pasarela.
- Descripción del producto: nombre, cantidad, especificaciones.

*Desglose económico*

- Precio unitario y total en COP.
- **IVA en cero u omitido.** El comercio Tipo B no es responsable de IVA; el recibo no puede mostrar
  cobro de IVA por el producto.
- Costo de envío desglosado aparte, si aplica.
- Total pagado.

*Cláusulas de protección legal*

- Tiempo máximo de entrega pactado.
- **Derecho de retracto**: 5 días hábiles siguientes a la entrega. Obligatorio en todo e-commerce en
  Colombia.
- **Leyenda de no facturación**, al pie:

  > *"Este documento constituye un recibo interno de compra y soporte de la transacción en la
  > plataforma. No opera como factura de venta debido a que el vendedor se encuentra clasificado
  > legalmente como No Obligado a facturar electrónicamente (Estatuto Tributario, Art. 616-2)."*
  >

**Diferencias frente a la FAN:**

|                    | FAN vía mandato (Tipo A)       | Recibo Interno (Tipo B)                          |
| ------------------ | ------------------------------- | ------------------------------------------------ |
| Encabezado         | "Factura Electrónica de Venta" | "Recibo de Compra" / "Comprobante de Operación" |
| Consecutivo        | Rango oficial del 1876 de Ñapa | Consecutivo plano de la BD                       |
| Elementos visuales | Código QR y CUFE al pie        | Código de barras interno o ID de transacción   |
| Transmisión       | Aliaddo → DIAN                 | Solo correo al comprador, en PDF                 |

### D. El DSN: qué NO es

> ⚠️ **El Documento Soporte NUNCA se usa para pagar o dispersar a los comercios del marketplace.**

El prefijo DSN sirve **estrictamente** para costos operativos internos de Ñapa: comprar servicios o
productos a proveedores no obligados a facturar (por ejemplo, un desarrollador freelance).

La razón es de fondo: al dispersar, Ñapa **no le está comprando nada al comercio** — le está
devolviendo dinero que recaudó por su cuenta. Un DSN documenta una adquisición; aquí no hay ninguna.
Ese flujo queda **fuera del alcance** de esta integración de marketplace.

---

## Retenciones y Dispersión

Hay **dos juegos de retenciones en direcciones opuestas**. Confundirlos causa fugas de caja reales.

### Fase 1 — La pasarela le retiene a Ñapa (recaudo)

Cuando un comprador paga $100.000, la pasarela **no deposita $100.000**. Descuenta retenciones
financieras contra el NIT de Ñapa:

| Retención | Base típica                                                           |
| ---------- | ---------------------------------------------------------------------- |
| ReteIVA    | 15% sobre el valor del IVA de la transacción                          |
| ReteFuente | 1,5% o 4% sobre el precio base, según tipo de producto                |
| ReteICA    | Según municipio (ej. 0,414% o 1,104% en Bogotá) sobre el precio base |

> ⚠️ **Regla de protección legal.** Bajo mandato esas retenciones **no le pertenecen a Ñapa**. La
> pasarela certifica que retuvo contra el NIT de Ñapa, pero conforme al **Artículo 1.6.1.4.9 del
> Decreto 1625 de 2016**, Ñapa debe trasladar esos créditos al comercio vía la Certificación Mensual.
> El comercio los usa para bajar su carga tributaria y Ñapa los deduce de su contabilidad para no
> tributar sobre volumen ajeno.

**Implicación técnica:** hay que **capturar y persistir las retenciones de pasarela transacción por
transacción**. Sin ese dato no hay certificación posible.

### Fase 2 — Ñapa NO retiene en el giro

**El giro al comercio es devolución de fondos recaudados por cuenta del mandante** (Art. 438 ET), no
un pago por servicios. **Por lo tanto no está sujeto a retención en la fuente por parte de Ñapa.**

Que Ñapa tenga el código 07 en su casilla 53 no cambia esto: ser agente retenedor significa retener
cuando se hacen pagos sujetos a retención, y una devolución de fondos bajo mandato no lo es.

### Fase 3 — El comercio le retiene a Ñapa (sobre la comisión)

La dirección inversa. Un comercio que tenga registrada la responsabilidad **código 07** en su RUT debe
practicar **retención en la fuente del 11%** sobre el valor de la comisión de Ñapa —concepto
"Honorarios y comisiones (personas jurídicas)", Art. 392 ET— y certificarle a Ñapa la retención
practicada.

> ⚠️ **Mecánica pendiente de validar con el contador.** Lo habitual es que retenga quien paga, pero
> aquí Ñapa descuenta su comisión de dinero que ya tiene en custodia. La lectura de implementación es:
> si el comercio es agente retenedor, Ñapa descuenta `comisión + IVA − retención`, y el comercio le
> certifica esa retención a Ñapa. Confirmar antes de implementar.

### La fórmula de dispersión

Ejemplo con producto exento de IVA y comisión del 10%:

```
# --- ENTRADAS ---
product_sale_total       = 100_000.00
platform_commission_rate = 0.10
iva_rate                 = 0.19

# --- FASE 1: retenciones de la pasarela (créditos del COMERCIO) ---
gateway_retefuente = product_sale_total * 0.015    # 1.500
gateway_reteica    = product_sale_total * 0.00414  #   414
total_gateway_withholdings = 1.914

# --- INGRESO DE ÑAPA ---
net_commission = product_sale_total * platform_commission_rate  # 10.000
commission_iva = net_commission * iva_rate                      #  1.900
total_platform_invoice = 11.900        # ← la FAN de comisión

# --- FASE 3: solo si el comercio es agente retenedor (código 07) ---
merchant_withholding_credit_to_napa = is_withholding_agent ? net_commission * 0.11 : 0

# --- DISPERSIÓN ---
final_merchant_payout = product_sale_total
                      - total_platform_invoice
                      - total_gateway_withholdings
                      - gateway_operational_fees
                      + merchant_withholding_credit_to_napa    # lo retenido a Ñapa no se le descuenta al comercio
```

**Caso 1 — comercio sin código 07** (no es agente retenedor):

```
final_merchant_payout = 100.000 − 11.900 − 1.914 = 86.186
```

Recibe **$86.186** — su producto ($100.000), menos el servicio de Ñapa ($11.900 = $10.000 de comisión

+ $1.900 de IVA), menos lo que la pasarela ya retuvo ($1.914). A fin de mes Ñapa le certifica esos
  $1.914 para que los reclame ante la DIAN.

**Caso 2 — comercio con código 07** (agente retenedor):

```
merchant_withholding_credit_to_napa = 10.000 * 0.11 = 1.100
final_merchant_payout = 100.000 − 11.900 − 1.914 + 1.100 = 87.286
```

Recibe **$87.286**. Ñapa emite igual su FAN por $11.900, pero solo descuenta $10.800 del saldo, porque
el comercio le retuvo $1.100 sobre la comisión. El comercio queda obligado a entregarle a Ñapa el
**Certificado de Retención**, que Ñapa usa como crédito fiscal propio.

> El signo `+` en la fórmula no es un ingreso extra para el comercio: es dinero que **no se le
> descuenta** porque ya lo retuvo por cuenta de la DIAN.

> ⚠️ **El ejemplo asume producto exento de IVA.** En el catálogo real los productos tienen IVA/INC
> variable, lo que además activa la ReteIVA del 15%. Calcularlo bien **exige** la clasificación fiscal
> por producto — **SCRUM-362**. Ver [Dependencias](#dependencias).

### Periodicidad

**Semanal, los viernes.** Se consolidan los saldos netos acumulados a favor del comercio y se ejecuta
la transferencia. Del saldo se descuentan previamente la factura de comisión, las tarifas operativas y
las retenciones aplicadas por la pasarela durante el recaudo.

---

## Certificación Mensual de Mandato

Obligación legal (Decreto 1625 de 2016, art. 1.6.1.4.9) hacia **cada comercio activo**. Es el
instrumento por el cual los créditos de retención pasan del NIT de Ñapa al del comercio — sin él, el
comercio no puede legalizar el dinero recibido.

| Aspecto    | Requisito                                                                                            |
| ---------- | ---------------------------------------------------------------------------------------------------- |
| Plazo      | Dentro de los primeros**10 días calendario** del mes siguiente al período certificado        |
| Firma      | **Representante Legal o Contador Público**                                                    |
| Naturaleza | *"Único soporte válido para los cruces contables del Comercio ante las autoridades tributarias"* |

**Contenido consolidado del período:**

- Histórico de las **FAN o recibos internos** emitidos en su nombre (consecutivos y CUFEs).
- Monto total de dineros recaudados.
- **Retenciones fiscales aplicadas por las pasarelas que deben trasladarse al comercio**, discriminadas.
- Comisiones cobradas, con sus respectivas facturas de servicios.

Se modela como entidad persistida, no como PDF al vuelo: debe ser reproducible y auditable años
después.

---

## El Contrato de Mandato: cómo se firma

No requiere documento físico ni PDF individual por comercio. Se instrumenta como **contrato de
adhesión** dentro de los Términos y Condiciones del Proveedor. En Colombia el contrato electrónico es
plenamente vinculante (**Ley 527 de 1999**).

**Mecánica:** en el registro del comercio, antes de subir RUT o certificación bancaria, un checkbox
obligatorio: *"Acepto los Términos y Condiciones, la Política de Privacidad y el Contrato de Mandato
para la Intermediación y Facturación de la plataforma"*, con enlace a la página legal donde está el
clausulado.

**Cláusulas que la DIAN exigirá ver:** objeto del mandato (citando Art. 1.6.1.4.9 del Decreto 1625),
remuneración (porcentaje de comisión + FAN con IVA 19%), dispersión (tiempos y condiciones),
responsabilidad tributaria del comercio (declaración juramentada sobre su RUT y obligación de
notificar cambios de estado frente al IVA o al código 52), y certificación mensual.

### Lo que ya existe en el repo

- **SCRUM-214 (Finalizado)** — ya añadió `commerces.terms_accepted_at` y `terms_accepted_version`, más
  el endpoint `POST /api/v1/commerce/{id}/accept-terms`. **El mecanismo de aceptación ya funciona.**
- **SCRUM-187 (Sin iniciar)** — `GET /api/v1/documents/legal`. Su payload de ejemplo ya contempla un
  `SERVICE_AGREEMENT` con `requires_acceptance: true`: **es el hueco natural del Contrato de Mandato**.
- La tabla `legal_documents` ya soporta `type`, `version`, `status` y `effective_date` independientes
  por documento.

### ⚠️ Hueco de versionado

`commerces.terms_accepted_version` es **un solo entero**. Si el Contrato de Mandato es un
`legal_documents.type` distinto del T&C, no se puede registrar de forma independiente qué versión de
cada documento aceptó cada comercio.

Dado que la DIAN puede exigir probar **qué texto exacto de mandato aceptó un comercio y cuándo**, se
recomienda una tabla de aceptaciones (`commerce_legal_acceptances`: `commerce_id`,
`legal_document_id`, `version`, `accepted_at`, `ip`, `user_agent`). La IP y el user-agent importan:
son la evidencia que sostiene la firma electrónica bajo Ley 527.

---

## Modelo de Datos Propuesto

### `dian_resolutions`

Resoluciones de **Ñapa únicamente**.

| Columna                             | Campo Nitro                       | Notas                                                        |
| ----------------------------------- | --------------------------------- | ------------------------------------------------------------ |
| `document_kind`                   | —                                | `fan` \| `dsn`                                           |
| `resolution_key`                  | `resolution.key`                | Clave técnica. Solo obtenible del**Muisca de Ñapa**. |
| `prefix`                          | `resolution.prefix`             | `FAN` \| `DSN`                                           |
| `resolution_number`               | `resolution.number`             |                                                              |
| `range_initial` / `range_final` | `rangeInitial` / `rangeFinal` |                                                              |
| `range_current`                   | →`consecutive`                 |                                                              |
| `valid_from` / `valid_until`    | `validFrom` / `validUntil`    | Monitoreo de vencimiento                                     |
| `is_active`                       | —                                |                                                              |

### `commerce_billing_profiles`

Alimenta el bloque `mandante` (4 campos) y el `customer` de la factura de comisión (perfil completo).

| Columna                                                          | Uso                                         | Notas                                                                           |
| ---------------------------------------------------------------- | ------------------------------------------- | ------------------------------------------------------------------------------- |
| `commerce_id`                                                  | —                                          | FK unique                                                                       |
| `legal_name`                                                   | `mandante.name`, `customer.companyName` | RUT campo 35                                                                    |
| `identification_type_code`                                     | `mandante.identificationTypeCode`         | `31` = NIT                                                                    |
| `tax_digit_check`                                              | `mandante.digitCheck`                     | RUT campo 6                                                                     |
| `person_kind`                                                  | `customer.personType`                     | RUT campo 24                                                                    |
| `regime_type`                                                  | `customer.regimeType`                     | RUT campo 53                                                                    |
| `fiscal_responsibilities`                                      | `customer.responsibilities`               | **Incluye código 07** → activa la retención del 11% sobre la comisión |
| `economic_activities`                                          | `customer.economicActivities`             | CIIU                                                                            |
| `commerce_type`                                                | Decide FAN vs Recibo Interno                | `a` \| `b`, **derivado** de las tres condiciones, no autodeclarado    |
| `is_vat_responsible` / `is_inc_responsible`                  | Condición (i) de Tipo B                    |                                                                                 |
| `has_single_establishment`                                     | Condición (ii) de Tipo B                   |                                                                                 |
| `annual_income_uvt`                                            | Condición (iii) de Tipo B                  | Umbral 3.500 UVT                                                                |
| `billing_email` / `billing_address` / códigos de ubicación | `customer.*`                              |                                                                                 |
| `verified_by_id` / `verified_at`                             | —                                          | Admin que validó contra el RUT                                                 |

**Reglas de integridad:**

- `commerce_type = 'b'` solo es válido si se cumplen las tres condiciones simultáneamente:
  `is_vat_responsible = false AND is_inc_responsible = false AND has_single_establishment = true AND annual_income_uvt <= 3500`. Cualquier otra combinación fuerza `'a'`.
- **`verified_by_id` no puede ser nulo cuando `commerce_type = 'b'`.** Tipo B se salta la DIAN y ahorra
  folios de la bolsa: precisamente por eso exige firma humana de back-office sobre el RUT cargado. Es
  la única barrera contra clasificar mal a un comercio para no gastar folios.

### `electronic_documents`

| Columna                                     | Notas                                                                                   |
| ------------------------------------------- | --------------------------------------------------------------------------------------- |
| `commerce_id`                             | Scoping por ownership                                                                   |
| `documentable_type` / `documentable_id` | `Order` (venta) o dispersión (comisión)                                             |
| `document_kind`                           | `sale_invoice_mandate` \| `commission_invoice` \| `credit_note` \| `debit_note` |
| `resolution_id`                           | FK →`dian_resolutions`                                                               |
| `prefix` / `consecutive`                | **Clave de idempotencia**                                                         |
| `mode`                                    | `Production` \| `Habilitation` \| `Test`                                          |
| `status`                                  | `pending` \| `sent` \| `accepted` \| `rejected` \| `error` \| `contingency`   |
| `provider_document_id` / `cufe`         | Devueltos por Aliaddo                                                                   |
| `xml_path` / `pdf_path`                 | Storage propio — la API solo retiene 30 días                                          |
| `related_document_id`                     | Para notas crédito/débito                                                             |
| `settlement_id`                           | FK →`settlements`, nullable. Lo usa la FAN de comisión.                             |

**Índices:** `(documentable_type, documentable_id)`, `commerce_id`, `status`, y **unique
`(resolution_id, prefix, consecutive)`**.

> ⚠️ **`contingency` es un estado distinto de `error`, no un sinónimo.** Ver
> [Reintento vs. contingencia](#reintento-transitorio-vs-contingencia-dian) — requiere un job
> recurrente propio, no solo el reintento reactivo del flujo normal.

### `settlements`

El corte semanal por comercio. **No existe hoy en el repo y es prerequisito**: colgar la FAN de
comisión directamente de una `Order` o de una `Transaction` suelta es un antipatrón relacional,
porque la comisión se agrega semanalmente por comercio, no por orden.

| Columna                                 | Notas                                                                     |
| --------------------------------------- | ------------------------------------------------------------------------- |
| `commerce_id`                         | FK                                                                        |
| `prefix` / `consecutive`            | Consecutivo interno de dispersión, no DIAN                               |
| `started_at` / `ended_at`           | Período;`ended_at` es el corte del viernes                             |
| `gross_collected_amount`              | Recaudado bruto del período                                              |
| `net_platform_commission`             | Comisión neta de Ñapa                                                   |
| `commission_iva`                      | 19% sobre la comisión neta                                               |
| `merchant_withholding_credit_to_napa` | El 11% que el comercio le retuvo a Ñapa.`0` si no es agente retenedor. |
| `gateway_operational_fees`            | Tarifas de la pasarela del período                                       |
| `total_dispersed_payout`              | Neto efectivamente transferido                                            |
| `status`                              | `pending` \| `invoiced` \| `dispersed` \| `failed`                |

**Único:** `(commerce_id, ended_at)` — impide dos cortes del mismo período.

> **Nota de nomenclatura.** `merchant_withholding_credit_to_napa` lleva el sufijo `_to_napa` a
> propósito. En este modelo conviven **dos créditos de retención con dueños opuestos**: los de
> `settlement_withholdings` son créditos **del comercio** (Ñapa se los traslada), y este es un crédito
> **de Ñapa** (el comercio se lo certifica). Un nombre como `merchant_withholding_credit` a secas se
> lee como el primero y significa el segundo.

### `internal_receipts`

El Recibo Interno de comercios Tipo B. Consecutivo propio, sin resolución DIAN.

| Columna                 | Notas                                              |
| ----------------------- | -------------------------------------------------- |
| `order_id`            | FK                                                 |
| `commerce_id`         |                                                    |
| `receipt_number`      | Consecutivo plano, ej.`REC-000452`               |
| `issued_at`           | Fecha/hora del procesamiento del pago              |
| `product_base_amount` | Valor del producto                                 |
| `shipping_amount`     | Flete, desglosado aparte por exigencia de Ley 1480 |
| `total_buyer_paid`    | Total debitado al comprador                        |
| `status`              | `issued` \| `refunded`                         |
| `document_path`       | PDF persistido                                     |
| `emailed_at`          | Envío al comprador                                |

El desglose económico no es decorativo: sin `product_base_amount` y `total_buyer_paid` persistidos,
la Certificación Mensual no puede consolidar el recaudo de los comercios Tipo B, cuyas ventas no dejan
rastro en `electronic_documents`.

### `settlement_withholdings`

Retenciones de pasarela por transacción. **Sin esta tabla no existe la certificación mensual.**

| Columna                                 | Notas                                                    |
| --------------------------------------- | -------------------------------------------------------- |
| `transaction_id`                      | FK →`transactions`                                    |
| `commerce_id`                         | A quién pertenece el crédito                           |
| `kind`                                | `retefuente` \| `reteiva` \| `reteica`             |
| `base_amount` / `rate` / `amount` |                                                          |
| `gateway`                             | Quién practicó la retención                           |
| `certificate_id`                      | FK →`mandate_certificates`, nullable hasta certificar |

### `mandate_certificates`

| Columna                                         | Notas                                   |
| ----------------------------------------------- | --------------------------------------- |
| `commerce_id`                                 |                                         |
| `period_start` / `period_end`               | Mes certificado                         |
| `total_collected`                             | Recaudado a nombre del comercio         |
| `total_commission` / `total_commission_iva` |                                         |
| `total_withholdings_transferred`              | Créditos trasladados                   |
| `signed_by`                                   | Representante Legal o Contador Público |
| `document_path` / `issued_at`               | PDF firmado, persistido                 |

**Único:** `(commerce_id, period_start)`.

### `commerce_legal_acceptances`

Aceptaciones de documentos legales por comercio. **Reemplaza el uso de
`commerces.terms_accepted_version`** para el Contrato de Mandato, que necesita trazabilidad propia.

| Columna               | Notas                                           |
| --------------------- | ----------------------------------------------- |
| `commerce_id`       | FK                                              |
| `legal_document_id` | FK →`legal_documents` (ya existe en el repo) |
| `version`           | Versión exacta aceptada                        |
| `accepted_at`       | Timestamp                                       |
| `ip_address`        | Origen de red — evidencia bajo Ley 527         |
| `user_agent`        | Huella del navegador — evidencia bajo Ley 527  |

**Único:** `(commerce_id, legal_document_id, version)`.

Un entero de estado en la tabla padre **no basta** para probar un contrato vinculante en una auditoría
forense. Si la DIAN audita una transacción de hace 12 meses, Ñapa debe poder reproducir **el texto
exacto** de la cláusula de mandato que ese comercio aceptó **ese día**. `ip_address` y `user_agent` son
lo que sostiene la firma electrónica.

### Relación con el modelo existente

```
Commerce ──1:1── commerce_billing_profiles
Commerce ──1:N── commerce_legal_acceptances ──N:1── legal_documents
Commerce ──1:N── settlements ──1:N── electronic_documents (FAN de comisión)
Commerce ──1:N── mandate_certificates ──1:N── settlement_withholdings
dian_resolutions (solo Ñapa) ──1:N── electronic_documents
Order ──0:1── electronic_documents   (Tipo A)
Order ──0:1── internal_receipts      (Tipo B)
Transaction ──1:N── settlement_withholdings
```

> ⚠️ **Adaptar los tipos al stack.** Este backend es **Laravel 12 sobre MySQL**, con claves primarias
> `bigint unsigned auto_increment`. Cualquier DDL de referencia escrito en PostgreSQL debe traducirse:
> `UUID` → `bigint unsigned` (coherente con `commerces`, `orders`, `transactions`), `INET` →
> `varchar(45)` (admite IPv6), `TIMESTAMP WITH TIME ZONE` → `timestamp`, y `NUMERIC(15,2)` →
> `decimal(15,2)`. Las reglas de integridad se expresan con migraciones de Laravel y validación en
> FormRequests, siguiendo la convención del repo, no con `CHECK` crudo.

> **Falta el modelo de liquidación/dispersión.** El repo tiene `Order` y `Transaction`, pero ninguna
> entidad de corte semanal. La FAN de comisión cuelga de ella. Es prerequisito.

---

## Contrato Interno Agnóstico de Proveedor

```php
interface ElectronicInvoicingProvider
{
    /** Venta vía mandato: resolución de Ñapa, mandante = comercio, customer = comprador. */
    public function issueMandateSaleInvoice(MandateSaleData $data): IssuanceResult;

    /** Comisión: resolución de Ñapa, customer = comercio, +19% IVA. */
    public function issueCommissionInvoice(CommissionInvoiceData $data): IssuanceResult;

    public function issueCreditNote(CreditNoteData $data, string $relatedDocumentId): IssuanceResult;

    public function checkStatus(string $providerDocumentId): IssuanceResult;

    /**
     * Idempotencia: saber si un documento ya se emitió ANTES de reintentar y
     * volver a pagar por él. Los rechazos DIAN se cobran igual que los aceptados.
     */
    public function findByConsecutive(string $prefix, int $consecutive): ?IssuanceResult;
}
```

El **Recibo Interno no pasa por esta interfaz**: no es un documento de proveedor, se genera y envía
localmente.

**Drivers:** `AliaddoNitroDriver` (principal) y `NullDriver` (dev/tests). Selección por
`config/invoicing.php`.

---

## Elección de API: Nitro

| Familia                             | Estado según Aliaddo               | Relevancia                                                                  |
| ----------------------------------- | ----------------------------------- | --------------------------------------------------------------------------- |
| **API Integradores Nitro**    | **Actual**                    | Facturas, notas, bloque`mandante` por línea. **Es la que se usa.** |
| API ERP Cloud                       | Actual                              | Fuera de alcance.                                                           |
| API FE de Primera Generación (ISV) | **"APIs Heredadas (Legacy)"** | Integrarse hoy contra ella sería deuda técnica de nacimiento.             |

Nitro soporta el bloque `mandante` que el modelo exige, está orientada a facturación masiva (encaja
con la dispersión semanal), ofrece **consulta por prefijo y consecutivo** —la primitiva de
idempotencia que necesitamos— y usa el campo `mode` en el body en vez de tres URLs por ambiente.

---

## Flujos

### Emisión: guardas antes de gastar una transacción

Cada rechazo de la DIAN **se cobra igual que un documento aceptado**.

```
   ├── perfil fiscal incompleto o resolución vencida/agotada ──▶ error de negocio, NO se envía
   ▼
Reservar consecutivo (unique resolution_id + prefix + consecutive)
   ▼
Enviar
   ├── accepted ──▶ persistir CUFE + XML/PDF en storage propio
   ├── rejected ──▶ persistir código DIAN. NO reintentar a ciegas: ya se cobró.
   └── timeout  ──▶ findByConsecutive() ANTES de reintentar
```

Sin `prefix`/`consecutive` propios, un timeout de red es indistinguible de un fallo real y cada
reintento es dinero. Concretamente: si la API expira **después** de que la DIAN ya procesó y validó el
CUFE, el reintento a ciegas falla con **`90 - Documento procesado anteriormente`**, se cobra igual y
bloquea el hilo. Por eso el `findByConsecutive()` ante un HTTP 504 no es una optimización: es lo que
evita pagar dos veces por el mismo documento.

**Arquitectura de cola.** Ninguna llamada a Aliaddo sale del hilo del controlador de checkout. Todas
van dentro de un worker aislado (Laravel Queues) con **throttling de 40 requests/minuto** — holgado
frente al techo de 3.000/hora, y con margen para crecer sin rozarlo.

### Reintento transitorio vs. contingencia DIAN

Son dos fallos diferidos distintos y **solo uno lo cubre el mecanismo de arriba**.

**Fallo transitorio propio** (timeout, 5xx de Aliaddo, caída puntual de red): ya cubierto. El job de
cola reintenta y `findByConsecutive()` evita duplicar. No requiere webhook — el reintento lo dispara
nuestro propio scheduler, no un evento externo.

**Contingencia DIAN** (la DIAN no está disponible en el momento del envío): es otro mecanismo. La
documentación de Aliaddo lo describe así — *"Se genera con un código especial de contingencia y
después lo transmites a la DIAN cuando el servicio vuelva a estar disponible"* — y el verbo importa:
**"lo transmites"**, una acción activa del integrador, no una validación pasiva que se resuelve sola
del lado de la DIAN mientras esperamos.

> ⚠️ **La documentación pública no aclara tres cosas**, y sin ellas no se puede cerrar el diseño de
> este caso:
>
> 1. ¿Quién detecta que la DIAN volvió a estar disponible — Aliaddo por su cuenta, o depende de que
>    nosotros lo intentemos de nuevo?
> 2. ¿Es el mismo endpoint de creación, o existe uno específico de retransmisión de contingencia?
> 3. ¿Hay algún "cierre" formal del estado, o el documento queda en un limbo hasta que alguien actúa?
>
> **Si depende de nosotros** (más probable dado el verbo "transmites"): el modelo de `status` alcanza,
> pero necesita un ingrediente que el flujo de arriba no tiene — un **job recurrente** que reintente
> activamente los documentos en estado `contingency` hasta que la DIAN los acepte, potencialmente
> durante horas. Es distinto del reintento reactivo ante un timeout puntual: aquí no hay un evento que
> lo dispare, hay que sondear con una cadencia propia. Sigue sin requerir webhook — es polling nuestro.
>
> **Si Aliaddo reintenta por su cuenta** y solo necesitamos enterarnos de cuándo se resolvió, el
> webhook sería útil pero **tampoco es indispensable**: Nitro expone consulta por ID y por prefijo +
> consecutivo, así que el mismo job recurrente sirve preguntando en vez de retransmitiendo. El webhook
> cambia *cuándo* nos enteramos (push, inmediato), no *si* podemos enterarnos (pull, a nuestra
> cadencia).
>
> **Conclusión de las dos ramas:** la forma del job es la misma en ambos escenarios — recorrer los
> documentos en `contingency` con backoff y actuar. Lo único que cambia es si ese job retransmite o
> solo consulta. Se puede diseñar e implementar sin esperar la respuesta de Aliaddo.

**Dos advertencias sobre el polling de contingencia**, para no venderlo como gratuito:

- **Consumo de bolsa.** Ante un timeout puntual, `findByConsecutive()` se llama una vez. En
  contingencia, sondear cada 5–10 minutos durante una caída de horas son decenas de consultas **por
  documento**. Si las consultas de estado descuentan de la bolsa (pregunta abierta, ver
  [Costos](#costos)), el costo se multiplica por la duración de la caída, no por el número de facturas.
- **Una caída de la DIAN no afecta a un documento, afecta a todos los que se estén emitiendo.** El
  punto más sensible es el corte de los viernes: si la DIAN cae durante el batch semanal quedan decenas
  o cientos de documentos en `contingency` a la vez, y sondearlos uno por uno con cadencia ingenua
  compite por el mismo cupo de 40 req/min que se necesita para seguir emitiendo lo que sí funciona.
  Backoff exponencial obligatorio, y evaluar consulta agrupada si el volumen lo justifica.

**Almacenamiento.** Apenas se recibe un callback `accepted`, se copian XML y PDF a bucket privado
propio. La API de Nitro solo los espeja 30 días; después, lo que no se guardó no existe.

### Dispersión semanal (viernes)

```
Corte del viernes
   ▼
Por comercio, consolidar el período:
   - total recaudado
   - comisión sobre valor bruto + 19% IVA     ──▶ emitir FAN de comisión
   - retenciones de pasarela (ya persistidas por transacción)
   - retención del 11% sobre la comisión, si el comercio tiene código 07
   ▼
payout = recaudado − (comisión + IVA) − retenciones pasarela − tarifas operativas (+ retención comisión)
   ▼
Marcar las retenciones de pasarela para su traslado en la certificación del mes
```

### Certificación mensual

Se ejecuta en los primeros 10 días calendario del mes siguiente, consolidando por comercio y
requiriendo firma de Representante Legal o Contador Público.

### Anulación por cancelación o reembolso

Una orden Tipo A con documento `accepted` que se cancela o reembolsa genera **nota crédito**
referenciando el original vía `related_document_id`. Los documentos emitidos son inmutables ante la
DIAN. Para Tipo B, al no haber documento electrónico, basta con el ajuste interno.

> Los umbrales de disputa del T&C (≤$25.000 automático; $25.000–$60.000 con 24h de respuesta del
> comercio; >$60.000 arbitraje) determinan **cuándo** se dispara este flujo.

---

## Límites, Costos y Riesgos

### Límites Aliaddo

| Límite                           | Valor                                      |
| --------------------------------- | ------------------------------------------ |
| API REST                          | 3.000 requests/hora por NIT habilitado     |
| Retención de documentos vía API | 30 días (portal 1 año, respaldo 5 años) |
| Tamaño XML                       | < 500KB                                    |

### Costos

Bolsas prepagadas de documentos, vigencia 1 año, tarifa decreciente por volumen, excluidas de IVA,
ajuste anual IPC + 3pp.

> El detalle de tarifas de la **propuesta comercial** es **confidencial** y no se transcribe en este
> documento versionado. Está en `aliaddo-doc/` (fuera de git). Los planes **públicos** de
> `aliaddo.com/precios/api-facturacion-masiva/` sí pueden citarse — ver comparación abajo.

**Volumen esperado:** una FAN de comisión por comercio y por corte semanal, más una FAN de venta por
cada orden de comercio **Tipo A**. Las ventas de comercios Tipo B **no consumen bolsa** — el Recibo
Interno es local. El piso de consumo no lo marcan las ventas, sino las facturas de comisión: con 96
comercios activos, un año de cortes semanales agota por sí solo un plan de 5.000 documentos, sin
contar una sola venta.

> ⚠️ **La propuesta comercial recibida cotiza ~70-127% más caro por documento que los planes públicos
> de Aliaddo**, comparando volúmenes equivalentes (ej. $72/doc en la propuesta vs $40/doc en el plan
> público "Executive" para ~40-50k documentos). Confirmar con Aliaddo el motivo de la diferencia antes
> de contratar — puede deberse a que la propuesta es anterior a los planes públicos, o a que cubre algo
> que los planes de lista no incluyen (ver nota de firma bajo mandato más abajo).

**Recomendación de plan (evaluación informal sobre precios públicos, 2026-08-07):** **Business Plus**
($674.900/año, 15.000 documentos, ~$45/doc, 60 requests/minuto). Dos razones:

1. **Rate limit.** El plan de entrada ("Pro") da solo 20 requests/minuto. El diseño de este documento
   especifica throttling de **40 requests/minuto** para el batch de dispersión semanal (ver
   [Emisión: guardas antes de gastar una transacción](#emisión-guardas-antes-de-gastar-una-transacción));
   con Pro habría que rediseñar el throttling a la mitad y el corte de los viernes se alargaría.
   Business Plus (60/min) deja margen.
2. **Costo por volumen.** Por encima de ~5.000 documentos/año, Business Plus sale más barato que
   comprar el plan de entrada dos veces, y trae "Reportes de documentos generados", que sirve
   directamente para conciliar la Certificación Mensual de Mandato.

No decidido todavía — pendiente de: (a) confirmar con Aliaddo si los planes públicos incluyen la firma
electrónica bajo contrato de mandato (la propuesta comercial la menciona explícitamente: *"Nuestra
firma electrónica (Se debe firmar contrato de mandato)"*; la página de planes públicos no lo aclara, y
sin eso ningún plan público sirve para este modelo), y (b) la pregunta de consultas de estado más
abajo.

> ⚠️ **Pregunta abierta: ¿las consultas de estado (`findByConsecutive`, `checkStatus`) consumen bolsa
> de documentos, o solo cuota de rate limit?** La propuesta comercial ya establece un precedente
> incómodo: *"los documentos rechazados por la DIAN se cuentan como transacción exitosa y se contará
> como documento facturable"*. Si las consultas de estado también consumieran bolsa, cada verificación
> de idempotencia tras un timeout costaría dinero además de cupo de rate limit, lo que cambiaría el
> diseño del mecanismo de reintento (hoy asumido "gratis" salvo por rate limit) y el volumen anual
> proyectado. Confirmar con Aliaddo antes de contratar el plan.
>
> Nota relacionada: bajo el flujo síncrono de Nitro (el JSON se responde con el resultado de la DIAN
> en el mismo request) no se necesitan **webhooks** para el camino feliz ni para el reintento
> transitorio propio (timeout, 5xx) — el `status` + `findByConsecutive()` alcanzan ahí.
>
> **El modo contingencia queda con un signo de interrogación, no descartado del todo** — ver
> [Reintento vs. contingencia](#reintento-transitorio-vs-contingencia-dian). Si retransmitir tras una
> caída de la DIAN depende de nosotros (lo más probable según cómo lo describe Aliaddo), un job de
> polling recurrente lo cubre sin webhook. Si en cambio Aliaddo reintenta por su cuenta y solo
> necesitamos que nos avisen cuándo terminó, el webhook vuelve a ser relevante. Eventos RADIAN de
> aceptación del adquirente tampoco aplican al alcance actual. Con la incertidumbre de contingencia
> sin resolver, los planes sin webhooks (Pro, Business Plus) siguen siendo la apuesta razonable, pero
> no es una descartada 100% cerrada.

### Riesgos

| Riesgo                                                                   | Prob. | Impacto        | Mitigación                                                                                                                                |
| ------------------------------------------------------------------------ | ----- | -------------- | ------------------------------------------------------------------------------------------------------------------------------------------ |
| No capturar las retenciones de pasarela por transacción                 | Alta  | **Alto** | `settlement_withholdings` desde el día uno. Sin esto Ñapa tributa sobre dinero ajeno.                                                  |
| No emitir la certificación mensual en plazo                             | Media | **Alto** | Obligación legal con plazo de 10 días. Entidad persistida + job programado.                                                              |
| Facturar IVA/INC incorrecto por producto                                 | Alta  | **Alto** | Depende de SCRUM-362. Un IVA mal liquidado en una FAN firmada es un error fiscal.                                                          |
| Bug de reintentos quema la bolsa                                         | Media | Alto           | Unique`(resolution_id, prefix, consecutive)` + `findByConsecutive`.                                                                    |
| Clasificar mal a un comercio como Tipo B                                 | Media | **Alto** | Derivar de las tres condiciones, no del booleano autodeclarado. Validar contra RUT y 1876.                                                 |
| Resolución de Ñapa vencida o rango agotado                             | Media | **Alto** | Al ser una sola resolución para todo, su agotamiento**detiene toda la operación**. Monitoreo de `valid_until` y `range_final`. |
| Recibo Interno sin las cláusulas de Ley 1480                            | Media | Medio          | Plantilla revisada por legal antes de producción.                                                                                         |
| Perder la trazabilidad de qué versión de mandato aceptó cada comercio | Media | **Alto** | Tabla de aceptaciones con IP y user-agent. Es la evidencia ante la DIAN.                                                                   |

---

## Decisiones Abiertas

Casi todo el diseño quedó cerrado. Lo que queda:

- **⚠️ Mecánica de la retención del 11% sobre la comisión.** Ñapa descuenta su comisión de dinero que
  ya tiene en custodia; lo habitual es que retenga quien paga. Confirmar la mecánica con el contador
  antes de implementar la dispersión.
- **⚠️ Revisión jurídica del T&C.** El borrador v5.0 requiere las cuatro correcciones de la sección
  siguiente, más las validaciones que él mismo marca como pendientes de abogado (cláusulas 2 y 7).
- **Habilitación contractual con Aliaddo.** Qué exige para operar bajo mandato y cómo se factura el
  consumo.
- **Habilitación de Ñapa ante la DIAN.** Correr el set de pruebas con Aliaddo para que la DIAN active
  el código 52. Todo lo demás depende de esto.
- **⚠️ Elección de plan y diferencia de precio.** Recomendación preliminar: **Business Plus**
  ($674.900/año, 15.000 docs, 60 req/min) — ver [Costos](#costos) para el detalle. Pendiente confirmar
  con Aliaddo: (a) por qué la propuesta comercial recibida cotiza 70-127% más caro por documento que
  los planes públicos en volúmenes equivalentes; (b) si los planes públicos incluyen la firma
  electrónica bajo contrato de mandato; (c) si las consultas de estado (`findByConsecutive`) consumen
  bolsa de documentos o solo cuota de rate limit — afecta tanto el diseño del mecanismo de idempotencia
  como el volumen anual proyectado.
- **Mecánica exacta del modo contingencia.** Ver
  [Reintento vs. contingencia](#reintento-transitorio-vs-contingencia-dian). No está claro si
  retransmitir tras una caída de la DIAN es una acción activa nuestra o si Aliaddo lo resuelve por su
  cuenta. **No bloquea la implementación**: el job recurrente sobre documentos en `contingency` tiene la
  misma forma en ambos casos, y el webhook es en cualquiera de los dos una optimización de latencia, no
  un requisito. Lo que sí falta confirmar es si las consultas de estado consumen bolsa, porque de eso
  depende el costo real del sondeo.

---

## Correcciones Requeridas al T&C v5.0

El borrador de Términos y Condiciones del Proveedor contradice este diseño en cuatro puntos. **Las
cuatro se resolvieron a favor del marco fiscal** y deben corregirse antes de publicar:

| # | Cláusula | Estado en el borrador                                                       | Corrección                                                                                                                                                                                                                            |
| - | --------- | --------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1 | 8.1       | FAN al usuario*"en todos los casos"*, sin importar el régimen del comercio | La emisión depende**estrictamente** de la calidad tributaria del comercio: Tipo A → FAN; Tipo B → **ninguna factura electrónica**, solo Recibo Interno (Ley 1480 art. 50)                                              |
| 2 | 8.3       | El proveedor Tipo B recibe**DSN** por cada liquidación               | **Eliminar toda mención de DSN hacia los comercios.** La comisión se cobra con FAN propia + IVA 19%. Aclarar explícitamente que la dispersión no es una compra y que nunca se emitirán DSN en liquidaciones del marketplace |
| 3 | 4(h)      | Solo promete conservar documentos 5 años                                   | **Insertar cláusula nueva** de Certificación Mensual de Mandato: primeros 10 días calendario del mes siguiente, firmada por Representante Legal o Contador Público, citando Art. 1.6.1.4.9 del Decreto 1625                  |
| 4 | 8.7       | Liquidación**quincenal, los lunes**                                  | **Semanal, los viernes** — es lo que refleja el flujo operativo real y el modelo de caja de la pasarela                                                                                                                         |

> El punto 2 era además una **inconsistencia interna del propio borrador**: la cláusula 8.8 afirma que
> el giro es devolución de fondos y no un pago por servicios, pero la 8.3 emitía un DSN — documento
> que soporta precisamente una adquisición.

### Cláusula 8.9 a insertar — texto para el abogado

> **CLÁUSULA 8.9 — CONTRATO DE MANDATO PARA LA FACTURACIÓN Y INTERMEDIACIÓN DIGITAL**
>
> El Comercio (en adelante, el "Mandante") otorga un mandato expreso, irrevocable y comercial, en los
> términos del Artículo 438 del Estatuto Tributario y el Artículo 1.6.1.4.9 del Decreto 1625 de 2016,
> a favor de ÑAPA S.A.S. (en adelante, el "Mandatario"), para que este último, por cuenta y riesgo del
> Mandante, realice las siguientes gestiones:
>
> (i) Recaudar a través de sus pasarelas de pago integradas el cien por ciento (100%) de los dineros
> provenientes de las ventas de los productos exhibidos por el Mandante.
>
> (ii) Expedir, firmar y transmitir electrónicamente las Facturas de Venta (Prefijo FAN) asociadas a
> los productos de los Comercios Tipo A utilizando la resolución de facturación del Mandatario, pero
> imputando el ingreso y las responsabilidades tributarias al NIT del Mandante a través de los nodos
> técnicos correspondientes.
>
> (iii) Generar soportes de operación internos y locales (Recibos de Compra) no electrónicos para las
> ventas de los Comercios Tipo B.
>
> El Mandante reconoce y acepta que las retenciones financieras practicadas por la pasarela de pagos
> al Mandatario constituyen créditos fiscales de propiedad exclusiva del Mandante. El Mandatario se
> obliga a trasladar formalmente dichos saldos mediante una Certificación Mensual de Contrato de
> Mandato, la cual será emitida y puesta a disposición del Mandante dentro de los primeros diez (10)
> días calendario del mes siguiente, constituyendo el único soporte válido para el cruce de cuentas,
> conciliaciones de dispersión y declaraciones de renta de las partes.

Esta cláusula cubre las correcciones **1, 2 y 3** de la tabla anterior en un solo texto: define qué
documento se emite según el tipo de comercio, deja el DSN fuera del flujo, y compromete la
certificación mensual con su plazo.

> ⚠️ **Debe ir como documento legal independiente**, no fundido en el T&C genérico. Ver
> [`commerce_legal_acceptances`](#commerce_legal_acceptances): la DIAN puede auditar operaciones de
> mandato años después, y eso exige registrar por separado qué versión del mandato aceptó cada
> comercio, cuándo, desde qué IP y con qué navegador.

---

## Dependencias

- **SCRUM-362** (clasificación fiscal de productos, IVA/INC) — **prerequisito duro**. Sin él no se
  puede emitir una FAN de venta válida ni calcular la ReteIVA. Nota: el T&C (cláusula 5.3) establece
  que la clasificación la declara **el comercio** y que Ñapa no la verifica — el diseño debe permitir
  capturarla por producto, individualmente y por carga masiva de catálogo.
- **SCRUM-339** (migración a movii) — la pasarela debe **reportar sus retenciones por transacción**.
  Si no las expone, la certificación mensual es inviable. Coordinar antes de cerrar ese diseño.
- **SCRUM-187** (`GET /api/v1/documents/legal`, sin iniciar) — el hueco donde vive el Contrato de
  Mandato como documento legal aceptable.
- **SCRUM-214** (finalizado) — ya provee la persistencia de aceptación.
- **SCRUM-310** (documento del comprador) — sin él, todo va como "Consumidor Final".
- **Modelo de liquidación/dispersión** — no existe en el repo.
- Habilitación de Ñapa ante la DIAN (set de pruebas) → activa el código 52.
- Revisión jurídica del T&C con las cuatro correcciones.

---

## Checklist OWASP Aplicado

- **[A02]** Credenciales de Aliaddo solo por variable de entorno. Bajo mandato hay una sola credencial
  (la de Ñapa), lo que reduce la superficie frente a un modelo multi-tenant.
- **[A01]** Todo endpoint de consulta de `electronic_documents`, `settlement_withholdings`,
  `mandate_certificates` o `internal_receipts` por comercio debe usar `AuthorizesCommerceOwnership`
  desde el primer commit — la lección de SCRUM-334/343 aplica de entrada. Un comercio no puede ver las
  retenciones ni los documentos de otro.
- **[A09]** Payloads persistidos con redacción de credenciales. Nunca loguear el payload completo:
  contiene NIT, dirección y correo del comprador.
- **[A04]** La idempotencia es requisito de seguridad **y** de costo: un reintento duplicado emite un
  documento fiscal irreversible ante la DIAN, y se cobra.
- **[A08]** Documentos emitidos, recibos y certificaciones son registros fiscales: inmutables una vez
  emitidos. Corregir es emitir nota crédito, nunca editar ni borrar.

---

## Referencias

- Propuesta comercial y fichas de Aliaddo — `aliaddo-doc/` (confidencial, fuera de git).
- Documentación Aliaddo — https://docs.aliaddo.com/ (índice completo en `/llms.txt`).
- Crear factura electrónica — https://docs.aliaddo.com/crear-factura-electr%C3%B3nica-36440739e0
- Modos de ambiente — https://docs.aliaddo.com/modos-de-ambiente-de-facturaci%C3%B3n-2171117m0
- Estatuto Tributario, Arts. 392, 426, 437, 438, 616-2, 632.
- Decreto 1625 de 2016, Art. 1.6.1.4.9 — traslado de retenciones bajo mandato.
- Ley 1480 de 2011, Art. 50 — Estatuto del Consumidor, soporte de la transacción.
- Ley 527 de 1999 — validez del contrato electrónico.
- Términos y Condiciones del Proveedor v5.0 (borrador) — pendiente de las correcciones de arriba.
- SCRUM-252, SCRUM-187, SCRUM-214, SCRUM-310, SCRUM-339, SCRUM-362 en Jira.
