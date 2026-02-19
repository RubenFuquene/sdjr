/**
 * Acciones de Aprobación/Rechazo
 * 
 * Responsabilidades:
 * - Botones de aprobar/rechazar proveedor
 * - Modal de confirmación para rechazo (con observaciones)
 * - Manejo de estados (loading, success, error)
 * 
 * TODO: Integrar con API de validación del backend
 */

'use client';

import { useState } from 'react';
import { CheckCircle, XCircle } from 'lucide-react';

// ============================================
// Props Interface
// ============================================

interface ProviderValidationActionsProps {
  providerId: number;
  onApprove: (providerId: number) => Promise<void>;
  onReject: (providerId: number, reason: string) => Promise<void>;
}

// ============================================
// Component
// ============================================

export function ProviderValidationActions({
  providerId,
  onApprove,
  onReject,
}: ProviderValidationActionsProps) {
  const [isApproving, setIsApproving] = useState(false);
  const [isRejectDialogOpen, setIsRejectDialogOpen] = useState(false);
  const [rejectReason, setRejectReason] = useState('');
  const [isRejecting, setIsRejecting] = useState(false);

  /**
   * Handler para aprobar proveedor
   */
  const handleApprove = async () => {
    if (!confirm('¿Estás seguro de aprobar este proveedor?')) return;

    try {
      setIsApproving(true);
      await onApprove(providerId);
    } catch (error) {
      console.error('Error al aprobar:', error);
      alert('Error al aprobar proveedor');
    } finally {
      setIsApproving(false);
    }
  };

  /**
   * Handler para rechazar proveedor (con observaciones)
   */
  const handleReject = async () => {
    if (!rejectReason.trim()) {
      alert('Debes proporcionar una razón de rechazo');
      return;
    }

    try {
      setIsRejecting(true);
      await onReject(providerId, rejectReason);
      
      // Limpiar y cerrar dialog
      setRejectReason('');
      setIsRejectDialogOpen(false);
    } catch (error) {
      console.error('Error al rechazar:', error);
      alert('Error al rechazar proveedor');
    } finally {
      setIsRejecting(false);
    }
  };

  return (
    <>
      <div className="flex gap-3">
        {/* Botón Rechazar */}
        <button
          onClick={() => setIsRejectDialogOpen(true)}
          disabled={isApproving || isRejecting}
          className="flex-1 h-[52px] flex items-center justify-center gap-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl"
        >
          <XCircle className="w-5 h-5" />
          Rechazar con Observaciones
        </button>

        {/* Botón Aprobar */}
        <button
          onClick={handleApprove}
          disabled={isApproving || isRejecting}
          className="flex-1 h-[52px] flex items-center justify-center gap-2 bg-[#4B236A] text-white rounded-xl hover:bg-[#5D2B7D] transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl"
        >
          <CheckCircle className="w-5 h-5" />
          {isApproving ? 'Aprobando...' : 'Aprobar Proveedor'}
        </button>
      </div>

      {/* Dialog de Rechazo */}
      {isRejectDialogOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
          <div className="bg-white rounded-[18px] shadow-2xl w-full max-w-md p-6">
            <h3 className="text-xl font-semibold text-[#1A1A1A] mb-2">
              Rechazar Proveedor
            </h3>
            <p className="text-sm text-[#6A6A6A] mb-4">
              Proporciona una razón detallada del rechazo
            </p>

            <textarea
              value={rejectReason}
              onChange={(e) => setRejectReason(e.target.value)}
              placeholder="Describe las razones del rechazo..."
              rows={4}
              className="w-full px-4 py-3 border border-[#E0E0E0] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4B236A] resize-none"
            />

            <div className="flex gap-3 mt-4">
              <button
                onClick={() => {
                  setIsRejectDialogOpen(false);
                  setRejectReason('');
                }}
                disabled={isRejecting}
                className="flex-1 h-[44px] border border-[#E0E0E0] text-[#1A1A1A] rounded-xl hover:bg-[#F7F7F7] transition-colors disabled:opacity-50"
              >
                Cancelar
              </button>
              <button
                onClick={handleReject}
                disabled={isRejecting || !rejectReason.trim()}
                className="flex-1 h-[44px] bg-red-500 text-white rounded-xl hover:bg-red-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {isRejecting ? 'Rechazando...' : 'Confirmar Rechazo'}
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
