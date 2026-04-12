## Plan: Mitigación DDOS y Rate Limiting API

Confirmado: la API está expuesta porque no tiene throttling efectivo en rutas públicas ni autenticadas, y tampoco hay capa de infraestructura anti-DDOS en VPS/Docker. La estrategia recomendada (balanceada) es implementar defensa en profundidad en 3 capas: aplicación (Laravel), borde de red (Nginx/Traefik) y observabilidad/respuesta.

**Steps**
1. Fase 1 - Baseline y corrección crítica de confianza de IP
2. Definir y aplicar TrustProxies para que la IP cliente sea confiable detrás de reverse proxy. *bloquea pasos 2 y 3*
3. Inventariar endpoints por criticidad y costo para asignar perfiles de límite: auth (estricto), público lectura (medio), público escritura (estricto), autenticado estándar (medio), operaciones pesadas (estricto).
4. Fase 2 - Rate limiting en Laravel (app-level)
5. Registrar RateLimiters nombrados (por IP para público; por usuario+IP para autenticado) con ventanas y burst balanceados. *depende de 1*
6. Aplicar middleware throttle por grupos en rutas API: público y auth:sanctum con límites diferenciados; aplicar límites más estrictos en login/register/password y endpoints de alto costo. *depende de 5*
7. Ajustar respuesta 429 con payload consistente y headers de observabilidad (Retry-After, X-RateLimit-*), y documentar contrato de error para frontend. *paralelo con 6*
8. Fase 3 - Protección de infraestructura (network-level)
9. Introducir reverse proxy (Nginx o Traefik) delante de Laravel con límites por IP en zona global y zonas específicas de auth; activar conexiones máximas y burst controlados. *paralelo con 4-7; bloquea 12*
10. Endurecer superficie de ataque: límites de tamaño de body, timeouts agresivos, keepalive controlado, y bloqueo temprano de patrones anómalos básicos. *paralelo con 9*
11. Definir políticas de firewall del host para puertos públicos mínimos y rate-limit de SYN/conn según proveedor VPS. *paralelo con 9*
12. Fase 4 - Validación, pruebas y rollout seguro
13. Crear pruebas automáticas de feature para validar 429 en endpoints públicos y auth, y que los límites se reseteen por ventana. *depende de 6*
14. Ejecutar pruebas de carga incremental (k6/ab/autocannon): baseline vs mitigado, midiendo p95/p99, errores, CPU, memoria y tasa de 429. *depende de 9 y 13*
15. Hacer despliegue progresivo con tuning en 2 iteraciones: primero límites conservadores, luego ajuste con métricas reales para reducir falsos positivos. *depende de 14*
16. Fase 5 - Gobierno y operación continua
17. Alinear documentación para eliminar discrepancia entre lo declarado y lo implementado en seguridad de API.
18. Definir runbook de incidente DDOS: umbrales, quién responde, acciones de contención (subir límites de proxy, bloquear rangos, degradar endpoints costosos).

**Relevant files**
- `/Users/jersonjr/Proyectos/App/sdjr/app/backend/routes/api.php` — aplicar middleware throttle por grupos y por endpoint crítico (auth, recuperación de contraseña, consultas pesadas).
- `/Users/jersonjr/Proyectos/App/sdjr/app/backend/bootstrap/app.php` — registrar/ajustar middleware global y de grupo para API.
- `/Users/jersonjr/Proyectos/App/sdjr/app/backend/app/Providers/AppServiceProvider.php` (o provider dedicado) — definir `RateLimiter::for(...)` con llaves por IP/usuario.
- `/Users/jersonjr/Proyectos/App/sdjr/app/backend/config/sanctum.php` — revisar impacto de autenticación por token y coherencia con límites de rutas protegidas.
- `/Users/jersonjr/Proyectos/App/sdjr/app/backend/config/cors.php` — mantener headers expuestos coherentes con headers reales de rate limit.
- `/Users/jersonjr/Proyectos/App/sdjr/app/backend/tests/Feature/` — añadir pruebas de 429, ventanas y comportamiento por tipo de endpoint.
- `/Users/jersonjr/Proyectos/App/sdjr/app/infra/docker-compose.yml` — insertar reverse proxy y red interna para que backend no exponga puerto directo público.
- `/Users/jersonjr/Proyectos/App/sdjr/app/backend/Dockerfile` — asegurar modo de ejecución compatible con reverse proxy en producción.
- `/Users/jersonjr/Proyectos/App/sdjr/docs/api.md` — corregir/actualizar política de límites y códigos esperados.
- `/Users/jersonjr/Proyectos/App/sdjr/docs/architecture.md` — documentar capa de protección perimetral y flujo de tráfico.

**Verification**
1. Ejecutar suite de tests de feature para throttling y confirmar respuestas 429 en umbral exacto para endpoints públicos y auth.
2. Verificar que un mismo usuario autenticado en dos IPs respete política definida (por usuario o usuario+IP según diseño final).
3. Simular ráfaga de login y registro; confirmar bloqueo temporal sin afectar tráfico normal de lectura.
4. Ejecutar prueba de carga de 5, 15 y 30 minutos y comparar p95/p99 antes/después.
5. Confirmar en logs/metrics: tasa de 429, IPs top bloqueadas, endpoints top atacados, y estabilidad de CPU/RAM.
6. Validar manualmente que frontend maneje 429 con retry/backoff y mensajes correctos.

**Decisions**
- Entorno objetivo: VPS/Docker sin CDN/WAF administrado.
- Perfil de protección elegido: balanceado para minimizar falsos positivos iniciales.
- Alcance incluido: API backend Laravel + capa de reverse proxy + pruebas + documentación.
- Alcance excluido por ahora: mitigación L3/L4 avanzada de proveedor (scrubbing center), bot management comercial y geo-blocking avanzado.

**Further Considerations**
1. Reverse proxy recomendado: Opción A Nginx (más común y documentado), Opción B Traefik (mejor DX con Docker). Recomendación inicial: Nginx.
2. Clave de rate limit en autenticados: Opción A usuario, Opción B usuario+IP. Recomendación inicial: usuario+IP para reducir abuso con token compartido.
3. Estrategia de rollout: Opción A big-bang, Opción B canary por endpoints sensibles. Recomendación inicial: canary por auth primero.
