import { BasicInfoForm } from '@/components/provider/forms';

/**
 * Página: Datos Básicos del Proveedor
 *
 * Estructura:
 * - Header con título y subtítulo
 * - 4 Cards principales (una por cada sección)
 * - Botones de acción (Cancelar, Guardar y Continuar)
 *
 * Este es un Client Component que maneja el estado del formulario.
 */
export default function BasicInfoPage() {
  return (
    <div className="min-h-screen bg-gray-50 p-6 md:p-8">
      <div className="max-w-4xl mx-auto">
        <div className="mb-8">
          <h1 className="text-2xl md:text-3xl font-bold text-gray-900">
            Datos Básicos del Proveedor
          </h1>
          <p className="text-gray-600 mt-2">
            Completa la información de tu negocio para comenzar
          </p>
        </div>

        <BasicInfoForm />
      </div>
    </div>
  );
}
