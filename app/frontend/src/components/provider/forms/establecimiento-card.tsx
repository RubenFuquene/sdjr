'use client';

import { useEffect, useMemo, useRef } from 'react';
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
  DepartmentSelect,
  CitySelect,
  NeighborhoodSelect,
} from '@/components/provider/ui';
import { AlertCircle } from 'lucide-react';
import type { BasicInfoFormData, FormErrors } from '@/types/basic-info';
import { DOCUMENT_TYPE_OPTIONS, FRANCHISE_ELIGIBLE_ESTABLISHMENT_CODES } from '@/types/basic-info';
import { useLocation, useEstablishmentTypes } from '@/hooks/index';

interface EstablecimientoCardProps {
  formData: BasicInfoFormData;
  onFieldChange: (field: string, value: string | number | boolean | null) => void;
  errors?: Partial<FormErrors>;
}

/**
 * Card: Información del Establecimiento
 * 10 campos en grid 2 columnas (dirección full width)
 */
export function EstablecimientoCard({
  formData,
  onFieldChange,
  errors = {},
}: EstablecimientoCardProps) {
  const {
    departments,
    cities,
    neighborhoods,
    loading,
    selectedDept,
    selectedCity,
    setSelectedDept,
    setSelectedCity,
    setSelectedNeighborhood,
  } = useLocation();

  const {
    types: establishmentTypes,
    loading: loadingEstablishmentTypes,
    error: establishmentTypesError,
  } = useEstablishmentTypes();

  // SCRUM-365: la franquicia solo se pregunta para tipos que prestan
  // servicio de expendio de comidas (Art. 426 ET) — mismo criterio que
  // CR-01 del backend, aplicado aquí para decidir si se muestra el campo.
  const selectedEstablishmentType = establishmentTypes.find(
    (type) => String(type.id) === formData.establishmentType
  );
  const isFranchiseEligible = Boolean(
    selectedEstablishmentType &&
      FRANCHISE_ELIGIBLE_ESTABLISHMENT_CODES.includes(selectedEstablishmentType.code)
  );

  const filteredCities = useMemo(() => {
    if (!selectedDept) return [];
    return cities.filter((city) => city.department_id === selectedDept);
  }, [cities, selectedDept]);

  const filteredNeighborhoods = useMemo(() => {
    if (!selectedCity) return [];
    return neighborhoods.filter((neighborhood) => neighborhood.city_id === selectedCity);
  }, [neighborhoods, selectedCity]);

  const hasInitializedLocationRef = useRef(false);

  useEffect(() => {
    if (hasInitializedLocationRef.current) {
      return;
    }

    const hasInitialLocationData =
      formData.departmentId !== null || formData.cityId !== null || formData.neighborhood !== '';
    if (!hasInitialLocationData) {
      return;
    }

    const parsedNeighborhood = formData.neighborhood ? Number(formData.neighborhood) : null;
    const normalizedNeighborhood =
      parsedNeighborhood !== null && Number.isNaN(parsedNeighborhood) ? null : parsedNeighborhood;

    setSelectedDept(formData.departmentId);
    setSelectedCity(formData.cityId);
    setSelectedNeighborhood(normalizedNeighborhood);
    hasInitializedLocationRef.current = true;
  }, [
    formData.departmentId,
    formData.cityId,
    formData.neighborhood,
    setSelectedDept,
    setSelectedCity,
    setSelectedNeighborhood,
  ]);

  const handleDepartmentChange = (departmentId: number | null) => {
    if (departmentId === null) {
      return;
    }

    setSelectedDept(departmentId);
    onFieldChange('departmentId', departmentId);
    onFieldChange('cityId', null);
    onFieldChange('neighborhood', '');
  };

  const handleCityChange = (cityId: number | null) => {
    if (cityId === null) {
      return;
    }

    setSelectedCity(cityId);
    onFieldChange('cityId', cityId);
    onFieldChange('neighborhood', '');
  };

  const handleNeighborhoodChange = (value: string) => {
    const neighborhoodId = value ? Number(value) : null;
    setSelectedNeighborhood(neighborhoodId);
    onFieldChange('neighborhood', value);
  };

  return (
    <Card className="mb-6">
      <CardHeader>
        <CardTitle>Información del Establecimiento</CardTitle>
        <CardDescription>
          Proporciona los datos principales de tu negocio
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {/* ============================================ */}
          {/* 1. Nombre Comercial */}
          {/* ============================================ */}
          <div className="space-y-2">
            <Label htmlFor="commercial-name" className="text-sm font-medium text-[#1A1A1A]">
              Nombre Comercial <span className="text-red-500">*</span>
            </Label>
            <Input
              id="commercial-name"
              type="text"
              value={formData.commercialName}
              onChange={(e) => onFieldChange('commercialName', e.target.value)}
              placeholder="Ej: Restaurante El Buen Sabor"
              className={`h-[50px] rounded-[14px] border-[#E0E0E0] ${
                errors.commercialName ? 'border-red-500' : ''
              }`}
            />
            {errors.commercialName && (
              <p className="text-sm text-red-600">{errors.commercialName}</p>
            )}
          </div>

          {/* ============================================ */}
          {/* 2. Tipo de Documento */}
          {/* ============================================ */}
          <div className="space-y-2">
            <Label htmlFor="document-type" className="text-sm font-medium text-[#1A1A1A]">
              Tipo de Documento <span className="text-red-500">*</span>
            </Label>
            <Select
              key={formData.documentType !== '' ? 'doc-type-hydrated' : 'doc-type-empty'}
              value={formData.documentType}
              onValueChange={(value) => onFieldChange('documentType', value)}
            >
              <SelectTrigger
                id="document-type"
                className={`h-[50px] rounded-[14px] border-[#E0E0E0] ${
                  errors.documentType ? 'border-red-500' : ''
                }`}
              >
                <SelectValue placeholder="Selecciona tipo" />
              </SelectTrigger>
              <SelectContent>
                {DOCUMENT_TYPE_OPTIONS.map((option) => (
                  <SelectItem key={option.value} value={option.value}>
                    {option.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
            {errors.documentType && (
              <p className="text-sm text-red-600">{errors.documentType}</p>
            )}
          </div>

          {/* ============================================ */}
          {/* 3. Número de Documento */}
          {/* ============================================ */}
          <div className="space-y-2">
            <Label htmlFor="document-number" className="text-sm font-medium text-[#1A1A1A]">
              Número de Documento <span className="text-red-500">*</span>
            </Label>
            <Input
              id="document-number"
              type="tel"
              inputMode="numeric"
              value={formData.documentNumber}
              onChange={(e) => {
                // Allow only numbers and hyphens
                const cleaned = e.target.value.replace(/[^0-9-]/g, '');
                onFieldChange('documentNumber', cleaned);
              }}
              placeholder="Ej: 900123456-7"
              className={`h-[50px] rounded-[14px] border-[#E0E0E0] ${
                errors.documentNumber ? 'border-red-500' : ''
              }`}
            />
            {errors.documentNumber && (
              <p className="text-sm text-red-600">{errors.documentNumber}</p>
            )}
          </div>

          {/* ============================================ */}
          {/* 4. Tipo de Establecimiento */}
          {/* ============================================ */}
          <div className="space-y-2">
            <Label htmlFor="establishment-type" className="text-sm font-medium text-[#1A1A1A]">
              Tipo de Establecimiento <span className="text-red-500">*</span>
            </Label>
            <Select
              key={formData.establishmentType !== '' ? 'est-type-hydrated' : 'est-type-empty'}
              value={formData.establishmentType}
              onValueChange={(value) => onFieldChange('establishmentType', value)}
              disabled={loadingEstablishmentTypes}
            >
              <SelectTrigger
                id="establishment-type"
                className={`h-[50px] rounded-[14px] border-[#E0E0E0] ${
                  errors.establishmentType ? 'border-red-500' : ''
                }`}
              >
                <SelectValue placeholder={loadingEstablishmentTypes ? 'Cargando tipos...' : 'Selecciona tipo'} />
              </SelectTrigger>
              <SelectContent>
                {Array.isArray(establishmentTypes) && establishmentTypes.map((type) => (
                  <SelectItem key={type.id} value={String(type.id)}>
                    {type.name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
            {errors.establishmentType && (
              <p className="text-sm text-red-600">{errors.establishmentType}</p>
            )}
            {!errors.establishmentType && establishmentTypesError && (
              <p className="text-sm text-red-600">No fue posible cargar los tipos de establecimiento.</p>
            )}
          </div>

          {/* ============================================ */}
          {/* 4b. Operación bajo franquicia (SCRUM-365) — solo RE/PA */}
          {/* ============================================ */}
          {isFranchiseEligible && (
            <div className="md:col-span-2">
              <fieldset
                className="flex flex-col space-y-2"
                aria-describedby="franchise-status"
              >
                <legend className="text-sm font-medium text-[#1A1A1A] mb-1">
                  ¿Tu negocio opera bajo franquicia, concesión o regalía?{' '}
                  <span className="text-red-500" aria-hidden="true">*</span>
                  <span className="sr-only"> (obligatorio)</span>
                </legend>
                <p className="text-sm text-[#6A6A6A]">
                  Marca si tu negocio funciona bajo un contrato de franquicia, concesión, regalía
                  o cualquier forma de explotación de marca de un tercero (por ejemplo: Subway,
                  Sandwich Qbano, Juan Valdez). Esto determina el régimen tributario que aplica a
                  tus productos.
                </p>
                <div className="flex gap-6" role="radiogroup" aria-required="true">
                  <label className="flex items-center gap-2 text-sm text-[#1A1A1A] cursor-pointer">
                    <input
                      type="radio"
                      name="operatesUnderFranchise"
                      checked={formData.operatesUnderFranchise === true}
                      onChange={() => onFieldChange('operatesUnderFranchise', true)}
                      className="h-4 w-4 accent-[#4B236A]"
                    />
                    Sí, opero bajo franquicia o contrato similar
                  </label>
                  <label className="flex items-center gap-2 text-sm text-[#1A1A1A] cursor-pointer">
                    <input
                      type="radio"
                      name="operatesUnderFranchise"
                      checked={formData.operatesUnderFranchise === false}
                      onChange={() => onFieldChange('operatesUnderFranchise', false)}
                      className="h-4 w-4 accent-[#4B236A]"
                    />
                    No, mi negocio es independiente
                  </label>
                </div>
                <div id="franchise-status" aria-live="polite">
                  {errors.operatesUnderFranchise && (
                    <div className="flex items-center gap-2 text-xs text-red-600">
                      <AlertCircle size={14} />
                      <span>{errors.operatesUnderFranchise}</span>
                    </div>
                  )}
                </div>
              </fieldset>
            </div>
          )}

          {/* ============================================ */}
          {/* 5. Teléfono */}
          {/* ============================================ */}
          <div className="space-y-2">
            <Label htmlFor="phone" className="text-sm font-medium text-[#1A1A1A]">
              Teléfono <span className="text-red-500">*</span>
            </Label>
            <Input
              id="phone"
              type="tel"
              inputMode="numeric"
              value={formData.phone}
              onChange={(e) => {
                // Allow only numbers, max 10 digits
                const cleaned = e.target.value.replace(/\D/g, '').slice(0, 10);
                onFieldChange('phone', cleaned);
              }}
              placeholder="Ej: 3001234567"
              maxLength={10}
              className={`h-[50px] rounded-[14px] border-[#E0E0E0] ${
                errors.phone ? 'border-red-500' : ''
              }`}
            />
            {errors.phone && <p className="text-sm text-red-600">{errors.phone}</p>}
          </div>

          {/* ============================================ */}
          {/* 6. Correo de contacto del establecimiento */}
          {/* ============================================ */}
          <div className="space-y-2">
            <Label htmlFor="email" className="text-sm font-medium text-[#1A1A1A]">
              Correo de contacto del establecimiento <span className="text-red-500">*</span>
            </Label>
            <Input
              id="email"
              type="email"
              value={formData.email}
              onChange={(e) => onFieldChange('email', e.target.value)}
              placeholder="contacto@negocio.com"
              aria-describedby="email-description"
              aria-invalid={Boolean(errors.email)}
              className={`h-[50px] rounded-[14px] border-[#E0E0E0] ${
                errors.email ? 'border-red-500' : ''
              }`}
            />
            {/* SCRUM-369: contenedor único para ayuda/error del campo — este correo
                es el del comercio (commerce.email), no el de la sesión del proveedor
                (users.email), y ambos pueden diferir. */}
            <p
              id="email-description"
              className={`text-sm ${errors.email ? 'text-red-600' : 'text-[#4B236A]/60'}`}
            >
              {errors.email ||
                'Este es el correo público de tu negocio. Puede ser distinto del correo con el que inicias sesión.'}
            </p>
          </div>

          {/* ============================================ */}
          {/* 7. Departamento */}
          {/* ============================================ */}
          <DepartmentSelect
            departments={departments}
            value={formData.departmentId}
            onChange={handleDepartmentChange}
            loading={loading.departments}
            required
            error={errors.departmentId}
          />

          {/* ============================================ */}
          {/* 8. Ciudad */}
          {/* ============================================ */}
          <CitySelect
            cities={filteredCities}
            departmentId={selectedDept}
            value={formData.cityId}
            onChange={handleCityChange}
            loading={loading.cities}
            required
            error={errors.cityId}
          />

          {/* ============================================ */}
          {/* 9. Barrio (Condicional: Select o Input) */}
          {/* ============================================ */}
          <NeighborhoodSelect
            neighborhoods={filteredNeighborhoods}
            cityId={selectedCity}
            value={formData.neighborhood}
            onChange={handleNeighborhoodChange}
            loading={loading.neighborhoods}
            required
            error={errors.neighborhood}
          />

          {/* ============================================ */}
          {/* 10. Dirección Principal (Full Width) */}
          {/* ============================================ */}
          <div className="md:col-span-2 space-y-2">
            <Label htmlFor="main-address" className="text-sm font-medium text-[#1A1A1A]">
              Dirección Principal <span className="text-red-500">*</span>
            </Label>
            <Input
              id="main-address"
              type="text"
              value={formData.mainAddress}
              onChange={(e) => onFieldChange('mainAddress', e.target.value)}
              placeholder="Ej: Calle 45 #23-10"
              className={`h-[50px] rounded-[14px] border-[#E0E0E0] ${
                errors.mainAddress ? 'border-red-500' : ''
              }`}
            />
            {errors.mainAddress && (
              <p className="text-sm text-red-600">{errors.mainAddress}</p>
            )}
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
