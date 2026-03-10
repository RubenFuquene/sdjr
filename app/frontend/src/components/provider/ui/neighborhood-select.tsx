'use client';

import type { Neighborhood } from '@/types/location';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/provider/ui/select';
import { Input } from '@/components/provider/ui/input';
import { Label } from '@/components/provider/ui/label';
import { AlertCircle } from 'lucide-react';

interface NeighborhoodSelectProps {
  neighborhoods: Neighborhood[];
  cityId: number | null;
  value: string;
  onChange: (value: string) => void;
  disabled?: boolean;
  loading?: boolean;
  allowManualEntry?: boolean;
  label?: string;
  required?: boolean;
  error?: string | null;
}

/**
 * Neighborhood Select Component
 * Conditional rendering:
 * - If neighborhoods exist for the city: Show Select dropdown
 * - If no neighborhoods exist: Show Input field for manual entry
 *
 * Data loading and filtering should be handled by parent form.
 */
export function NeighborhoodSelect({
  neighborhoods,
  cityId,
  value,
  onChange,
  disabled = false,
  loading = false,
  allowManualEntry = true,
  label = 'Barrio',
  required = false,
  error,
}: NeighborhoodSelectProps) {
  const hasNeighborhoods = neighborhoods.length > 0;
  const isDisabled = disabled || !cityId || loading;
  const shouldUseManualInput = !hasNeighborhoods && allowManualEntry;

  return (
    <div className="space-y-2 w-full">
      <Label
        htmlFor={hasNeighborhoods ? 'neighborhood-select' : 'neighborhood-input'}
        className="text-sm font-medium text-[#1A1A1A]"
      >
        {label}
        {required && <span className="text-red-500 ml-1">*</span>}
      </Label>

      {hasNeighborhoods ? (
        // Show Select if neighborhoods exist
        <Select
          value={value}
          onValueChange={(val) => onChange(val || '')}
          disabled={isDisabled}
        >
          <SelectTrigger
            id="neighborhood-select"
            className={`h-[50px] rounded-[14px] border-[#E0E0E0] ${
              error ? 'border-red-500' : ''
            }`}
          >
            <SelectValue
              placeholder={
                !cityId
                  ? 'Selecciona una ciudad primero'
                  : 'Selecciona un barrio'
              }
            />
          </SelectTrigger>

          <SelectContent>
            {neighborhoods.map((nbh) => (
              <SelectItem key={nbh.id} value={nbh.id.toString()}>
                {nbh.name}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
      ) : shouldUseManualInput ? (
        // Show Input field if no neighborhoods available
        <div className="relative">
          <Input
            id="neighborhood-input"
            type="text"
            value={value}
            onChange={(e) => onChange(e.target.value)}
            disabled={isDisabled}
            placeholder={
              !cityId
                ? 'Selecciona una ciudad primero'
                : 'Ingresa el barrio manualmente'
            }
            className={`h-[50px] rounded-[14px] border-[#E0E0E0] ${
              error ? 'border-red-500' : ''
            }`}
          />
          {!cityId && (
            <p className="text-xs text-[#6A6A6A] mt-1 absolute -bottom-5">
              (No hay barrios disponibles para esta ciudad)
            </p>
          )}
        </div>
      ) : (
        <Input
          id="neighborhood-input"
          type="text"
          value=""
          onChange={() => undefined}
          disabled
          placeholder={!cityId ? 'Selecciona una ciudad primero' : 'No hay barrios disponibles'}
          className={`h-[50px] rounded-[14px] border-[#E0E0E0] ${
            error ? 'border-red-500' : ''
          }`}
        />
      )}

      {error && (
        <div className="flex items-center gap-2 text-sm text-red-600 mt-2">
          <AlertCircle className="w-4 h-4 flex-shrink-0" />
          <span>{error}</span>
        </div>
      )}

      {loading && cityId && (
        <p className="text-xs text-[#6A6A6A] mt-1">Cargando barrios...</p>
      )}
    </div>
  );
}
