'use client';

import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
  Label,
  FileUploadBox,
} from '@/components/provider/ui';
import { useFileUpload } from '@/hooks';
import type { BasicInfoFormData, FormErrors } from '@/types/basic-info';
import { AlertCircle } from 'lucide-react';

interface CamaraComercioCardProps {
  formData: BasicInfoFormData;
  onFieldChange: (field: string, value: string | number | null) => void;
  errors?: Partial<FormErrors>;
}

/**
 * Card: Cámara de Comercio
 * Single FileUploadBox for commerce chamber document (required)
 *
 * Path: documents.commerceChamber
 */
export function CamaraComercioCard({
  formData,
  onFieldChange,
  errors = {},
}: CamaraComercioCardProps) {
  const { documents } = formData;
  const { isUploading: isUploadingCamara } = useFileUpload(
    (fileName) => onFieldChange('documents.commerceChamber', fileName)
  );

  return (
    <Card className="mb-6">
      <CardHeader>
        <CardTitle>Cámara de Comercio</CardTitle>
        <CardDescription>
          Adjunta el documento de Cámara de Comercio del establecimiento
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-4">
        <div className="flex flex-col space-y-2">
          <Label className="text-sm font-medium text-[#1A1A1A]">
            Documento de Cámara de Comercio <span className="text-red-500">*</span>
          </Label>
          <FileUploadBox
            label="Carga el documento de Cámara de Comercio"
            fileName={documents.commerceChamber}
            onUpload={async () => {
              const { openFileDialog, validateFile, generateFileName } = await import('@/lib/utils/file-upload');
              const file = await openFileDialog();
              if (file) {
                const validation = validateFile(file);
                if (validation.isValid) {
                  const generatedName = generateFileName(file.name);
                  onFieldChange('documents.commerceChamber', generatedName);
                }
              }
            }}
            onRemove={() => onFieldChange('documents.commerceChamber', null)}
            disabled={isUploadingCamara}
          />
          {errors.commerceChamber && (
            <div className="flex items-center gap-2 text-xs text-red-600">
              <AlertCircle size={14} />
              <span>{errors.commerceChamber}</span>
            </div>
          )}
        </div>
      </CardContent>
    </Card>
  );
}
