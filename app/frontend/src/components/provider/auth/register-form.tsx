"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { Mail, Lock, User, Check } from "lucide-react";
import { useAuthForm } from "@/hooks/use-auth-form";
import { Button } from "@/components/provider/ui/button";
import { Input } from "@/components/provider/ui/input";
import { Label } from "@/components/provider/ui/label";
import { cn } from "@/components/provider/ui/utils";

interface RegisterFormProps {
  onSuccess?: () => void;
}

export function RegisterForm({ onSuccess }: RegisterFormProps) {
  const router = useRouter();
  const { handleRegister, loading, error, clearError } = useAuthForm();
  
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
  const validatePassword = (password: string) => password.length >= 6;
  const validatePasswordMatch = (pwd: string, confirm: string) => pwd === confirm && pwd.length > 0;

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
        formData.password
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
          <Input
            id="password"
            name="password"
            type="password"
            placeholder="••••••"
            value={formData.password}
            onChange={handleChange}
            onBlur={() => handleBlur("password")}
            disabled={loading}
            className={cn(
              "pl-10 rounded-[14px] border-[#E0E0E0] focus-visible:ring-[#4B236A] placeholder-gray-400",
              touched.password && !isPasswordValid && "border-red-300 bg-red-50"
            )}
          />
        </div>
        {touched.password && !isPasswordValid && (
          <p className="text-xs text-red-600">
            La contraseña debe tener al menos 6 caracteres.
          </p>
        )}
      </div>

      {/* Campo Confirmar Contraseña */}
      <div className="space-y-2">
        <Label htmlFor="confirmPassword" className="text-[#1A1A1A] font-semibold">
          Confirmar Contraseña
        </Label>
        <div className="relative">
          <Check className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
          <Input
            id="confirmPassword"
            name="confirmPassword"
            type="password"
            placeholder="••••••"
            value={formData.confirmPassword}
            onChange={handleChange}
            onBlur={() => handleBlur("confirmPassword")}
            disabled={loading}
            className={cn(
              "pl-10 rounded-[14px] border-[#E0E0E0] focus-visible:ring-[#4B236A] placeholder-gray-400",
              touched.confirmPassword && !isConfirmPasswordValid && "border-red-300 bg-red-50"
            )}
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
        <a href="#" className="font-medium text-[#4B236A] hover:underline">
          Inicia sesión
        </a>
      </p>
    </form>
  );
}
