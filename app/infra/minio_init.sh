#!/bin/bash

# Esperar a que MinIO esté listo
echo "Esperando a MinIO..."
sleep 10

MINIO_ENDPOINT="http://minio:9000"
MINIO_ACCESS_KEY="minioadmin"
MINIO_SECRET_KEY="minioadmin123"
BUCKET_NAME="documents"

# Esperar disponibilidad
while ! curl -f "${MINIO_ENDPOINT}/minio/health/live" >/dev/null 2>&1; do
    echo "MinIO no está listo..."
    sleep 2
done

echo "MinIO está listo"

# Descargar mc si no existe (algunas imágenes de MinIO no lo incluyen)
if ! command -v mc >/dev/null 2>&1; then
  echo "Descargando cliente mc..."
  curl -sSLo /usr/local/bin/mc https://dl.min.io/client/mc/release/linux-amd64/mc
  chmod +x /usr/local/bin/mc
fi

# Configurar alias y crear bucket si no existe
mc alias set minio ${MINIO_ENDPOINT} ${MINIO_ACCESS_KEY} ${MINIO_SECRET_KEY} || true
mc mb minio/${BUCKET_NAME} --ignore-existing || true

# Política básica para permitir presigned URLs de lectura/escritura
cat > /tmp/policy.json << EOF
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Principal": "*",
      "Action": [
        "s3:GetObject",
        "s3:PutObject"
      ],
      "Resource": "arn:aws:s3:::${BUCKET_NAME}/*"
    }
  ]
}
EOF

mc policy set /tmp/policy.json minio/${BUCKET_NAME} || true

mc ls minio/

exit 0
