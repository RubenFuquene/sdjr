'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { toast } from 'sonner';
import type { BasicInfoFormData, FormErrors } from '@/types/basic-info';
import { INITIAL_BASIC_INFO_FORM } from '@/types/basic-info';
import {
  createCommerceBasic,
  createPresignedDocument,
  confirmDocumentUpload,
  updateCommerce,
} from '@/lib/api';
import { getSessionFromCookie } from '@/lib/session';
import { basicInfoToCommerceBasicPayload } from '@/types/commerces.adapters';
import { uploadFileToPresignedUrl, getBackendMimeType } from '@/lib/utils/document-upload';
import { validateBasicInfoForm } from '@/lib/provider/validations/basic-info';
import type { CommerceFromAPI } from '@/types/commerces';
import { useProviderCommerce } from '@/components/provider/context/provider-commerce-context';

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
  const router = useRouter();
  const { commerce, isLoadingCommerce, refreshCommerce } = useProviderCommerce();
  const [formData, setFormData] = useState<BasicInfoFormData>(INITIAL_BASIC_INFO_FORM);
  const [errors, setErrors] = useState<FormErrors>({});
  const [isLoading, setIsLoading] = useState(false);
  const [existingCommerceId, setExistingCommerceId] = useState<number | null>(null);
  const [documentFiles, setDocumentFiles] = useState<DocumentFiles>({
    commerceChamber: null,
    legalRepresentativeId: null,
  });
  const [documentStatus, setDocumentStatus] = useState<DocumentStatusMap>({
    commerceChamber: { status: 'idle', error: null },
    legalRepresentativeId: { status: 'idle', error: null },
  });

  useEffect(() => {
    if (isLoadingCommerce || !commerce) {
      return;
    }

    setExistingCommerceId(commerce.id);
    setFormData(mapCommerceToBasicInfoForm(commerce));
  }, [commerce, isLoadingCommerce]);

  const getErrorKeysForFieldPath = (fieldPath: string): Array<keyof FormErrors> => {
    const nestedFieldMap: Record<string, keyof FormErrors> = {
      'legalRepresentative.firstName': 'legalRepresentativeFirstName',
      'legalRepresentative.lastName': 'legalRepresentativeLastName',
      'legalRepresentative.documentType': 'legalRepresentativeDocumentType',
      'legalRepresentative.documentNumber': 'legalRepresentativeDocumentNumber',
      'legalRepresentative.documentFile': 'legalRepresentativeDocumentFile',
      'documents.identity': 'identity',
      'documents.commerceChamber': 'commerceChamber',
    };

    if (nestedFieldMap[fieldPath]) {
      return [nestedFieldMap[fieldPath]];
    }

    if (!fieldPath.includes('.')) {
      return [fieldPath as keyof FormErrors];
    }

    return [];
  };

  const clearFieldErrors = (fieldPath: string) => {
    const keys = getErrorKeysForFieldPath(fieldPath);

    if (keys.length === 0) {
      return;
    }

    setErrors((prev) => {
      let changed = false;
      const newErrors = { ...prev };

      keys.forEach((key) => {
        if (newErrors[key]) {
          delete newErrors[key];
          changed = true;
        }
      });

      return changed ? newErrors : prev;
    });
  };

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

    clearFieldErrors(fieldPath);
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

      const payload = basicInfoToCommerceBasicPayload(formData, ownerUserId);
      let commerceId = existingCommerceId;

      if (commerceId) {
        const establishmentTypeId = Number.parseInt(formData.establishmentType, 10);

        await updateCommerce(commerceId, {
          owner_user_id: ownerUserId,
          name: payload.commerce.name,
          description: payload.commerce.description,
          establishment_type_id: Number.isNaN(establishmentTypeId)
            ? undefined
            : establishmentTypeId,
          tax_id: payload.commerce.tax_id,
          tax_id_type: payload.commerce.tax_id_type,
          address: payload.commerce.address,
          phone: payload.commerce.phone,
          email: payload.commerce.email,
          department_id: payload.commerce.department_id,
          city_id: payload.commerce.city_id,
          neighborhood_id: payload.commerce.neighborhood_id,
        });
      } else {
        const commerceResponse = await createCommerceBasic(payload);
        // Extraer el commerce de la respuesta CommerceBasicDataResponse
        const commerce = commerceResponse.data.commerce;
        commerceId = commerce.id;
        setExistingCommerceId(commerceId);
      }

      await uploadCommerceDocuments(commerceId);
      await refreshCommerce();

      toast.success(existingCommerceId ? 'Datos básicos actualizados correctamente' : 'Datos básicos guardados correctamente', {
        description: existingCommerceId
          ? 'Tus cambios fueron aplicados.'
          : 'Procederemos con la siguiente sección de tu registro',
      });

      // Fase 3: Encadenar flujo según aceptación de términos
      // Si terms_accepted_at es null, redirigir a pantalla legal para primera aceptación
      // Si ya fue aceptado, mantener comportamiento normal
      setTimeout(() => {
        if (!commerce?.terms_accepted_at) {
          router.push('/provider/legal');
        }
      }, 500);
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
      if (errorMessage === 'establishment_type_id_invalid') {
        toast.error('Tipo de establecimiento inválido', {
          description: 'Selecciona un tipo de establecimiento válido desde la lista.',
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

    setDocumentStatus((prev) => ({
      ...prev,
      [key]: { status: 'idle', error: null },
    }));

    const fieldPath = key === 'commerceChamber'
      ? 'documents.commerceChamber'
      : 'legalRepresentative.documentFile';
    clearFieldErrors(fieldPath);
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
    existingCommerceId,
    errors,
    formData,
    handleCancel,
    handleDocumentFileSelected,
    handleFieldChange,
    handleSubmit,
    isInitializing: isLoadingCommerce,
    isLoading,
  };
};

const mapCommerceToBasicInfoForm = (commerce: CommerceFromAPI): BasicInfoFormData => {
  const primaryLegalRepresentative =
    commerce.legal_representatives?.find((representative) => representative.is_primary) ||
    commerce.legal_representatives?.[0];
  const commerceChamberDocument = commerce.documents?.find(
    (document) => document.document_type === 'CAMARA_COMERCIO'
  );
  const legalRepresentativeDocument = commerce.documents?.find(
    (document) => document.document_type === 'ID_CARD'
  );
  
  return {
    commercialName: commerce.name || '',
    documentType: mapBackendDocumentTypeToFrontend(commerce.tax_id_type),
    documentNumber: commerce.tax_id || '',
    establishmentType: commerce.establishment_type_id
      ? String(commerce.establishment_type_id)
      : '',
    phone: commerce.phone || '',
    email: commerce.email || '',
    departmentId: commerce.department?.id ?? null,
    cityId: commerce.city?.id ?? null,
    neighborhood: commerce.neighborhood?.id ? String(commerce.neighborhood.id) : '',
    mainAddress: commerce.address || '',
    legalRepresentative: {
      firstName: primaryLegalRepresentative?.name || '',
      lastName: primaryLegalRepresentative?.last_name || '',
      documentType: mapBackendDocumentTypeToFrontend(primaryLegalRepresentative?.document_type),
      documentNumber: primaryLegalRepresentative?.document || '',
      documentFile: legalRepresentativeDocument?.file_path || null,
    },
    documents: {
      identity: legalRepresentativeDocument?.file_path || null,
      commerceChamber: commerceChamberDocument?.file_path || null,
    },
    observations: commerce.description || '',
  };
};

const mapBackendDocumentTypeToFrontend = (documentType?: string): BasicInfoFormData['documentType'] => {
  const normalizedDocumentType = (documentType || '')
    .trim()
    .toUpperCase()
    .replace(/[.\s_-]/g, '');

  switch (normalizedDocumentType) {
    case 'NIT':
      return 'nit';
    case 'CEDULACIUDADANIA':
    case 'CEDULADECIUDADANIA':
    case 'CC':
      return 'cc';
    case 'CEDULAEXTRANJERIA':
    case 'CEDULADEEXTRANJERIA':
    case 'CE':
      return 'ce';
    case 'PASSPORT':
    case 'PS':
    case 'PAS':
    case 'PASAPORTE':
      return 'passport';
    default:
      return '';
  }
};

