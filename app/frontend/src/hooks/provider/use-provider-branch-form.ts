"use client";

import { useCallback } from "react";
import {
  useCreateProviderBranch,
  type BranchFormFieldErrors,
  type CreateProviderBranchFormInput,
} from "./use-create-provider-branch";

interface SubmitOptions {
  onSuccess?: (branch: unknown) => Promise<void> | void;
}

export type ProviderBranchFormInput = CreateProviderBranchFormInput;
export type ProviderBranchFormFieldErrors = BranchFormFieldErrors;

export function useProviderBranchForm() {
  const {
    submitting,
    error,
    fieldErrors,
    submit,
    clearErrors,
  } = useCreateProviderBranch();

  const submitForm = useCallback(
    async (input: ProviderBranchFormInput, options?: SubmitOptions) => {
      return submit(input, options);
    },
    [submit]
  );

  const clearFormErrors = useCallback(() => {
    clearErrors();
  }, [clearErrors]);

  return {
    isSubmitting: submitting,
    error,
    fieldErrors,
    submitForm,
    clearFormErrors,
  };
}
