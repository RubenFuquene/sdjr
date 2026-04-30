## Plan: Mensaje Personalizado en Verificación de Comercios

Modificar el endpoint PATCH /api/v1/commerces/{id}/verification para aceptar un mensaje personalizado desde el frontend que será incluido en las notificaciones de correo (tanto para verificación como rechazo), sin persistir el mensaje en la base de datos.

**Steps**

1. Fase de contrato y reglas
   1. Definir contrato del endpoint actualizado: body con "is_verified" (existente) y "message" (nuevo, obligatorio).
   1. Establecer validación del campo "message": string requerido, longitud mínima 10 caracteres, máxima 500 caracteres.
   1. Mantener respuestas existentes (200 en éxito, 403/404/422/500 según casos) con el mismo estilo de ApiResponseTrait.

2. Fase de validación (FormRequest)
   1. Actualizar `PatchCommerceVerificationRequest` para incluir regla de validación del campo "message".
   1. Agregar mensajes de error personalizados para el campo "message" (si aplica).
   1. Mantener autorización existente con permiso provider.commerces.update.

3. Fase de notificaciones de correo
   1. Modificar `CommerceVerifiedNotification`:
      - Agregar propiedad protegida `$message` (string).
      - Actualizar constructor para recibir Commerce y el mensaje personalizado.
      - Modificar método `toMail()` para agregar el mensaje personalizado como línea adicional después de las líneas actuales, antes de la acción.
   1. Modificar `CommerceRejectedNotification`:
      - Agregar propiedad protegida `$message` (string).
      - Actualizar constructor para recibir Commerce y el mensaje personalizado.
      - Modificar método `toMail()` para agregar el mensaje personalizado como línea adicional después de las líneas actuales.

4. Fase de controlador
   1. Actualizar método `patchVerification` en `CommerceController`:
      - Extraer el campo "message" del request validado.
      - Pasar el mensaje personalizado al constructor de CommerceVerifiedNotification.
      - Pasar el mensaje personalizado al constructor de CommerceRejectedNotification.
   1. Actualizar documentación OpenAPI del endpoint:
      - Agregar "message" en el @OA\RequestBody como campo requerido.
      - Documentar tipo, restricciones y descripción del campo.
      - Actualizar descripción del endpoint si es necesario.

5. Fase de pruebas
   1. Actualizar `PatchCommerceVerificationTest`:
      - Modificar test_patch_commerce_verification_success para incluir el campo "message".
      - Agregar test para verificar que falla (422) si no se envía el campo "message".
      - Agregar test para verificar que falla (422) si el mensaje es muy corto (<10 caracteres).
      - Agregar test para verificar que falla (422) si el mensaje es muy largo (>500 caracteres).
      - Verificar que los tests de sin permiso (403) también incluyan el campo "message" en el payload.
   1. Considerar agregar verificación de que las notificaciones se envían con el mensaje correcto (opcional: usar Queue::fake o Notification::fake).

6. Fase de verificación
   1. Ejecutar tests del endpoint: `php artisan test --filter=PatchCommerceVerificationTest`.
   1. Ejecutar Pint para asegurar estándares de código.
   1. Verificar regresión: ejecutar tests relacionados con commerces.
   1. Probar manualmente el endpoint con Postman/Insomnia o similar:
      - Enviar request con is_verified=1 y message (verificación).
      - Enviar request con is_verified=2 y message (rechazo).
      - Verificar que el correo incluye el mensaje personalizado como línea adicional.

**Relevant files**

Backend:
- [app/backend/app/Http/Requests/Api/V1/PatchCommerceVerificationRequest.php](app/backend/app/Http/Requests/Api/V1/PatchCommerceVerificationRequest.php) - validación del request con nuevo campo "message".
- [app/backend/app/Notifications/CommerceVerifiedNotification.php](app/backend/app/Notifications/CommerceVerifiedNotification.php) - notificación de verificación con mensaje personalizado.
- [app/backend/app/Notifications/CommerceRejectedNotification.php](app/backend/app/Notifications/CommerceRejectedNotification.php) - notificación de rechazo con mensaje personalizado.
- [app/backend/app/Http/Controllers/Api/V1/CommerceController.php](app/backend/app/Http/Controllers/Api/V1/CommerceController.php) - método patchVerification actualizado y documentación OpenAPI.
- [app/backend/tests/Feature/Api/V1/PatchCommerceVerificationTest.php](app/backend/tests/Feature/Api/V1/PatchCommerceVerificationTest.php) - pruebas actualizadas del endpoint.

**No requieren cambios:**
- [app/backend/app/Services/CommerceService.php](app/backend/app/Services/CommerceService.php) - no cambia, el mensaje no se persiste.
- [app/backend/app/Models/Commerce.php](app/backend/app/Models/Commerce.php) - no cambia, no se agregan campos.
- Migraciones - no se requieren cambios en la estructura de la tabla.

**Verification**

1. Confirmar que el endpoint rechaza requests sin el campo "message" (422).
1. Confirmar que el endpoint rechaza mensajes muy cortos o muy largos (422).
1. Confirmar que el endpoint acepta requests válidos con is_verified y message (200).
1. Verificar que el correo de verificación incluye el mensaje personalizado como línea adicional.
1. Verificar que el correo de rechazo incluye el mensaje personalizado como línea adicional.
1. Ejecutar tests: `php artisan test --filter=PatchCommerceVerificationTest`.
1. Ejecutar Pint: `./vendor/bin/pint`.
1. Ejecutar tests de regresión de commerces.

**Decisions**

- Campo "message" es obligatorio en todos los casos (verificación y rechazo).
- Longitud del mensaje: mínimo 10 caracteres, máximo 500 caracteres.
- El mensaje NO se persiste en la base de datos, solo se usa para el correo.
- El mensaje se agrega como línea adicional en el correo, manteniendo el contenido actual.
- Aplica para ambas notificaciones: CommerceVerifiedNotification y CommerceRejectedNotification.
- No se cambia el método HTTP (sigue siendo PATCH).
- No se requiere nuevo permiso (se mantiene provider.commerces.update).
- Alcance incluido: backend completo (validación, notificaciones, controlador, documentación OpenAPI, tests).
- Alcance excluido: frontend (el frontend debe enviar el campo "message" en el request).

**Consideraciones adicionales**

1. **Seguridad**: Validar que el mensaje no contenga HTML o scripts maliciosos (el campo string de Laravel ya hace escape básico).
1. **Internacionalización**: El mensaje personalizado viene del frontend, se asume que ya está en el idioma correcto.
1. **Formato del correo**: El mensaje se agregará con el método `->line()` de MailMessage, manteniendo el formato actual.
1. **Logs**: Los logs existentes ya capturan el commerce_id y is_verified, no es necesario agregar el mensaje al log.

**Ejemplo de uso**

Request actual (antes):
```json
PATCH /api/v1/commerces/123/verification
{
  "is_verified": 1
}
```

Request nuevo (después):
```json
PATCH /api/v1/commerces/123/verification
{
  "is_verified": 1,
  "message": "Tu comercio ha cumplido con todos los requisitos necesarios. ¡Bienvenido!"
}
```

Si te parece bien este plan, en el siguiente paso puedo ejecutarlo por fases en ese mismo orden.
