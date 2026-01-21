'use client';

import { useState, useEffect } from 'react';
import { getCities } from '@/lib/api/location';
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
  departmentId: number | null;
  value: number | null;
  onChange: (id: number | null) => void;
  disabled?: boolean;
  label?: string;
  required?: boolean;
  error?: string | null;
}

/**
 * City Select Component
 * Displays cities filtered by selected department
 * Automatically fetches cities when department changes
 * Triggers cascading updates for neighborhoods
 */
export function CitySelect({
  departmentId,
  value,
  onChange,
  disabled = false,
  label = 'Ciudad',
  required = false,
  error,
}: CitySelectProps) {
  const [cities, setCities] = useState<City[]>([]);
  const [loading, setLoading] = useState(false);
  const [fetchError, setFetchError] = useState<string | null>(null);

  /**
   * Effect: Fetch cities when departmentId changes
   * Automatically reset city selection when department changes
   */
  useEffect(() => {
    if (!departmentId) {
      setCities([]);
      onChange(null); // Reset city selection when department is cleared
      return;
    }

    const fetchCities = async () => {
      setLoading(true);
      setFetchError(null);
      try {
        // Fetch cities with optional department_id filter (if backend supports it)
        // For now: fetch all cities, filter on frontend
        const response = await getCities({ per_page: 100 });
        const filtered = response.data.filter((city) => city.department_id === departmentId);
        setCities(filtered);
        onChange(null); // Reset selection when loading new cities
      } catch (err) {
        const errorMessage = err instanceof Error ? err.message : 'Error al cargar ciudades';
        setFetchError(errorMessage);
      } finally {
        setLoading(false);
      }
    };

    fetchCities();
  }, [departmentId]); // onChange omitida intencionalmente para evitar loops infinitos (es callback estable)

  const isDisabled = disabled || !departmentId || loading;

  return (
    <div className="space-y-2 w-full">
      <Label htmlFor="city-select" className="text-sm font-medium text-[#1A1A1A]">
        {label}
        {required && <span className="text-red-500 ml-1">*</span>}
      </Label>

      <Select
        value={value?.toString() || ''}
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

      {fetchError && (
        <div className="flex items-center gap-2 text-sm text-red-600 mt-2">
          <AlertCircle className="w-4 h-4 flex-shrink-0" />
          <span>{fetchError}</span>
        </div>
      )}

      {loading && departmentId && (
        <p className="text-xs text-[#6A6A6A] mt-1">Cargando ciudades...</p>
      )}
    </div>
  );
}
