'use client';

import type { City } from '@/types/location';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/provider/ui/select';
import { Label } from '@/components/provider/ui/label';
import { AlertCircle } from 'lucide-react';

interface CitySelectProps {
  cities: City[];
  departmentId?: number | null;
  value: number | null;
  onChange: (id: number | null) => void;
  disabled?: boolean;
  loading?: boolean;
  label?: string;
  required?: boolean;
  error?: string | null;
}

/**
 * City Select Component (presentational)
 * Filtering/fetching logic should be handled by parent form.
 */
export function CitySelect({
  cities,
  departmentId,
  value,
  onChange,
  disabled = false,
  loading = false,
  label = 'Ciudad',
  required = false,
  error,
}: CitySelectProps) {
  const isDisabled = disabled || !departmentId || loading;

  return (
    <div className="space-y-2 w-full">
      <Label htmlFor="city-select" className="text-sm font-medium text-[#1A1A1A]">
        {label}
        {required && <span className="text-red-500 ml-1">*</span>}
      </Label>

      <Select
        value={value?.toString()}
        onValueChange={(val) => onChange(val ? parseInt(val, 10) : null)}
        disabled={isDisabled}
      >
        <SelectTrigger
          id="city-select"
          className={`h-[50px] rounded-[14px] border-[#E0E0E0] ${
            error ? 'border-red-500' : ''
          }`}
        >
          <SelectValue
            placeholder={
              !departmentId
                ? 'Selecciona un departamento primero'
                : 'Selecciona una ciudad'
            }
          />
        </SelectTrigger>

        <SelectContent>
          {cities.map((city) => (
            <SelectItem key={city.id} value={city.id.toString()}>
              {city.name}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>

      {error && (
        <div className="flex items-center gap-2 text-sm text-red-600 mt-2">
          <AlertCircle className="w-4 h-4 flex-shrink-0" />
          <span>{error}</span>
        </div>
      )}

      {loading && departmentId && (
        <p className="text-xs text-[#6A6A6A] mt-1">Cargando ciudades...</p>
      )}
    </div>
  );
}
