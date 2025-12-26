import { Proveedor } from "@/types/admin";
import { Badge, StatusBadge } from "./badge";
import { TableActions } from "./table-actions";

interface ProvidersTableProps {
  data: Proveedor[];
}

export function ProvidersTable({ data }: ProvidersTableProps) {
  return (
    <div className="bg-white rounded-[18px] shadow-sm border border-slate-100 overflow-hidden">
      <div className="overflow-x-auto">
        <table className="w-full">
          <thead className="bg-[#F7F7F7] border-b border-[#E0E0E0]">
            <tr>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Nombre Comercial</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">NIT</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Representante Legal</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Tipo</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Teléfono</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Email</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Ubicación</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Perfil</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Estado</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Acciones</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-[#E0E0E0]">
            {data.map((proveedor) => (
              <tr key={proveedor.id} className="hover:bg-[#F7F7F7] transition">
                <td className="px-6 py-4">
                  <span className="text-sm font-medium text-[#1A1A1A]">{proveedor.nombreComercial}</span>
                </td>
                <td className="px-6 py-4">
                  <span className="text-sm text-[#6A6A6A]">{proveedor.nit}</span>
                </td>
                <td className="px-6 py-4">
                  <span className="text-sm text-[#1A1A1A]">{proveedor.representanteLegal}</span>
                </td>
                <td className="px-6 py-4">
                  <span className="text-sm text-[#6A6A6A]">{proveedor.tipoEstablecimiento}</span>
                </td>
                <td className="px-6 py-4">
                  <span className="text-sm text-[#6A6A6A]">{proveedor.telefono}</span>
                </td>
                <td className="px-6 py-4">
                  <span className="text-sm text-[#6A6A6A]">{proveedor.email}</span>
                </td>
                <td className="px-6 py-4">
                  <span className="text-sm text-[#1A1A1A]">{proveedor.ciudad}, {proveedor.departamento}</span>
                </td>
                <td className="px-6 py-4">
                  <Badge variant="perfil">{proveedor.perfil}</Badge>
                </td>
                <td className="px-6 py-4">
                  <StatusBadge activo={proveedor.activo} />
                </td>
                <td className="px-6 py-4">
                  <TableActions 
                    itemId={proveedor.id} 
                    itemName={proveedor.nombreComercial} 
                    activo={proveedor.activo} 
                  />
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
