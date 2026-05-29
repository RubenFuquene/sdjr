import { useCallback, useEffect, useState } from 'react';
import {
  ApiError,
  createCommerceComment,
  getCommerceComments,
  type CommerceCommentFromAPI,
} from '@/lib/api';
import { PROVIDER_VALIDATION_MESSAGES } from '@/components/admin/validacion-proveedores/provider-validation-messages';

interface UseProviderValidationCommentsReturn {
  comments: CommerceCommentFromAPI[];
  isLoading: boolean;
  loadError: string | null;
  isSubmitting: boolean;
  submitError: string | null;
  reloadComments: () => Promise<void>;
  addComment: (comment: string) => Promise<void>;
}

/**
 * Hook que encapsula el historial de comentarios de validación:
 * carga inicial, recarga manual y creación de comentarios.
 */
export function useProviderValidationComments(
  providerId: number,
  refreshTrigger = 0
): UseProviderValidationCommentsReturn {
  const [comments, setComments] = useState<CommerceCommentFromAPI[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitError, setSubmitError] = useState<string | null>(null);

  const reloadComments = useCallback(async () => {
    try {
      setIsLoading(true);
      setLoadError(null);

      const response = await getCommerceComments(providerId, { perPage: 20, page: 1 });
      setComments(response.data);
    } catch (error) {
      if (error instanceof ApiError && (error.status === 403 || error.status === 404)) {
        setLoadError(PROVIDER_VALIDATION_MESSAGES.historyUnavailable);
      } else {
        setLoadError(PROVIDER_VALIDATION_MESSAGES.historyLoadError);
      }
    } finally {
      setIsLoading(false);
    }
  }, [providerId]);

  useEffect(() => {
    void reloadComments();
  }, [reloadComments, refreshTrigger]);

  const addComment = useCallback(
    async (comment: string) => {
      const commentText = comment.trim();
      if (!commentText) return;

      try {
        setIsSubmitting(true);
        setSubmitError(null);

        await createCommerceComment(providerId, {
          comment: commentText,
          priority_type_id: 1,
          comment_type: 'VA',
          status: '1',
        });

        await reloadComments();
      } catch (error) {
        if (error instanceof ApiError && error.status === 422) {
          setSubmitError(PROVIDER_VALIDATION_MESSAGES.commentContractUnavailable);
        } else {
          setSubmitError(PROVIDER_VALIDATION_MESSAGES.commentSubmitError);
        }
        throw error;
      } finally {
        setIsSubmitting(false);
      }
    },
    [providerId, reloadComments]
  );

  return {
    comments,
    isLoading,
    loadError,
    isSubmitting,
    submitError,
    reloadComments,
    addComment,
  };
}
