'use client';

import { EstablecimientoCard } from './establecimiento-card';
import { RepresentanteLegalCard } from './representante-legal-card';
import { CamaraComercioCard } from './camara-comercio-card';
import { ObservacionesCard } from './observaciones-card';
import { useBasicInfoForm } from '@/hooks/provider/use-basic-info-form';

export function BasicInfoForm() {
  const {
    documentStatus,
    errors,
    formData,
    handleCancel,
    handleDocumentFileSelected,
    handleFieldChange,
    handleSubmit,
    isLoading,
  } = useBasicInfoForm();

  return (
    <form onSubmit={handleSubmit}>
      <EstablecimientoCard
        formData={formData}
        onFieldChange={handleFieldChange}
        errors={errors}
      />

      <RepresentanteLegalCard
        formData={formData}
        onFieldChange={handleFieldChange}
        onFileSelected={(file) => handleDocumentFileSelected('legalRepresentativeId', file)}
        uploadStatus={documentStatus.legalRepresentativeId.status}
        uploadError={documentStatus.legalRepresentativeId.error}
        errors={errors}
      />

      <CamaraComercioCard
        formData={formData}
        onFieldChange={handleFieldChange}
        onFileSelected={(file) => handleDocumentFileSelected('commerceChamber', file)}
        uploadStatus={documentStatus.commerceChamber.status}
        uploadError={documentStatus.commerceChamber.error}
        errors={errors}
      />

      <ObservacionesCard
        formData={formData}
        onFieldChange={handleFieldChange}
      />

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
  );
}
