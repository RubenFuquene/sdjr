'use client';

import { useState } from 'react';
import { toast } from 'sonner';
import type { BasicInfoFormData, FormErrors } from '@/types/basic-info';
import { INITIAL_BASIC_INFO_FORM } from '@/types/basic-info';
import { createCommerce, createPresignedDocument, confirmDocumentUpload } from '@/lib/api';
import { getSessionFromCookie } from '@/lib/session';
import { basicInfoToProveedorPayload } from '@/types/provider.adapters';
import { uploadFileToPresignedUrl, getBackendMimeType } from '@/lib/utils/document-upload';
import { validateBasicInfoForm } from '@/lib/provider/validations/basic-info';

type DocumentStatus = { status: 'idle' | 'uploading' | 'success' | 'error'; error: string | null };

type DocumentStatusMap = {
  commerceChamber: DocumentStatus;
  legalRepresentativeId: DocumentStatus;
};

type DocumentFiles = {
  commerceChamber: File | null;
  legalRepresentativeId: File | null;
};

type DocumentFileKey = keyof DocumentFiles;

export const useBasicInfoForm = () => {
  const [formData, setFormData] = useState<BasicInfoFormData>(INITIAL_BASIC_INFO_FORM);
  const [errors, setErrors] = useState<FormErrors>({});
  const [isLoading, setIsLoading] = useState(false);
  const [documentFiles, setDocumentFiles] = useState<DocumentFiles>({
    commerceChamber: null,
    legalRepresentativeId: null,
  });
  const [documentStatus, setDocumentStatus] = useState<DocumentStatusMap>({
    commerceChamber: { status: 'idle', error: null },
    legalRepresentativeId: { status: 'idle', error: null },
  });

  const handleFieldChange = (fieldPath: string, value: string | number | null) => {
    setFormData((prev) => {
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

      return {
        ...prev,
        [fieldPath]: value,
      };
    });

    if (errors[fieldPath as keyof FormErrors]) {
      setErrors((prev) => {
        const newErrors = { ...prev };
        delete newErrors[fieldPath as keyof FormErrors];
        return newErrors;
      });
    }
  };

  const validateForm = (): boolean => {
    const newErrors = validateBasicInfoForm(formData);

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

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
    statusKey: DocumentFileKey
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

  const handleDocumentFileSelected = (key: DocumentFileKey, file: File | null) => {
    setDocumentFiles((prev) => ({ ...prev, [key]: file }));
  };

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
  };

  return {
    documentStatus,
    errors,
    formData,
    handleCancel,
    handleDocumentFileSelected,
    handleFieldChange,
    handleSubmit,
    isLoading,
  };
};
