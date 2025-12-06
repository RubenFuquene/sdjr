# Descripción de la tarea

Como proveedor, quiero poder recuperar mi contraseña ingresando mi correo electrónico, para restablecer el acceso a mi cuenta en caso de olvidarla.
Esta historia contempla **dos pantallas principales** y un flujo completo:

## 1. Endpoint: Solicitar recuperación

- `POST /api/v1/auth/password/forgot`

### Validaciones Backend

- Correo:
- Tipo string
- Máx 120 caracteres
- Formato válido
- No vacío

### Comportamiento Backend

- NO debe revelar si el email existe o no (seguridad).
- Si el usuario existe:
- Generar un **token seguro**, único, de un solo uso.
- Guardarlo en la tabla correspondiente con:
- user_id
- token (hashed si se desea)
- fecha de expiración (por ejemplo 30 minutos)
- Enviar email con enlace:
`https://app.com/reset-password?token=<token>`

## 2. Endpoint: Restablecer contraseña

`POST /api/v1/auth/password/reset`

### Validaciones

### Token

- Debe existir
- Debe no estar expirado
- Debe estar marcado como activo/no usado

### Contraseña

- Tipo string
- Mín. 8, máx. 64
- Al menos 1 letra y 1 número

### Confirmación

- Debe coincidir con password
- Validar token
- Actualizar contraseña del usuario:
- Usar hash seguro: bcrypt o Argon2
- Invalidar token (marcar como usado)
- Registrar en logs “password reset”

## 3. Modelo de Base de Datos

### `password_resets` (o similar)

| Campo | Tipo | Descripción |
| --- | --- | --- |
| id | UUID | PK |
| user_id | UUID | FK users |
| token | varchar(255) | único, hash opcional |
| expires_at | datetime | tiempo de expiración |
| used | boolean | indica si ya fue usado |
| created_at | datetime |  |
| updated_at | datetime |  |
