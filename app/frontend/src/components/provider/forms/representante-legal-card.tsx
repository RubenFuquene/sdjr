'use client';

import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
  Input,
  Label,
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
  FileUploadBox,
} from '@/components/provider/ui';import { useFileUpload } from '@/hooks';import type { BasicInfoFormData, FormErrors } from '@/types/basic-info';
import { LEGAL_REP_DOCUMENT_TYPE_OPTIONS } from '@/types/basic-info';
import { AlertCircle } from 'lucide-react';

interface RepresentanteLegalCardProps {
  formData: BasicInfoFormData;
  onFieldChange: (field: string, value: string | number | null) => void;
  errors?: Partial<FormErrors>;
}

/**
 * Card: Información del Representante Legal
 * 4 campos de texto + 1 FileUploadBox en grid 2 columnas
 * - Nombres (text)
 * - Apellidos (text)
 * - Tipo de Documento (select)
 * - Número de Documento (tel input)
 * - Documento (FileUploadBox, full width)
 */
export function RepresentanteLegalCard({
  formData,
  onFieldChange,
  errors = {},
}: RepresentanteLegalCardProps) {
  const { legalRepresentative } = formData;
  const { isUploading: isUploadingDoc } = useFileUpload(
    (fileName) => handleRepChange('documentFile', fileName)
  );

  /**
   * Manejador de cambios en campos del representante legal
   * Usa la ruta anidada: legalRepresentative.fieldName
   */
  const handleRepChange = (field: string, value: string | number | null) => {
    onFieldChange(`legalRepresentative.${field}`, value);
  };

  /**
   * Formatea número de documento: solo números y guiones
   */
  const formatDocumentNumber = (value: string) => {
    return value.replace(/[^0-9\-]/g, '').slice(0, 20);
  };

  return (
    <Card className="mb-6">
      <CardHeader>
        <CardTitle>Representante Legal</CardTitle>
        <CardDescription>
          Información de la persona responsable del establecimiento
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {/* ============================================ */}
          {/* 1. Nombres */}
          {/* ============================================ */}
          <div className="flex flex-col space-y-2">
            <Label htmlFor="legalRep-firstName" className="text-sm font-medium text-[#1A1A1A]">
              Nombres <span className="text-red-500">*</span>
            </Label>
            <Input
              id="legalRep-firstName"
              placeholder="Ej: Juan Pedro"
              value={legalRepresentative.firstName}
              onChange={(e) => handleRepChange('firstName', e.target.value)}
              className={`h-[50px] rounded-[14px] border ${
                errors.legalRepresentativeFirstName
                  ? 'border-red-500 bg-red-50'
                  : 'border-[#E0E0E0] bg-white'
              }`}
            />
            {errors.legalRepresentativeFirstName && (
              <div className="flex items-center gap-2 text-xs text-red-600">
                <AlertCircle size={14} />
                <span>{errors.legalRepresentativeFirstName}</span>
              </div>
            )}
          </div>

          {/* ============================================ */}
          {/* 2. Apellidos */}
          {/* ============================================ */}
          <div className="flex flex-col space-y-2">
            <Label htmlFor="legalRep-lastName" className="text-sm font-medium text-[#1A1A1A]">
              Apellidos <span className="text-red-500">*</span>
            </Label>
            <Input
              id="legalRep-lastName"
              placeholder="Ej: García López"
              value={legalRepresentative.lastName}
              onChange={(e) => handleRepChange('lastName', e.target.value)}
              className={`h-[50px] rounded-[14px] border ${
                errors.legalRepresentativeLastName
                  ? 'border-red-500 bg-red-50'
                  : 'border-[#E0E0E0] bg-white'
              }`}
            />
            {errors.legalRepresentativeLastName && (
              <div className="flex items-center gap-2 text-xs text-red-600">
                <AlertCircle size={14} />
                <span>{errors.legalRepresentativeLastName}</span>
              </div>
            )}
          </div>

          {/* ============================================ */}
          {/* 3. Tipo de Documento */}
          {/* ============================================ */}
          <div className="flex flex-col space-y-2">
            <Label htmlFor="legalRep-docType" className="text-sm font-medium text-[#1A1A1A]">
              Tipo de Documento <span className="text-red-500">*</span>
            </Label>
            <Select
              value={legalRepresentative.documentType}
              onValueChange={(value) => handleRepChange('documentType', value)}
            >
              <SelectTrigger
                id="legalRep-docType"
                className={`h-[50px] rounded-[14px] border ${
                  errors.legalRepresentativeDocumentType
                    ? 'border-red-500 bg-red-50'
                    : 'border-[#E0E0E0] bg-white'
                }`}
              >
                <SelectValue placeholder="Selecciona un tipo de documento" />
              </SelectTrigger>
              <SelectContent>
                {LEGAL_REP_DOCUMENT_TYPE_OPTIONS.map((option) => (
                  <SelectItem key={option.value} value={option.value}>
                    {option.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
            {errors.legalRepresentativeDocumentType && (
              <div className="flex items-center gap-2 text-xs text-red-600">
                <AlertCircle size={14} />
                <span>{errors.legalRepresentativeDocumentType}</span>
              </div>
            )}
          </div>

          {/* ============================================ */}
          {/* 4. Número de Documento */}
          {/* ============================================ */}
          <div className="flex flex-col space-y-2">
            <Label htmlFor="legalRep-docNumber" className="text-sm font-medium text-[#1A1A1A]">
              Número de Documento <span className="text-red-500">*</span>
            </Label>
            <Input
              id="legalRep-docNumber"
              placeholder="Ej: 1234567890"
              type="tel"
              value={legalRepresentative.documentNumber}
              onChange={(e) =>
                handleRepChange('documentNumber', formatDocumentNumber(e.target.value))
              }
              className={`h-[50px] rounded-[14px] border ${
                errors.legalRepresentativeDocumentNumber
                  ? 'border-red-500 bg-red-50'
                  : 'border-[#E0E0E0] bg-white'
              }`}
            />
            {errors.legalRepresentativeDocumentNumber && (
              <div className="flex items-center gap-2 text-xs text-red-600">
                <AlertCircle size={14} />
                <span>{errors.legalRepresentativeDocumentNumber}</span>
              </div>
            )}
          </div>

          {/* ============================================ */}
          {/* 5. Documento (FileUploadBox - Full Width) */}
          {/* ============================================ */}
          <div className="md:col-span-2 flex flex-col space-y-2">
            <Label className="text-sm font-medium text-[#1A1A1A]">
              Documento (Cédula/Pasaporte) <span className="text-red-500">*</span>
            </Label>
            <FileUploadBox
              label="Carga el documento de identidad"
              fileName={legalRepresentative.documentFile}
              onUpload={async () => {
                const { openFileDialog, validateFile, generateFileName } = await import('@/lib/utils/file-upload');
                const file = await openFileDialog();
                if (file) {
                  const validation = validateFile(file);
                  if (validation.isValid) {
                    const generatedName = generateFileName(file.name);
                    handleRepChange('documentFile', generatedName);
                  }
                }
              }}
              onRemove={() => handleRepChange('documentFile', null)}
              disabled={isUploadingDoc}
            />
            {errors.legalRepresentativeDocumentFile && (
              <div className="flex items-center gap-2 text-xs text-red-600">
                <AlertCircle size={14} />
                <span>{errors.legalRepresentativeDocumentFile}</span>
              </div>
            )}
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
