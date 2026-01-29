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
  DepartmentSelect,
  CitySelect,
  NeighborhoodSelect,
} from '@/components/provider/ui';
import type { BasicInfoFormData, FormErrors } from '@/types/basic-info';
import { DOCUMENT_TYPE_OPTIONS, ESTABLISHMENT_TYPE_OPTIONS } from '@/types/basic-info';

interface EstablecimientoCardProps {
  formData: BasicInfoFormData;
  onFieldChange: (field: string, value: string | number | null) => void;
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
              value={formData.establishmentType}
              onValueChange={(value) => onFieldChange('establishmentType', value)}
            >
              <SelectTrigger
                id="establishment-type"
                className={`h-[50px] rounded-[14px] border-[#E0E0E0] ${
                  errors.establishmentType ? 'border-red-500' : ''
                }`}
              >
                <SelectValue placeholder="Selecciona tipo" />
              </SelectTrigger>
              <SelectContent>
                {ESTABLISHMENT_TYPE_OPTIONS.map((option) => (
                  <SelectItem key={option.value} value={option.value}>
                    {option.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
            {errors.establishmentType && (
              <p className="text-sm text-red-600">{errors.establishmentType}</p>
            )}
          </div>

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
          {/* 6. Correo Electrónico */}
          {/* ============================================ */}
          <div className="space-y-2">
            <Label htmlFor="email" className="text-sm font-medium text-[#1A1A1A]">
              Correo Electrónico <span className="text-red-500">*</span>
            </Label>
            <Input
              id="email"
              type="email"
              value={formData.email}
              onChange={(e) => onFieldChange('email', e.target.value)}
              placeholder="contacto@negocio.com"
              className={`h-[50px] rounded-[14px] border-[#E0E0E0] ${
                errors.email ? 'border-red-500' : ''
              }`}
            />
            {errors.email && <p className="text-sm text-red-600">{errors.email}</p>}
          </div>

          {/* ============================================ */}
          {/* 7. Departamento */}
          {/* ============================================ */}
          <DepartmentSelect
            value={formData.departmentId}
            onChange={(id) => onFieldChange('departmentId', id)}
            required
            error={errors.departmentId}
          />

          {/* ============================================ */}
          {/* 8. Ciudad */}
          {/* ============================================ */}
          <CitySelect
            departmentId={formData.departmentId}
            value={formData.cityId}
            onChange={(id) => onFieldChange('cityId', id)}
            required
            error={errors.cityId}
          />

          {/* ============================================ */}
          {/* 9. Barrio (Condicional: Select o Input) */}
          {/* ============================================ */}
          <NeighborhoodSelect
            cityId={formData.cityId}
            value={formData.neighborhood}
            onChange={(value) => onFieldChange('neighborhood', value)}
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
