import { useCallback, useEffect, useState } from 'react';
import {
  ApiError,
  createCommerceComment,
  getCommerceComments,
  getMyCommerce,
  type CommerceCommentFromAPI,
} from '@/lib/api';
import {
  normalizeCommerceVerificationStatus,
  type CommerceVerificationStatus,
} from '@/types/commerces';

/**
 * Estados de verificación en los que el hilo de mensajes está activo
 * (comercio en ruta de aprobación): pendiente (0) y "por aprobar nuevamente" (3).
 */
const MESSAGING_STATES: CommerceVerificationStatus[] = [0, 3];

const MESSAGES = {
  loadError: 'No se pudieron cargar los mensajes. Intenta nuevamente más tarde.',
  noCommerce: 'Aún no tienes un comercio asociado.',
  sendError: 'No se pudo enviar el mensaje. Intenta nuevamente.',
  channelClosed:
    'El canal de mensajes solo está disponible mientras tu comercio está en revisión.',
} as const;

interface UseProviderMessagesReturn {
  messages: CommerceCommentFromAPI[];
  /** Id del usuario dueño del comercio: sirve para alinear los mensajes propios. */
  ownerUserId: number | null;
  /** Estado de verificación del comercio del proveedor (null mientras carga). */
  verificationStatus: CommerceVerificationStatus | null;
  /** true si el comercio está en ruta de aprobación y admite mensajes. */
  canMessage: boolean;
  isLoading: boolean;
  loadError: string | null;
  isSending: boolean;
  sendError: string | null;
  reload: () => Promise<void>;
  sendMessage: (text: string) => Promise<void>;
}

/**
 * Encapsula el hilo de mensajes admin↔proveedor de la ruta de revisión:
 * resuelve el comercio del proveedor, lista los mensajes visibles (MS/RJ)
 * y permite enviar mensajes (MS) mientras el comercio está en revisión.
 */
export function useProviderMessages(): UseProviderMessagesReturn {
  const [commerceId, setCommerceId] = useState<number | null>(null);
  const [ownerUserId, setOwnerUserId] = useState<number | null>(null);
  const [verificationStatus, setVerificationStatus] =
    useState<CommerceVerificationStatus | null>(null);
  const [messages, setMessages] = useState<CommerceCommentFromAPI[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [isSending, setIsSending] = useState(false);
  const [sendError, setSendError] = useState<string | null>(null);

  const reload = useCallback(async () => {
    try {
      setIsLoading(true);
      setLoadError(null);

      const commerceResponse = await getMyCommerce();
      const commerce = commerceResponse.data;

      if (!commerce?.id) {
        setCommerceId(null);
        setOwnerUserId(null);
        setVerificationStatus(null);
        setMessages([]);
        setLoadError(MESSAGES.noCommerce);
        return;
      }

      setCommerceId(commerce.id);
      setOwnerUserId(commerce.owner_user?.id ?? null);
      setVerificationStatus(
        normalizeCommerceVerificationStatus(commerce.is_verified)
      );

      const commentsResponse = await getCommerceComments(commerce.id, {
        perPage: 50,
        page: 1,
      });
      setMessages(commentsResponse.data);
    } catch {
      setLoadError(MESSAGES.loadError);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    void reload();
  }, [reload]);

  const canMessage =
    verificationStatus !== null && MESSAGING_STATES.includes(verificationStatus);

  const sendMessage = useCallback(
    async (text: string) => {
      const body = text.trim();
      if (!body || commerceId === null) return;

      if (!canMessage) {
        setSendError(MESSAGES.channelClosed);
        return;
      }

      try {
        setIsSending(true);
        setSendError(null);

        await createCommerceComment(commerceId, {
          comment: body,
          priority_type_id: 1,
          comment_type: 'MS',
          status: '1',
        });

        await reload();
      } catch (error) {
        setSendError(
          error instanceof ApiError && error.status === 422
            ? MESSAGES.channelClosed
            : MESSAGES.sendError
        );
        throw error;
      } finally {
        setIsSending(false);
      }
    },
    [commerceId, canMessage, reload]
  );

  return {
    messages,
    ownerUserId,
    verificationStatus,
    canMessage,
    isLoading,
    loadError,
    isSending,
    sendError,
    reload,
    sendMessage,
  };
}
