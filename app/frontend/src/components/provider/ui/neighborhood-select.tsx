'use client';

import { useState, useEffect } from 'react';
import { getNeighborhoods } from '@/lib/api/location';
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
  cityId: number | null;
  value: string | null;
  onChange: (value: string | null) => void;
  disabled?: boolean;
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
 * Automatically fetches neighborhoods when city changes
 */
export function NeighborhoodSelect({
  cityId,
  value,
  onChange,
  disabled = false,
  label = 'Barrio',
  required = false,
  error,
}: NeighborhoodSelectProps) {
  const [neighborhoods, setNeighborhoods] = useState<Neighborhood[]>([]);
  const [loading, setLoading] = useState(false);
  const [fetchError, setFetchError] = useState<string | null>(null);

  /**
   * Effect: Fetch neighborhoods when cityId changes
   * Automatically reset neighborhood selection when city changes
   */
  useEffect(() => {
    if (!cityId) {
      setNeighborhoods([]);
      onChange(null); // Reset neighborhood selection when city is cleared
      return;
    }

    const fetchNeighborhoods = async () => {
      setLoading(true);
      setFetchError(null);
      try {
        // Fetch neighborhoods with optional city_id filter (if backend supports it)
        // For now: fetch all neighborhoods, filter on frontend
        const response = await getNeighborhoods({ per_page: 100 });
        const filtered = response.data.filter((nbh) => nbh.city_id === cityId);
        setNeighborhoods(filtered);
        onChange(null); // Reset selection when loading new neighborhoods
      } catch (err) {
        const errorMessage = err instanceof Error ? err.message : 'Error al cargar barrios';
        setFetchError(errorMessage);
      } finally {
        setLoading(false);
      }
    };

    fetchNeighborhoods();
  }, [cityId]); // Remove onChange from dependencies to avoid infinite loop

  const hasNeighborhoods = neighborhoods.length > 0;
  const isDisabled = disabled || !cityId;

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
          value={value?.toString() || ''}
          onValueChange={(val) => onChange(val || null)}
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
      ) : (
        // Show Input field if no neighborhoods available
        <div className="relative">
          <Input
            id="neighborhood-input"
            type="text"
            value={value || ''}
            onChange={(e) => onChange(e.target.value || null)}
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
      )}

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

      {loading && cityId && hasNeighborhoods && (
        <p className="text-xs text-[#6A6A6A] mt-1">Cargando barrios...</p>
      )}
    </div>
  );
}
