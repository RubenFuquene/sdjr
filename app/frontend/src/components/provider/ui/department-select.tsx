'use client';

import type { Department } from '@/types/location';
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
  departments: Department[];
  value: number | null;
  onChange: (id: number | null) => void;
  disabled?: boolean;
  loading?: boolean;
  label?: string;
  required?: boolean;
  error?: string | null;
}

/**
 * Department Select Component (presentational)
 * Data loading and cascading logic should be handled by parent form.
 */
export function DepartmentSelect({
  departments,
  value,
  onChange,
  disabled = false,
  loading = false,
  label = 'Departamento',
  required = false,
  error,
}: DepartmentSelectProps) {
  return (
    <div className="space-y-2 w-full">
      <Label htmlFor="department-select" className="text-sm font-medium text-[#1A1A1A]">
        {label}
        {required && <span className="text-red-500 ml-1">*</span>}
      </Label>

      <Select
        value={value?.toString()}
        onValueChange={(val) => onChange(val ? parseInt(val, 10) : null)}
        disabled={disabled || loading}
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

      {loading && (
        <p className="text-xs text-[#6A6A6A] mt-1">Cargando departamentos...</p>
      )}
    </div>
  );
}
