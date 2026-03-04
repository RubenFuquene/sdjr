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
import { CheckCircle, XCircle, AlertCircle } from 'lucide-react';
import { ConfirmationDialog } from '@/components/admin/shared/confirmation-dialog';
import { useCommerceApproval } from '@/hooks/use-commerce-approval';

// ============================================
// Props Interface
// ============================================

interface ProviderValidationActionsProps {
  providerId: number;
  onApprovalSuccess?: (message: string) => void;
  onApprovalError?: (error: string) => void;
}

// ============================================
// Component
// ============================================

export function ProviderValidationActions({
  providerId,
  onApprovalSuccess,
  onApprovalError,
}: ProviderValidationActionsProps) {
  const { approveProvider, rejectProvider, isLoading, error } = useCommerceApproval();
  
  const [isApproveDialogOpen, setIsApproveDialogOpen] = useState(false);
  const [isRejectDialogOpen, setIsRejectDialogOpen] = useState(false);
  const [rejectReason, setRejectReason] = useState('');
  const [approvalError, setApprovalError] = useState<string | null>(null);

  const isProcessing = isLoading;

  /**
   * Handler para aprobar proveedor
   */
  const handleApprove = async () => {
    try {
      setApprovalError(null);
      await approveProvider(providerId);
      setIsApproveDialogOpen(false);
      onApprovalSuccess?.('Proveedor aprobado exitosamente');
    } catch (err) {
      const mensaje = err instanceof Error ? err.message : 'Error desconocido';
      setApprovalError(mensaje);
      onApprovalError?.(mensaje);
      console.error('Error al aprobar:', err);
    }
  };

  /**
   * Handler para rechazar proveedor
   * 
   * Nota: Las observaciones se guardan localmente pero no se envían al backend
   * (el endpoint de verificación no acepta observaciones)
   * Para futuro: Crear endpoint separado PQRS/Comments para almacenar observaciones
   */
  const handleReject = async () => {
    if (!rejectReason.trim()) {
      setApprovalError('Debes proporcionar una razón de rechazo');
      return;
    }

    try {
      setApprovalError(null);
      await rejectProvider(providerId);
      
      // Limpiar y cerrar dialog
      setRejectReason('');
      setIsRejectDialogOpen(false);
      onApprovalSuccess?.('Proveedor rechazado exitosamente');
    } catch (err) {
      const mensaje = err instanceof Error ? err.message : 'Error desconocido';
      setApprovalError(mensaje);
      onApprovalError?.(mensaje);
      console.error('Error al rechazar:', err);
    }
  };

  return (
    <>
      <div className="flex gap-3">
        {/* Botón Rechazar */}
        <button
          onClick={() => setIsRejectDialogOpen(true)}
          disabled={isProcessing}
          className="flex-1 h-[52px] flex items-center justify-center gap-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl"
        >
          <XCircle className="w-5 h-5" />
          Rechazar con Observaciones
        </button>

        {/* Botón Aprobar */}
        <button
          onClick={() => setIsApproveDialogOpen(true)}
          disabled={isProcessing}
          className="flex-1 h-[52px] flex items-center justify-center gap-2 bg-[#4B236A] text-white rounded-xl hover:bg-[#5D2B7D] transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl"
        >
          <CheckCircle className="w-5 h-5" />
          {isProcessing ? 'Procesando...' : 'Aprobar Proveedor'}
        </button>
      </div>

      {/* Dialog de Aprobación */}
      <ConfirmationDialog
        isOpen={isApproveDialogOpen}
        title="Aprobar Proveedor"
        description="Una vez aprobado, el proveedor tendrá acceso a la plataforma. Esta acción puede ser revertida después si es necesario."
        confirmText="Sí, Aprobar"
        cancelText="Cancelar"
        variant="info"
        isLoading={isProcessing}
        onConfirm={handleApprove}
        onClose={() => {
          setIsApproveDialogOpen(false);
          setApprovalError(null);
        }}
      />

      {/* Error Banner */}
      {approvalError && (
        <div className="fixed bottom-4 right-4 z-40 max-w-md bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
          <AlertCircle className="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" />
          <div className="flex-1">
            <p className="text-sm font-medium text-red-900">Error</p>
            <p className="text-sm text-red-700">{approvalError}</p>
          </div>
          <button
            onClick={() => setApprovalError(null)}
            className="text-red-400 hover:text-red-600"
          >
            ✕
          </button>
        </div>
      )}

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
              disabled={isProcessing}
              className="w-full px-4 py-3 border border-[#E0E0E0] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4B236A] resize-none disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A]"
            />

            <div className="flex gap-3 mt-4">
              <button
                onClick={() => {
                  setIsRejectDialogOpen(false);
                  setRejectReason('');
                  setApprovalError(null);
                }}
                disabled={isProcessing}
                className="flex-1 h-[44px] border border-[#E0E0E0] text-[#1A1A1A] rounded-xl hover:bg-[#F7F7F7] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Cancelar
              </button>
              <button
                onClick={handleReject}
                disabled={isProcessing || !rejectReason.trim()}
                className="flex-1 h-[44px] bg-red-500 text-white rounded-xl hover:bg-red-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
              >
                {isProcessing && (
                  <span className="w-4 h-4 border-2 border-white/60 border-t-transparent rounded-full animate-spin" />
                )}
                {isProcessing ? 'Rechazando...' : 'Confirmar Rechazo'}
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
