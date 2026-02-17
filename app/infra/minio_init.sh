#!/bin/bash
set -e

echo "Esperando a MinIO..."
sleep 15

MINIO_ENDPOINT="http://minio:9000"
MINIO_ACCESS_KEY="minioadmin"
MINIO_SECRET_KEY="minioadmin123"
BUCKET_NAME="documents"

# Esperar disponibilidad
max_attempts=30
attempt=0
while ! curl -f "${MINIO_ENDPOINT}/minio/health/live" >/dev/null 2>&1; do
    attempt=$((attempt + 1))
    if [ $attempt -ge $max_attempts ]; then
        echo "MinIO no respondió"
        exit 1
    fi
    echo "MinIO no está listo... ($attempt/$max_attempts)"
    sleep 2
done

echo "✓ MinIO está listo"

# Descargar mc si no existe
if ! command -v mc >/dev/null 2>&1; then
  echo "Descargando cliente mc..."
  curl -sSLo /usr/local/bin/mc https://dl.min.io/client/mc/release/linux-amd64/mc
  chmod +x /usr/local/bin/mc
fi

# Configurar alias
echo "Configurando alias..."
mc alias set minio ${MINIO_ENDPOINT} ${MINIO_ACCESS_KEY} ${MINIO_SECRET_KEY} --api S3v4 || true

# Crear bucket si no existe
echo "Creando bucket..."
mc mb minio/${BUCKET_NAME} --ignore-existing || true

# Configurar CORS via HTTP API
echo "Configurando CORS..."
cat > /tmp/cors.xml << 'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<CORSConfiguration>
  <CORSRule>
    <AllowedOrigin>*</AllowedOrigin>
    <AllowedMethod>GET</AllowedMethod>
    <AllowedMethod>HEAD</AllowedMethod>
    <AllowedMethod>PUT</AllowedMethod>
    <AllowedMethod>POST</AllowedMethod>
    <AllowedMethod>DELETE</AllowedMethod>
    <AllowedMethod>OPTIONS</AllowedMethod>
    <AllowedHeader>*</AllowedHeader>
    <ExposeHeader>ETag</ExposeHeader>
    <ExposeHeader>Content-Length</ExposeHeader>
    <MaxAgeSeconds>3600</MaxAgeSeconds>
  </CORSRule>
</CORSConfiguration>
EOF

# Aplicar CORS
curl -s -X PUT "${MINIO_ENDPOINT}/${BUCKET_NAME}?cors" \
  -H "Content-Type: application/xml" \
  --data-binary @/tmp/cors.xml \
  -u ${MINIO_ACCESS_KEY}:${MINIO_SECRET_KEY} > /dev/null 2>&1 || true

echo "✓ CORS configurado para todos los orígenes"

# Verificar bucket
echo "Buckets disponibles:"
mc ls minio/

echo "✓ Inicialización completada"
exit 0
