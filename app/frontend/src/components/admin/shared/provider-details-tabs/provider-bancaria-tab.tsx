/**
 * Tab: Información Bancaria del Proveedor
 * 
 * Responsabilidades:
 * - Formulario de información bancaria (titular, tipo cuenta, banco, número)
 * - Validación de campos bancarios
 */

'use client';

import type { Proveedor, InformacionBancaria } from '@/types/admin';

// ============================================
// Constants
// ============================================

/**
 * Tipos de cuenta bancaria disponibles
 */
const TIPOS_CUENTA = [
  { value: 'ahorros', label: 'Ahorros' },
  { value: 'corriente', label: 'Corriente' },
  { value: 'fiduciaria', label: 'Fiduciaria' },
] as const;

/**
 * Bancos colombianos principales
 */
const BANCOS_COLOMBIA = [
  'Banco de Bogotá',
  'Bancolombia',
  'Banco de Occidente',
  'Banco Popular',
  'BBVA Colombia',
  'Davivienda',
  'Banco AV Villas',
  'Banco Caja Social',
  'Banco Agrario',
  'Banco GNB Sudameris',
  'Banco Falabella',
  'Banco Pichincha',
  'Banco Cooperativo Coopcentral',
  'Scotiabank Colpatria',
  'Itaú',
  'Banco Finandina',
  'Nequi',
  'Daviplata',
  'Banco Mundo Mujer',
  'Confiar',
  'Otro',
];

// ============================================
// Props Interface
// ============================================

interface ProviderBancariaTabProps {
  formData: Proveedor;
  isViewMode: boolean;
  errors: Record<string, string>;
  onFieldChange: (field: keyof Proveedor, value: unknown) => void;
}

// ============================================
// Component
// ============================================

export function ProviderBancariaTab({
  formData,
  isViewMode,
  errors,
  onFieldChange,
}: ProviderBancariaTabProps) {
  const infoBancaria = formData.informacionBancaria || {
    titular: '',
    tipoCuenta: 'ahorros' as const,
    banco: '',
    numeroCuenta: '',
  };

  /**
   * Actualiza un campo de información bancaria
   */
  const handleBancariaFieldChange = (field: keyof InformacionBancaria, value: string) => {
    const updatedInfo: InformacionBancaria = {
      ...infoBancaria,
      [field]: value,
    };
    onFieldChange('informacionBancaria', updatedInfo);
  };

  return (
    <div className="space-y-6">
      {/* Descripción */}
      <div className="p-4 bg-[#DDE8BB]/30 border border-[#C8D86D] rounded-xl">
        <p className="text-sm text-[#1A1A1A]">
          📌 <strong>Información de pago:</strong> Los datos bancarios son utilizados para realizar los pagos correspondientes al proveedor.
        </p>
      </div>

      {/* Grid 2 columnas */}
      <div className="grid grid-cols-2 gap-4">
        {/* Titular de la Cuenta */}
        <div className="col-span-2">
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            Titular de la Cuenta <span className="text-red-500">*</span>
          </label>
          <input
            type="text"
            value={infoBancaria.titular}
            onChange={(e) => handleBancariaFieldChange('titular', e.target.value)}
            disabled={isViewMode}
            className={`w-full h-[50px] px-4 border rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A] transition-colors ${
              errors['informacionBancaria.titular'] ? 'border-red-500' : 'border-[#E0E0E0]'
            }`}
            placeholder="Ej: Juan Pérez González o Empresa SAS"
          />
          {errors['informacionBancaria.titular'] && (
            <p className="mt-1 text-sm text-red-500">{errors['informacionBancaria.titular']}</p>
          )}
          <p className="mt-1 text-xs text-[#6A6A6A]">
            Nombre completo del titular según documento de identidad o razón social de la empresa
          </p>
        </div>

        {/* Tipo de Cuenta */}
        <div>
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            Tipo de Cuenta <span className="text-red-500">*</span>
          </label>
          <select
            value={infoBancaria.tipoCuenta}
            onChange={(e) => handleBancariaFieldChange('tipoCuenta', e.target.value)}
            disabled={isViewMode}
            className={`w-full h-[50px] px-4 border rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A] transition-colors ${
              errors['informacionBancaria.tipoCuenta'] ? 'border-red-500' : 'border-[#E0E0E0]'
            }`}
          >
            {TIPOS_CUENTA.map((tipo) => (
              <option key={tipo.value} value={tipo.value}>
                {tipo.label}
              </option>
            ))}
          </select>
          {errors['informacionBancaria.tipoCuenta'] && (
            <p className="mt-1 text-sm text-red-500">{errors['informacionBancaria.tipoCuenta']}</p>
          )}
        </div>

        {/* Banco */}
        <div>
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            Banco <span className="text-red-500">*</span>
          </label>
          <select
            value={infoBancaria.banco}
            onChange={(e) => handleBancariaFieldChange('banco', e.target.value)}
            disabled={isViewMode}
            className={`w-full h-[50px] px-4 border rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A] transition-colors ${
              errors['informacionBancaria.banco'] ? 'border-red-500' : 'border-[#E0E0E0]'
            }`}
          >
            <option value="">Seleccionar banco...</option>
            {BANCOS_COLOMBIA.map((banco) => (
              <option key={banco} value={banco}>
                {banco}
              </option>
            ))}
          </select>
          {errors['informacionBancaria.banco'] && (
            <p className="mt-1 text-sm text-red-500">{errors['informacionBancaria.banco']}</p>
          )}
        </div>

        {/* Número de Cuenta */}
        <div className="col-span-2">
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            Número de Cuenta <span className="text-red-500">*</span>
          </label>
          <input
            type="text"
            value={infoBancaria.numeroCuenta}
            onChange={(e) => handleBancariaFieldChange('numeroCuenta', e.target.value)}
            disabled={isViewMode}
            className={`w-full h-[50px] px-4 border rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A] transition-colors ${
              errors['informacionBancaria.numeroCuenta'] ? 'border-red-500' : 'border-[#E0E0E0]'
            }`}
            placeholder="Ej: 1234567890"
            maxLength={20}
          />
          {errors['informacionBancaria.numeroCuenta'] && (
            <p className="mt-1 text-sm text-red-500">{errors['informacionBancaria.numeroCuenta']}</p>
          )}
          <p className="mt-1 text-xs text-[#6A6A6A]">
            Número completo de la cuenta bancaria (sin guiones ni espacios)
          </p>
        </div>
      </div>

      {/* Nota de seguridad */}
      <div className="p-4 bg-[#F7F7F7] border border-[#E0E0E0] rounded-xl">
        <p className="text-xs text-[#6A6A6A]">
          🔒 <strong>Seguridad:</strong> Esta información es confidencial y solo será utilizada para procesar pagos.
          Verifica que los datos sean correctos antes de guardar.
        </p>
      </div>
    </div>
  );
}
