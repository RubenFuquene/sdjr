"use client";

import { FormField } from "./form-field";
import { cn } from "./utils";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "./select";

export interface SelectFieldOption {
  value: string;
  label: string;
  disabled?: boolean;
}

type SelectFieldProps = {
  id: string;
  label: string;
  required?: boolean;
  error?: string;
  helperText?: string;
  describedBy?: string;
  options: SelectFieldOption[];
  value?: string;
  defaultValue?: string;
  onValueChange?: (value: string) => void;
  placeholder?: string;
  disabled?: boolean;
  name?: string;
  containerClassName?: string;
  triggerClassName?: string;
};

export function SelectField({
  id,
  label,
  required = false,
  error,
  helperText,
  describedBy,
  options,
  value,
  defaultValue,
  onValueChange,
  placeholder,
  disabled,
  name,
  containerClassName,
  triggerClassName,
}: SelectFieldProps) {
  const normalizedOptions = options.filter((option) => option.value !== "");

  const ariaDescribedBy = error
    ? describedBy
      ? `${id}-error ${describedBy}`
      : `${id}-error`
    : describedBy;

  return (
    <FormField
      id={id}
      label={label}
      required={required}
      error={error}
      helperText={helperText}
      className={containerClassName}
    >
      <Select
        value={value}
        defaultValue={defaultValue}
        onValueChange={onValueChange}
        disabled={disabled}
        name={name}
      >
        <SelectTrigger
          id={id}
          aria-invalid={Boolean(error)}
          aria-describedby={ariaDescribedBy}
          className={cn(
            "h-[50px] rounded-[14px] border border-[#E0E0E0] px-4 text-[#1A1A1A] bg-white",
            "focus:ring-2 focus:ring-[#4B236A]/30 disabled:opacity-60 disabled:cursor-not-allowed",
            triggerClassName
          )}
        >
          <SelectValue placeholder={placeholder ?? "Selecciona una opción"} />
        </SelectTrigger>
        <SelectContent>
          {normalizedOptions.map((option) => (
            <SelectItem key={option.value} value={option.value} disabled={option.disabled}>
              {option.label}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
    </FormField>
  );
}
