/**
 * Página de Validación de Proveedores
 * 
 * Ruta: /admin/validacion
 * Propósito: Revisar y aprobar/rechazar solicitudes de proveedores pendientes
 * 
 * Arquitectura:
 * - Layout de 2 columnas (sidebar + detalle)
 * - Reutiliza tabs existentes de provider-tabs
 * - Agrega sección de comentarios y acciones de aprobación
 */

import { ProviderValidationLayout } from "@/components/admin/validacion-proveedores";

// Forzar renderizado dinámico (página usa cookies para autenticación)
export const dynamic = 'force-dynamic';

export const metadata = {
  title: "Validación de Proveedores | Admin | Sumass",
  description: "Revisa y aprueba solicitudes de proveedores pendientes",
};

export default async function ValidacionProveedoresPage() {
  return <ProviderValidationLayout />;
}
