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
import type { BasicInfoFormData, FormErrors } from '@/types/basic-info';
import { AlertCircle, Info } from 'lucide-react';

type UploadStatus = 'idle' | 'uploading' | 'success' | 'error';

interface RutCardProps {
  formData: BasicInfoFormData;
  onFieldChange: (field: string, value: string | number | boolean | null) => void;
  onRutFileSelected?: (file: File | null) => void;
  onForm1876FileSelected?: (file: File | null) => void;
  rutUploadStatus?: UploadStatus;
  rutUploadError?: string | null;
  form1876UploadStatus?: UploadStatus;
  form1876UploadError?: string | null;
  errors?: Partial<FormErrors>;
}

/**
 * Card: RUT + Formato 1876
 * RUT es obligatorio siempre. El formato 1876 solo aplica si el comercio
 * está obligado a emitir factura electrónica (autodeclarado).
 *
 * Path: documents.rut, documents.form1876, electronicInvoicingRequired
 */
export function RutCard({
  formData,
  onFieldChange,
  onRutFileSelected,
  onForm1876FileSelected,
  rutUploadStatus = 'idle',
  rutUploadError = null,
  form1876UploadStatus = 'idle',
  form1876UploadError = null,
  errors = {},
}: RutCardProps) {
  const { documents, electronicInvoicingRequired } = formData;
  const isLegalPerson = formData.documentType === 'nit';
  const rutLabel = isLegalPerson ? 'RUT de la sociedad' : 'RUT del titular';

  return (
    <Card className="mb-6">
      <CardHeader>
        <CardTitle>RUT</CardTitle>
        <CardDescription>
          Adjunta el Registro Único Tributario del establecimiento
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-6">
        <div className="flex flex-col space-y-2">
          <Label className="text-sm font-medium text-[#1A1A1A]">
            {rutLabel} <span className="text-red-500">*</span>
          </Label>
          <FileUploadBox
            label="Carga el RUT"
            fileName={documents.rut}
            onUpload={async () => {
              const { openFileDialog, validateFile, generateFileName } = await import('@/lib/utils/file-upload');
              const file = await openFileDialog();
              if (file) {
                const validation = validateFile(file);
                if (validation.isValid) {
                  const generatedName = generateFileName(file.name);
                  onFieldChange('documents.rut', generatedName);
                  onRutFileSelected?.(file);
                }
              }
            }}
            onRemove={() => {
              onFieldChange('documents.rut', null);
              onRutFileSelected?.(null);
            }}
            disabled={rutUploadStatus === 'uploading'}
          />
          {rutUploadStatus === 'uploading' && (
            <p className="text-xs text-[#6A6A6A]">Subiendo documento...</p>
          )}
          {rutUploadStatus === 'success' && (
            <p className="text-xs text-green-600">Documento cargado correctamente.</p>
          )}
          {rutUploadStatus === 'error' && rutUploadError && (
            <div className="flex items-center gap-2 text-xs text-red-600">
              <AlertCircle size={14} />
              <span>{rutUploadError}</span>
            </div>
          )}
          {errors.rut && (
            <div className="flex items-center gap-2 text-xs text-red-600">
              <AlertCircle size={14} />
              <span>{errors.rut}</span>
            </div>
          )}
        </div>

        <fieldset className="flex flex-col space-y-2">
          <legend className="text-sm font-medium text-[#1A1A1A] mb-1">
            ¿Está obligado a emitir factura electrónica? <span className="text-red-500">*</span>
          </legend>
          <div className="flex gap-6">
            <label className="flex items-center gap-2 text-sm text-[#1A1A1A] cursor-pointer">
              <input
                type="radio"
                name="electronicInvoicingRequired"
                checked={electronicInvoicingRequired === true}
                onChange={() => onFieldChange('electronicInvoicingRequired', true)}
                className="h-4 w-4 accent-[#4B236A]"
              />
              Sí
            </label>
            <label className="flex items-center gap-2 text-sm text-[#1A1A1A] cursor-pointer">
              <input
                type="radio"
                name="electronicInvoicingRequired"
                checked={electronicInvoicingRequired === false}
                onChange={() => onFieldChange('electronicInvoicingRequired', false)}
                className="h-4 w-4 accent-[#4B236A]"
              />
              No
            </label>
          </div>
          {errors.electronicInvoicingRequired && (
            <div className="flex items-center gap-2 text-xs text-red-600">
              <AlertCircle size={14} />
              <span>{errors.electronicInvoicingRequired}</span>
            </div>
          )}
        </fieldset>

        {electronicInvoicingRequired === true && (
          <div className="flex flex-col space-y-2">
            <Label className="text-sm font-medium text-[#1A1A1A]">
              Formato 1876 <span className="text-red-500">*</span>
            </Label>
            <FileUploadBox
              label="Carga el formato 1876"
              fileName={documents.form1876}
              onUpload={async () => {
                const { openFileDialog, validateFile, generateFileName } = await import('@/lib/utils/file-upload');
                const file = await openFileDialog();
                if (file) {
                  const validation = validateFile(file);
                  if (validation.isValid) {
                    const generatedName = generateFileName(file.name);
                    onFieldChange('documents.form1876', generatedName);
                    onForm1876FileSelected?.(file);
                  }
                }
              }}
              onRemove={() => {
                onFieldChange('documents.form1876', null);
                onForm1876FileSelected?.(null);
              }}
              disabled={form1876UploadStatus === 'uploading'}
            />
            {form1876UploadStatus === 'uploading' && (
              <p className="text-xs text-[#6A6A6A]">Subiendo documento...</p>
            )}
            {form1876UploadStatus === 'success' && (
              <p className="text-xs text-green-600">Documento cargado correctamente.</p>
            )}
            {form1876UploadStatus === 'error' && form1876UploadError && (
              <div className="flex items-center gap-2 text-xs text-red-600">
                <AlertCircle size={14} />
                <span>{form1876UploadError}</span>
              </div>
            )}
            {errors.form1876 && (
              <div className="flex items-center gap-2 text-xs text-red-600">
                <AlertCircle size={14} />
                <span>{errors.form1876}</span>
              </div>
            )}
          </div>
        )}

        {electronicInvoicingRequired === false && (
          <div className="flex items-start gap-2 rounded-[14px] bg-[#F3F0F7] p-3 text-xs text-[#4B236A]">
            <Info size={14} className="mt-0.5 flex-shrink-0" />
            <span>
              Podrás operar en la plataforma sin este requisito, aunque esta información
              podrá ser solicitada posteriormente si cambia tu situación tributaria.
            </span>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
