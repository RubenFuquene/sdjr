import { MapPin } from "lucide-react";
import { AddBranchButton } from "./add-branch-button";

interface BranchesEmptyStateProps {
  onAddBranch: () => void;
}

export function BranchesEmptyState({ onAddBranch }: BranchesEmptyStateProps) {
  return (
    <div className="bg-white rounded-[18px] shadow-sm p-6 md:p-8 border border-dashed border-[#E0E0E0]">
      <div className="flex flex-col items-center justify-center py-16 text-center">
        <MapPin className="w-16 h-16 text-gray-300 mb-4" />
        <h2 className="text-gray-900 font-semibold mb-2">No hay sucursales registradas</h2>
        <p className="text-gray-600 mb-6 max-w-md">
          Comienza agregando las sedes de tu negocio para que los clientes puedan encontrarte
        </p>

        <AddBranchButton
          label="Agregar Primera Sucursal"
          ariaLabel="Agregar primera sucursal"
          onClick={onAddBranch}
        />
      </div>
    </div>
  );
}
