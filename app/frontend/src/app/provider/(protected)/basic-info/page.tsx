'use client';

import { useState } from 'react';
import { toast } from 'sonner';
import { EstablecimientoCard, RepresentanteLegalCard, CamaraComercioCard, ObservacionesCard } from '@/components/provider/forms';
import type { BasicInfoFormData, FormErrors } from '@/types/basic-info';
import { INITIAL_BASIC_INFO_FORM } from '@/types/basic-info';
import { createCommerce, createPresignedDocument, confirmDocumentUpload } from '@/lib/api';
import { getSessionFromCookie } from '@/lib/session';
import { basicInfoToProveedorPayload } from '@/types/provider.adapters';
import { uploadFileToPresignedUrl, getBackendMimeType } from '@/lib/utils/document-upload';

/**
 * Página: Datos Básicos del Proveedor
 *
 * Estructura:
 * - Header con título y subtítulo
 * - 4 Cards principales (una por cada sección)
 * - Botones de acción (Cancelar, Guardar y Continuar)
 *
 * Este es un Client Component que maneja el estado del formulario.
 */
export default function BasicInfoPage() {
  const [formData, setFormData] = useState<BasicInfoFormData>(INITIAL_BASIC_INFO_FORM);
  const [errors, setErrors] = useState<FormErrors>({});
  const [isLoading, setIsLoading] = useState(false);
  const [documentFiles, setDocumentFiles] = useState<{
    commerceChamber: File | null;
    legalRepresentativeId: File | null;
  }>({
    commerceChamber: null,
    legalRepresentativeId: null,
  });
  const [documentStatus, setDocumentStatus] = useState<{
    commerceChamber: { status: 'idle' | 'uploading' | 'success' | 'error'; error: string | null };
    legalRepresentativeId: { status: 'idle' | 'uploading' | 'success' | 'error'; error: string | null };
  }>({
    commerceChamber: { status: 'idle', error: null },
    legalRepresentativeId: { status: 'idle', error: null },
  });

  /**
   * Actualiza un campo del formulario
   * Soporta nested updates: 'legalRepresentative.firstName' actualiza formData.legalRepresentative.firstName
   */
  const handleFieldChange = (fieldPath: string, value: string | number | null) => {
    setFormData((prev) => {
      // Handle nested fields like "legalRepresentative.firstName"
      if (fieldPath.includes('.')) {
        const [parent, child] = fieldPath.split('.');
        const parentKey = parent as keyof BasicInfoFormData;
        const parentObj = prev[parentKey] as unknown;
        if (!parentObj || typeof parentObj !== 'object') return prev;
        return {
          ...prev,
          [parentKey]: {
            ...(parentObj as Record<string, unknown>),
            [child]: value,
          },
        };
      }

      // Handle top-level fields
      return {
        ...prev,
        [fieldPath]: value,
      };
    });

    // Clear error for this field when user starts typing
    if (errors[fieldPath as keyof FormErrors]) {
      setErrors((prev) => {
        const newErrors = { ...prev };
        delete newErrors[fieldPath as keyof FormErrors];
        return newErrors;
      });
    }
  };

  /**
   * Valida el formulario antes de guardar
   * Retorna true si es válido, false si hay errores
   */
  const validateForm = (): boolean => {
    const newErrors: Partial<FormErrors> = {};

    // ============================================
    // Validaciones del Establecimiento
    // ============================================

    if (!formData.commercialName?.trim()) {
      newErrors.commercialName = 'El nombre comercial es obligatorio';
    }

    if (!formData.documentType) {
      newErrors.documentType = 'Debe seleccionar un tipo de documento';
    }

    if (!formData.documentNumber?.trim()) {
      newErrors.documentNumber = 'El número de documento es obligatorio';
    } else if (!/^[\d\-]+$/.test(formData.documentNumber)) {
      newErrors.documentNumber = 'El número de documento solo debe contener números y guiones';
    }

    if (!formData.establishmentType) {
      newErrors.establishmentType = 'Debe seleccionar un tipo de establecimiento';
    }

    if (!formData.phone?.trim()) {
      newErrors.phone = 'El teléfono es obligatorio';
    } else if (!/^\d{10}$/.test(formData.phone)) {
      newErrors.phone = 'El teléfono debe tener exactamente 10 dígitos';
    }

    if (!formData.email?.trim()) {
      newErrors.email = 'El correo electrónico es obligatorio';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      newErrors.email = 'El correo electrónico no es válido';
    }

    if (!formData.departmentId) {
      newErrors.departmentId = 'Debe seleccionar un departamento';
    }

    if (!formData.cityId) {
      newErrors.cityId = 'Debe seleccionar una ciudad';
    }

    if (!formData.neighborhood?.trim()) {
      newErrors.neighborhood = 'El barrio es obligatorio';
    }

    if (!formData.mainAddress?.trim()) {
      newErrors.mainAddress = 'La dirección principal es obligatoria';
    }

    // ============================================
    // Validaciones del Representante Legal
    // ============================================

    if (!formData.legalRepresentative.firstName?.trim()) {
      newErrors.legalRepresentativeFirstName = 'El nombre del representante es obligatorio';
    }

    if (!formData.legalRepresentative.lastName?.trim()) {
      newErrors.legalRepresentativeLastName = 'El apellido del representante es obligatorio';
    }

    if (!formData.legalRepresentative.documentType) {
      newErrors.legalRepresentativeDocumentType = 'Debe seleccionar un tipo de documento';
    }

    if (!formData.legalRepresentative.documentNumber?.trim()) {
      newErrors.legalRepresentativeDocumentNumber = 'El número de documento es obligatorio';
    } else if (!/^\d+$/.test(formData.legalRepresentative.documentNumber)) {
      newErrors.legalRepresentativeDocumentNumber = 'El número de documento solo debe contener dígitos';
    }

    if (!formData.legalRepresentative.documentFile?.trim()) {
      newErrors.legalRepresentativeDocumentFile = 'El documento del representante es obligatorio';
    }

    // ============================================
    // Validaciones de Documentos
    // ============================================

    if (!formData.documents.commerceChamber?.trim()) {
      newErrors.commerceChamber = 'El documento de Cámara de Comercio es obligatorio';
    }

    // ============================================
    // Actualizar estado de errores
    // ============================================

    setErrors(newErrors as FormErrors);
    return Object.keys(newErrors).length === 0;
  };

  /**
   * Maneja el envío del formulario
   */
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    // Validar el formulario
    if (!validateForm()) {
      toast.error('Formulario inválido', {
        description: 'Por favor, completa todos los campos requeridos correctamente',
      });
      console.log('Formulario inválido, errores:', errors);
      return;
    }

    try {
      setIsLoading(true);
      setDocumentStatus({
        commerceChamber: { status: 'idle', error: null },
        legalRepresentativeId: { status: 'idle', error: null },
      });
      const session = getSessionFromCookie();
      const ownerUserId = session?.userId ? Number.parseInt(session.userId, 10) : Number.NaN;
      if (Number.isNaN(ownerUserId)) {
        throw new Error('missing_owner_user_id');
      }

      const payload = basicInfoToProveedorPayload(formData, ownerUserId);
      const commerceResponse = await createCommerce(payload);
      const commerceId = commerceResponse.data.id;

      await uploadCommerceDocuments(commerceId);

      toast.success('Datos básicos guardados correctamente', {
        description: 'Procederemos con la siguiente sección de tu registro',
      });
      // TODO: Navegar a siguiente página
    } catch (err) {
      console.error('Error al guardar:', err);
      const errorMessage = err instanceof Error ? err.message : '';
      if (errorMessage === 'missing_owner_user_id') {
        toast.error('Sesión inválida', {
          description: 'No pudimos identificar tu usuario. Vuelve a iniciar sesión.',
        });
        return;
      }
      if (errorMessage === 'neighborhood_id_invalid') {
        toast.error('Barrio inválido', {
          description: 'Selecciona un barrio válido desde la lista.',
        });
        return;
      }
      toast.error('Error al guardar', {
        description: 'Ocurrió un error al intentar guardar los datos. Por favor, intenta de nuevo.',
      });
    } finally {
      setIsLoading(false);
    }
  };

  const uploadCommerceDocuments = async (commerceId: number) => {
    const uploads: Array<Promise<void>> = [];

    if (documentFiles.commerceChamber) {
      uploads.push(
        uploadDocumentForCommerce(
          commerceId,
          documentFiles.commerceChamber,
          'CAMARA_COMERCIO',
          'commerceChamber'
        )
      );
    }

    if (documentFiles.legalRepresentativeId) {
      uploads.push(
        uploadDocumentForCommerce(
          commerceId,
          documentFiles.legalRepresentativeId,
          'ID_CARD',
          'legalRepresentativeId'
        )
      );
    }

    if (uploads.length > 0) {
      await Promise.all(uploads);
    }
  };

  const uploadDocumentForCommerce = async (
    commerceId: number,
    file: File,
    documentType: 'CAMARA_COMERCIO' | 'ID_CARD',
    statusKey: 'commerceChamber' | 'legalRepresentativeId'
  ) => {
    setDocumentStatus((prev) => ({
      ...prev,
      [statusKey]: { status: 'uploading', error: null },
    }));
    const backendMimeType = getBackendMimeType(file);
    if (!backendMimeType) {
      setDocumentStatus((prev) => ({
        ...prev,
        [statusKey]: { status: 'error', error: 'Tipo de archivo no permitido.' },
      }));
      throw new Error('invalid_document_type');
    }

    try {
      const presignedResponse = await createPresignedDocument({
        document_type: documentType,
        file_name: file.name,
        mime_type: backendMimeType,
        file_size_bytes: file.size,
        commerce_id: commerceId,
      });

      const uploadResult = await uploadFileToPresignedUrl(
        file,
        presignedResponse.data.presigned_url,
        { contentType: file.type }
      );

      await confirmDocumentUpload({
        upload_token: presignedResponse.data.upload_token,
        s3_metadata: uploadResult,
      });

      setDocumentStatus((prev) => ({
        ...prev,
        [statusKey]: { status: 'success', error: null },
      }));
    } catch (error) {
      setDocumentStatus((prev) => ({
        ...prev,
        [statusKey]: {
          status: 'error',
          error: 'No se pudo cargar el documento. Intenta nuevamente.',
        },
      }));
      throw error;
    }
  };

  /**
   * Reinicia el formulario
   */
  const handleCancel = () => {
    setFormData(INITIAL_BASIC_INFO_FORM);
    setErrors({});
    setDocumentFiles({ commerceChamber: null, legalRepresentativeId: null });
    setDocumentStatus({
      commerceChamber: { status: 'idle', error: null },
      legalRepresentativeId: { status: 'idle', error: null },
    });
    toast.info('Cambios descartados', {
      description: 'El formulario ha sido reiniciado',
    });
    // TODO: Navegar a página anterior o mostrar confirmación
  };

  return (
    <div className="min-h-screen bg-gray-50 p-6 md:p-8">
      <form onSubmit={handleSubmit} className="max-w-4xl mx-auto">
        {/* ============================================ */}
        {/* Header Section */}
        {/* ============================================ */}
        <div className="mb-8">
          <h1 className="text-2xl md:text-3xl font-bold text-gray-900">
            Datos Básicos del Proveedor
          </h1>
          <p className="text-gray-600 mt-2">
            Completa la información de tu negocio para comenzar
          </p>
        </div>

        {/* ============================================ */}
        {/* Card 1: Información del Establecimiento */}
        {/* ============================================ */}
        <EstablecimientoCard
          formData={formData}
          onFieldChange={handleFieldChange}
          errors={errors}
        />

        {/* ============================================ */}
        {/* Card 2: Representante Legal */}
        {/* ============================================ */}
        <RepresentanteLegalCard
          formData={formData}
          onFieldChange={handleFieldChange}
          onFileSelected={(file) =>
            setDocumentFiles((prev) => ({ ...prev, legalRepresentativeId: file }))
          }
          uploadStatus={documentStatus.legalRepresentativeId.status}
          uploadError={documentStatus.legalRepresentativeId.error}
          errors={errors}
        />

        {/* ============================================ */}
        {/* Card 3: Cámara de Comercio */}
        {/* ============================================ */}
        <CamaraComercioCard
          formData={formData}
          onFieldChange={handleFieldChange}
          onFileSelected={(file) =>
            setDocumentFiles((prev) => ({ ...prev, commerceChamber: file }))
          }
          uploadStatus={documentStatus.commerceChamber.status}
          uploadError={documentStatus.commerceChamber.error}
          errors={errors}
        />

        {/* ============================================ */}
        {/* Card 4: Observaciones */}
        {/* ============================================ */}
        <ObservacionesCard
          formData={formData}
          onFieldChange={handleFieldChange}
        />

        {/* ============================================ */}
        {/* Action Buttons */}
        {/* ============================================ */}
        <div className="flex justify-end gap-3">
          <button
            type="button"
            onClick={handleCancel}
            className="px-6 h-[48px] rounded-[14px] border border-[#DDE8BB] text-[#4B236A] hover:bg-[#DDE8BB] transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
            disabled={isLoading}
          >
            Cancelar
          </button>
          <button
            type="submit"
            className="px-6 h-[52px] rounded-[14px] bg-[#4B236A] hover:bg-[#5D2B7D] text-white font-medium shadow-md hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            disabled={isLoading}
          >
            {isLoading ? 'Guardando...' : 'Guardar y Continuar'}
          </button>
        </div>
      </form>
    </div>
  );
}
