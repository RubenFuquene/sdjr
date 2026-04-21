import type { ReactNode } from "react";
import { Label } from "./label";

interface FormFieldProps {
  id: string;
  label: string;
  required?: boolean;
  error?: string;
  helperText?: ReactNode;
  className?: string;
  children: ReactNode;
}

export function FormField({
  id,
  label,
  required = false,
  error,
  helperText,
  className = "space-y-2",
  children,
}: FormFieldProps) {
  return (
    <div className={className}>
      <Label htmlFor={id} className="text-sm font-medium text-[#1A1A1A]">
        {label}
        {required ? " *" : ""}
      </Label>

      {children}

      {helperText ? <div className="text-sm text-[#6A6A6A]">{helperText}</div> : null}

      {error ? <p id={`${id}-error`} className="text-sm text-red-600">{error}</p> : null}
    </div>
  );
}
