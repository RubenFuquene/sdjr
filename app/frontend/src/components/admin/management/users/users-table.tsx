import { Usuario } from "@/types/admin";
import { Badge, StatusBadge } from "@/components/admin/shared/badge";
import { TableActions } from "@/components/admin/shared/table-actions";

interface UsersTableProps {
  data: Usuario[];
}

export function UsersTable({ data }: UsersTableProps) {
  return (
    <div className="bg-white rounded-[18px] shadow-sm border border-slate-100 overflow-hidden">
      <div className="overflow-x-auto">
        <table className="w-full">
          <thead className="bg-[#F7F7F7] border-b border-[#E0E0E0]">
            <tr>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Nombres</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Apellidos</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Celular</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Email</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Perfil</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Estado</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Acciones</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-[#E0E0E0]">
            {data.map((usuario) => (
              <tr key={usuario.id} className="hover:bg-[#F7F7F7] transition">
                <td className="px-6 py-4">
                  <span className="text-sm text-[#1A1A1A]">{usuario.nombres}</span>
                </td>
                <td className="px-6 py-4">
                  <span className="text-sm text-[#1A1A1A]">{usuario.apellidos}</span>
                </td>
                <td className="px-6 py-4">
                  <span className="text-sm text-[#6A6A6A]">{usuario.celular}</span>
                </td>
                <td className="px-6 py-4">
                  <span className="text-sm text-[#6A6A6A]">{usuario.email}</span>
                </td>
                <td className="px-6 py-4">
                  <Badge variant="perfil">{usuario.perfil}</Badge>
                </td>
                <td className="px-6 py-4">
                  <StatusBadge activo={usuario.activo} />
                </td>
                <td className="px-6 py-4">
                  <TableActions 
                    itemId={usuario.id} 
                    itemName={`${usuario.nombres} ${usuario.apellidos}`} 
                    activo={usuario.activo} 
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
