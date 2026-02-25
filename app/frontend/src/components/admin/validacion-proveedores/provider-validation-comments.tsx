/**
 * Sección de Comentarios para Validación
 * 
 * Responsabilidades:
 * - Mostrar historial de comentarios del proveedor
 * - Permitir agregar nuevos comentarios
 * - Formatear fechas y autores
 * 
 * TODO: Integrar con API de comentarios del backend
 */

'use client';

import { useState } from 'react';
import { MessageSquare, Send } from 'lucide-react';

// ============================================
// Props Interface
// ============================================

interface ProviderValidationCommentsProps {
  providerId: number;
}

// ============================================
// Mock Data (TODO: Reemplazar con API)
// ============================================

interface Comment {
  id: number;
  author: string;
  text: string;
  date: string;
}

const MOCK_COMMENTS: Comment[] = [
  {
    id: 1,
    author: 'Juan Pérez',
    text: 'Se solicitó documentación adicional de cámara de comercio',
    date: '2025-11-16T10:30:00Z',
  },
  {
    id: 2,
    author: 'María García',
    text: 'Documentos recibidos y validados correctamente',
    date: '2025-11-17T14:20:00Z',
  },
];

// ============================================
// Component
// ============================================

export function ProviderValidationComments({ providerId }: ProviderValidationCommentsProps) {
  void providerId; // TODO: Usar para cargar comentarios desde API
  
  const [comments] = useState<Comment[]>(MOCK_COMMENTS);
  const [newComment, setNewComment] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  /**
   * Handler para agregar comentario
   */
  const handleAddComment = async () => {
    if (!newComment.trim()) return;

    try {
      setIsSubmitting(true);
      
      // TODO: Llamar a API para agregar comentario
      console.log('Agregar comentario:', newComment);
      
      // Simular delay
      await new Promise(resolve => setTimeout(resolve, 500));
      
      // Limpiar input
      setNewComment('');
    } catch (error) {
      console.error('Error al agregar comentario:', error);
    } finally {
      setIsSubmitting(false);
    }
  };

  /**
   * Formatea fecha ISO a formato legible
   */
  const formatDate = (isoDate: string): string => {
    try {
      const date = new Date(isoDate);
      return date.toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });
    } catch {
      return 'Fecha inválida';
    }
  };

  return (
    <div className="space-y-4">
      {/* Header */}
      <div className="flex items-center gap-2">
        <MessageSquare className="w-5 h-5 text-[#4B236A]" />
        <h3 className="text-lg font-semibold text-[#1A1A1A]">
          Historial de Comentarios
        </h3>
      </div>

      {/* Lista de Comentarios */}
      <div className="space-y-3 max-h-64 overflow-y-auto">
        {comments.length === 0 ? (
          <div className="p-6 text-center bg-[#F7F7F7] rounded-xl">
            <p className="text-sm text-[#6A6A6A]">
              No hay comentarios aún
            </p>
          </div>
        ) : (
          comments.map((comment) => (
            <div
              key={comment.id}
              className="p-4 bg-[#F7F7F7] rounded-xl border border-[#E0E0E0]"
            >
              <div className="flex items-baseline justify-between mb-2">
                <span className="text-sm font-medium text-[#1A1A1A]">
                  {comment.author}
                </span>
                <span className="text-xs text-[#6A6A6A]">
                  {formatDate(comment.date)}
                </span>
              </div>
              <p className="text-sm text-[#1A1A1A]">{comment.text}</p>
            </div>
          ))
        )}
      </div>

      {/* Agregar Comentario */}
      <div className="flex gap-2">
        <input
          type="text"
          value={newComment}
          onChange={(e) => setNewComment(e.target.value)}
          onKeyDown={(e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
              e.preventDefault();
              handleAddComment();
            }
          }}
          placeholder="Agregar comentario..."
          disabled={isSubmitting}
          className="flex-1 h-[44px] px-4 border border-[#E0E0E0] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A]"
        />
        <button
          onClick={handleAddComment}
          disabled={isSubmitting || !newComment.trim()}
          className="px-4 h-[44px] bg-[#4B236A] text-white rounded-xl hover:bg-[#5D2B7D] transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
        >
          <Send className="w-4 h-4" />
          {isSubmitting ? 'Enviando...' : 'Enviar'}
        </button>
      </div>
    </div>
  );
}
