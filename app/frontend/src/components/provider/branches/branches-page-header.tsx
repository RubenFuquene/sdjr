import { AddBranchButton } from "./add-branch-button";

interface BranchesPageHeaderProps {
  onAddBranch: () => void;
}

export function BranchesPageHeader({ onAddBranch }: BranchesPageHeaderProps) {
  return (
    <div className="mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
      <div>
        <h1 className="text-2xl md:text-3xl font-bold text-gray-900">Sucursales</h1>
        <p className="text-gray-600 mt-2">Administra las ubicaciones de tu negocio</p>
      </div>

      <AddBranchButton
        label="Agregar Sucursal"
        className="w-full md:w-auto"
        onClick={onAddBranch}
      />
    </div>
  );
}
