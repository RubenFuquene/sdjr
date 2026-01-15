# Base image
FROM node:20-alpine AS base

# Install dependencies only when needed
FROM base AS deps
WORKDIR /app

# Copy package files
COPY package.json package-lock.json* ./
RUN npm ci

# Development image
FROM base AS runner
WORKDIR /app

ENV NODE_ENV development

COPY --from=deps /app/node_modules ./node_modules
COPY . .

# Limpiar caché de Next.js completamente
RUN rm -rf .next .turbo node_modules/.cache

EXPOSE 3000

ENV PORT 3000
ENV HOSTNAME "0.0.0.0"

# Usar turbopack en lugar de webpack para mejor compatibilidad
CMD ["npm", "run", "dev"]
