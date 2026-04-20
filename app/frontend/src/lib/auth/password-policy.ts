export const PASSWORD_MIN_LENGTH = 10;

export interface PasswordPolicyResult {
  minLength: boolean;
  hasUppercase: boolean;
  hasLowercase: boolean;
  hasNumber: boolean;
  hasSymbol: boolean;
  noSpaces: boolean;
  isStrong: boolean;
}

export function validatePasswordPolicy(password: string): PasswordPolicyResult {
  const result: PasswordPolicyResult = {
    minLength: password.length >= PASSWORD_MIN_LENGTH,
    hasUppercase: /[A-Z]/.test(password),
    hasLowercase: /[a-z]/.test(password),
    hasNumber: /\d/.test(password),
    hasSymbol: /[^A-Za-z0-9\s]/.test(password),
    noSpaces: !/\s/.test(password),
    isStrong: false,
  };

  result.isStrong =
    result.minLength &&
    result.hasUppercase &&
    result.hasLowercase &&
    result.hasNumber &&
    result.hasSymbol &&
    result.noSpaces;

  return result;
}

export function getPasswordPolicyMessage(result: PasswordPolicyResult): string {
  if (result.isStrong) {
    return "";
  }

  return "La contraseña debe tener mínimo 10 caracteres, incluir mayúscula, minúscula, número, símbolo y no contener espacios.";
}
