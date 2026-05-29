export const PROVIDER_VALIDATION_MESSAGES = {
  historyUnavailable:
    'Historial de validación en proceso de habilitación por backend. Intenta nuevamente más tarde.',
  historyLoadError:
    'No se pudieron cargar los comentarios. Verifica tu conexión e intenta nuevamente.',
  commentContractUnavailable:
    'No fue posible registrar el comentario con el contrato actual del backend.',
  commentSubmitError:
    'No se pudo enviar el comentario. Intenta nuevamente.',
  rejectionCommentBackendPending:
    'Funcionalidad de observaciones detalladas en desarrollo por backend.',
  rejectionCommentSaveError:
    'No se pudo registrar la observación en historial, pero el rechazo fue aplicado.',
} as const;
