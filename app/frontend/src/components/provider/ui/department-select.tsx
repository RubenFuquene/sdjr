'use client';

import { useLocation } from '@/hooks/use-location';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/provider/ui/select';
import { Label } from '@/components/provider/ui/label';
import { AlertCircle } from 'lucide-react';

interface DepartmentSelectProps {
  value: number | null;
  onChange: (id: number | null) => void;
  disabled?: boolean;
  label?: string;
  required?: boolean;
  error?: string | null;
}

/**
 * Department Select Component
 * Loads departments from API and displays them in a dropdown
 * Triggers cascading updates for cities and neighborhoods
 */
export function DepartmentSelect({
  value,
  onChange,
  disabled = false,
  label = 'Departamento',
  required = false,
  error,
}: DepartmentSelectProps) {
  const { departments, loading } = useLocation();

  return (
    <div className="space-y-2 w-full">
      <Label htmlFor="department-select" className="text-sm font-medium text-[#1A1A1A]">
        {label}
        {required && <span className="text-red-500 ml-1">*</span>}
      </Label>

      <Select
        value={value?.toString() || ''}
        onValueChange={(val) => onChange(val ? parseInt(val, 10) : null)}
        disabled={disabled || loading.departments}
      >
        <SelectTrigger
          id="department-select"
          className={`h-[50px] rounded-[14px] border-[#E0E0E0] ${
            error ? 'border-red-500' : ''
          }`}
        >
          <SelectValue placeholder="Selecciona un departamento" />
        </SelectTrigger>

        <SelectContent>
          {departments.map((dept) => (
            <SelectItem key={dept.id} value={dept.id.toString()}>
              {dept.name}
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

      {loading.departments && (
        <p className="text-xs text-[#6A6A6A] mt-1">Cargando departamentos...</p>
      )}
    </div>
  );
}
