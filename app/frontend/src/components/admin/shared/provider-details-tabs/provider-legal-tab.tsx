/**
 * Tab: Informacion Legal del Proveedor
 *
 * Responsabilidades:
 * - Mostrar estado de aceptacion de terminos
 * - Mostrar fecha y version aceptada (read-only)
 */

'use client';

import { AlertCircle, Calendar, CheckCircle2, FileCheck } from 'lucide-react';
import type { Proveedor } from '@/types/admin';

interface ProviderLegalTabProps {
  formData: Proveedor;
}

export function ProviderLegalTab({ formData }: ProviderLegalTabProps) {
  const legal = formData.legal || {
    aceptoTerminos: false,
    fechaAceptacion: '',
    termsAcceptedVersion: null,
  };

  const formatFecha = (isoDate: string): string => {
    if (!isoDate) return 'No disponible';

    try {
      const date = new Date(isoDate);
      if (Number.isNaN(date.getTime())) {
        return 'No disponible';
      }

      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const year = date.getFullYear();
      const hour = String(date.getHours()).padStart(2, '0');
      const minute = String(date.getMinutes()).padStart(2, '0');

      return `${day}/${month}/${year} ${hour}:${minute}`;
    } catch {
      return 'No disponible';
    }
  };

  return (
    <div className="bg-white rounded-[18px] shadow-sm p-6 border border-[#E0E0E0]">
      <div className="flex items-center gap-3 mb-6">
        <div className="w-10 h-10 bg-[#DDE8BB] rounded-lg flex items-center justify-center">
          <FileCheck className="w-5 h-5 text-[#4B236A]" />
        </div>
        <h3 className="text-[#1A1A1A] font-semibold">Términos y Condiciones</h3>
      </div>

      <div className="space-y-4">
        <div
          className={`flex items-center justify-between p-4 rounded-xl border ${
            legal.aceptoTerminos
              ? 'bg-emerald-50 border-emerald-200'
              : 'bg-amber-50 border-amber-200'
          }`}
        >
          <div className="flex items-center gap-3">
            {legal.aceptoTerminos ? (
              <CheckCircle2 className="w-6 h-6 text-emerald-600" />
            ) : (
              <AlertCircle className="w-6 h-6 text-amber-600" />
            )}

            <div>
              <p className="text-[#1A1A1A] font-medium">
                {legal.aceptoTerminos
                  ? 'Términos y Condiciones Aceptados'
                  : 'Términos y Condiciones Pendientes'}
              </p>
              <p className="text-[#6A6A6A] text-sm">
                Fecha de aceptación: {formatFecha(legal.fechaAceptacion)}
              </p>
            </div>
          </div>

          <span
            className={`px-4 py-2 rounded-xl text-white text-sm ${
              legal.aceptoTerminos ? 'bg-[#10B981]' : 'bg-[#D97706]'
            }`}
          >
            {legal.aceptoTerminos ? 'Aceptado' : 'Pendiente'}
          </span>
        </div>

        <div className="p-4 bg-[#F7F7F7] rounded-xl border border-[#E0E0E0]">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-[#4B236A]/10 rounded-lg flex items-center justify-center">
              <Calendar className="w-5 h-5 text-[#4B236A]" />
            </div>
            <div>
              <p className="text-[#6A6A6A] text-sm">Versión aceptada</p>
              <p className="text-[#1A1A1A] font-medium">
                {legal.termsAcceptedVersion ?? 'No disponible'}
              </p>
            </div>
          </div>
        </div>

        {/* TODO: Implementar seccion de documentos legales cuando negocio confirme contenidos y rutas finales. */}
      </div>
    </div>
  );
}
