import { forwardRef } from "react";
import type { ComponentPropsWithoutRef, ReactNode } from "react";
import { cn } from "./utils";
import { FormField } from "./form-field";
import { Input } from "./input";

type InputFieldProps = {
  id: string;
  label: string;
  required?: boolean;
  error?: string;
  helperText?: ReactNode;
  describedBy?: string;
  containerClassName?: string;
  inputClassName?: string;
} & ComponentPropsWithoutRef<"input">;

export const InputField = forwardRef<HTMLInputElement, InputFieldProps>(function InputField(
  {
    id,
    label,
    required = false,
    error,
    helperText,
    describedBy,
    containerClassName,
    inputClassName,
    ...inputProps
  },
  ref
) {
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
      <Input
        ref={ref}
        id={id}
        aria-invalid={Boolean(error)}
        aria-describedby={ariaDescribedBy}
        className={cn(
          "h-[50px] rounded-[14px] border border-[#E0E0E0] px-4 text-[#1A1A1A] bg-white focus-visible:ring-2 focus-visible:ring-[#4B236A]/30",
          "disabled:opacity-60 disabled:cursor-not-allowed",
          inputClassName
        )}
        {...inputProps}
      />
    </FormField>
  );
});
