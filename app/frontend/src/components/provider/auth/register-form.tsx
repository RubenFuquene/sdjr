"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { Check, Eye, EyeOff, Lock, Mail, User } from "lucide-react";
import { useAuthForm } from "@/hooks/use-auth-form";
import { PASSWORD_MIN_LENGTH, validatePasswordPolicy } from "@/lib/auth/password-policy";
import { Button } from "@/components/provider/ui/button";
import { Input } from "@/components/provider/ui/input";
import { Label } from "@/components/provider/ui/label";
import { cn } from "@/components/provider/ui/utils";

interface RegisterFormProps {
  onSuccess?: () => void;
  onSwitchToLogin?: () => void;
}

export function RegisterForm({ onSuccess, onSwitchToLogin }: RegisterFormProps) {
  const router = useRouter();
  const { handleRegister, loading, error, clearError } = useAuthForm();
  const [showPassword, setShowPassword] = useState(false);
  
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    password: "",
    confirmPassword: "",
  });
  const [touched, setTouched] = useState<Record<string, boolean>>({});

  // Validaciones
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  const validateName = (name: string) => name.trim().length >= 2;
  const validateEmail = (email: string) => emailRegex.test(email);
  const validatePassword = (password: string) => validatePasswordPolicy(password).isStrong;
  const validatePasswordMatch = (pwd: string, confirm: string) => pwd === confirm && pwd.length > 0;

  const passwordPolicy = validatePasswordPolicy(formData.password);

  // Obtener estado de validación para cada campo
  const isNameValid = formData.name === "" || validateName(formData.name);
  const isEmailValid = formData.email === "" || validateEmail(formData.email);
  const isPasswordValid = formData.password === "" || validatePassword(formData.password);
  const isConfirmPasswordValid = formData.confirmPassword === "" || validatePasswordMatch(formData.password, formData.confirmPassword);

  // Validar si el formulario está completo y válido
  const isFormValid =
    formData.name.trim().length >= 2 &&
    validateEmail(formData.email) &&
    validatePassword(formData.password) &&
    validatePasswordMatch(formData.password, formData.confirmPassword);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
    // Limpiar error cuando el usuario empieza a escribir
    if (error) clearError();
  };

  const handleBlur = (fieldName: string) => {
    setTouched((prev) => ({
      ...prev,
      [fieldName]: true,
    }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    clearError();
    
    if (!isFormValid) {
      return;
    }

    try {
      // Usar el hook para registro
      const { redirectTo } = await handleRegister(
        formData.name,
        formData.email,
        formData.password,
        formData.confirmPassword
      );
      
      // Registro exitoso - redirigir al dashboard
      onSuccess?.();
      router.push(redirectTo);
    } catch {
      // El error ya está en el estado del hook (mostrado en UI)
      // No hacemos nada aquí, el componente lo renderiza
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      {/* Campo Nombre */}
      <div className="space-y-2">
        <Label htmlFor="name" className="text-[#1A1A1A] font-semibold">
          Nombre del Negocio
        </Label>
        <div className="relative">
          <User className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
          <Input
            id="name"
            name="name"
            type="text"
            placeholder="Mi Negocio"
            value={formData.name}
            onChange={handleChange}
            onBlur={() => handleBlur("name")}
            disabled={loading}
            className={cn(
              "pl-10 rounded-[14px] border-[#E0E0E0] focus-visible:ring-[#4B236A] placeholder-gray-400",
              touched.name && !isNameValid && "border-red-300 bg-red-50"
            )}
          />
        </div>
        {touched.name && !isNameValid && (
          <p className="text-xs text-red-600">
            El nombre debe tener al menos 2 caracteres.
          </p>
        )}
      </div>

      {/* Campo Email */}
      <div className="space-y-2">
        <Label htmlFor="email" className="text-[#1A1A1A] font-semibold">
          Correo Electrónico
        </Label>
        <div className="relative">
          <Mail className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
          <Input
            id="email"
            name="email"
            type="email"
            placeholder="tu@correo.com"
            value={formData.email}
            onChange={handleChange}
            onBlur={() => handleBlur("email")}
            disabled={loading}
            className={cn(
              "pl-10 rounded-[14px] border-[#E0E0E0] focus-visible:ring-[#4B236A] placeholder-gray-400",
              touched.email && !isEmailValid && "border-red-300 bg-red-50"
            )}
          />
        </div>
        {touched.email && !isEmailValid && (
          <p className="text-xs text-red-600">
            Por favor ingresa un correo válido.
          </p>
        )}
      </div>

      {/* Campo Contraseña */}
      <div className="space-y-2">
        <Label htmlFor="password" className="text-[#1A1A1A] font-semibold">
          Contraseña
        </Label>
        <div className="relative">
          <Lock className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
          <button
            type="button"
            onClick={() => setShowPassword((prev) => !prev)}
            disabled={loading}
            className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 transition-colors hover:text-[#4B236A] disabled:opacity-50"
            aria-label={showPassword ? "Ocultar contraseña" : "Mostrar contraseña"}
          >
            {showPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
          </button>
          <Input
            id="password"
            name="password"
            type={showPassword ? "text" : "password"}
            placeholder="••••••"
            value={formData.password}
            onChange={handleChange}
            onBlur={() => handleBlur("password")}
            disabled={loading}
            className={cn(
              "pl-10 pr-10 rounded-[14px] border-[#E0E0E0] focus-visible:ring-[#4B236A] placeholder-gray-400",
              touched.password && !isPasswordValid && "border-red-300 bg-red-50"
            )}
            aria-label="Contraseña"
          />
        </div>
        {touched.password && !isPasswordValid && (
          <p className="text-xs text-red-600">
            Debe cumplir todos los criterios de seguridad.
          </p>
        )}

        <div className="rounded-[12px] border border-[#E0E0E0] bg-[#F7F7F7] p-3">
          <p className="mb-2 text-xs font-medium text-[#1A1A1A]">La contraseña debe cumplir:</p>
          <ul className="space-y-1 text-xs">
            <li className={cn(passwordPolicy.minLength ? "text-emerald-700" : "text-[#6A6A6A]") }>
              {passwordPolicy.minLength ? "✓" : "•"} Mínimo {PASSWORD_MIN_LENGTH} caracteres
            </li>
            <li className={cn(passwordPolicy.hasUppercase ? "text-emerald-700" : "text-[#6A6A6A]") }>
              {passwordPolicy.hasUppercase ? "✓" : "•"} Al menos una mayúscula (A-Z)
            </li>
            <li className={cn(passwordPolicy.hasLowercase ? "text-emerald-700" : "text-[#6A6A6A]") }>
              {passwordPolicy.hasLowercase ? "✓" : "•"} Al menos una minúscula (a-z)
            </li>
            <li className={cn(passwordPolicy.hasNumber ? "text-emerald-700" : "text-[#6A6A6A]") }>
              {passwordPolicy.hasNumber ? "✓" : "•"} Al menos un número (0-9)
            </li>
            <li className={cn(passwordPolicy.hasSymbol ? "text-emerald-700" : "text-[#6A6A6A]") }>
              {passwordPolicy.hasSymbol ? "✓" : "•"} Al menos un carácter especial (ej. !@#$%)
            </li>
            <li className={cn(passwordPolicy.noSpaces ? "text-emerald-700" : "text-[#6A6A6A]") }>
              {passwordPolicy.noSpaces ? "✓" : "•"} Sin espacios
            </li>
          </ul>
        </div>
      </div>

      {/* Campo Confirmar Contraseña */}
      <div className="space-y-2">
        <Label htmlFor="confirmPassword" className="text-[#1A1A1A] font-semibold">
          Confirmar Contraseña
        </Label>
        <div className="relative">
          <Check className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
          <button
            type="button"
            onClick={() => setShowPassword((prev) => !prev)}
            disabled={loading}
            className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 transition-colors hover:text-[#4B236A] disabled:opacity-50"
            aria-label={showPassword ? "Ocultar contraseña" : "Mostrar contraseña"}
          >
            {showPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
          </button>
          <Input
            id="confirmPassword"
            name="confirmPassword"
            type={showPassword ? "text" : "password"}
            placeholder="••••••"
            value={formData.confirmPassword}
            onChange={handleChange}
            onBlur={() => handleBlur("confirmPassword")}
            disabled={loading}
            className={cn(
              "pl-10 pr-10 rounded-[14px] border-[#E0E0E0] focus-visible:ring-[#4B236A] placeholder-gray-400",
              touched.confirmPassword && !isConfirmPasswordValid && "border-red-300 bg-red-50"
            )}
            aria-label="Confirmar contraseña"
          />
        </div>
        {touched.confirmPassword && !isConfirmPasswordValid && (
          <p className="text-xs text-red-600">
            Las contraseñas no coinciden.
          </p>
        )}
      </div>

      {/* Error Global */}
      {error && (
        <div className="rounded-[12px] bg-red-50 border border-red-200 p-3">
          <p className="text-sm text-red-700">{error}</p>
        </div>
      )}

      {/* Botón Submit */}
      <Button
        type="submit"
        disabled={loading || !isFormValid}
        className="w-full h-[52px] bg-[#4B236A] hover:bg-[#5D2B7D] rounded-xl font-medium text-white shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {loading ? (
          <span className="flex items-center gap-2">
            <span className="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
            Creando cuenta...
          </span>
        ) : (
          "Crear Cuenta"
        )}
      </Button>

      {/* Link Login */}
      <p className="text-center text-sm text-[#6A6A6A]">
        ¿Ya tienes cuenta?{" "}
        <button
          type="button"
          onClick={onSwitchToLogin}
          className="font-medium text-[#4B236A] hover:underline"
        >
          Inicia sesión
        </button>
      </p>
    </form>
  );
}
