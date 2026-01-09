import { Perfil } from "@/types/admin";
import { Badge, StatusBadge } from "@/components/admin/shared/badge";
import { TableActions } from "@/components/admin/shared/table-actions";

interface RolesTableProps {
  data: Perfil[];
  onView?: (perfil: Perfil) => void;
  onEdit?: (perfil: Perfil) => void;
}

export function RolesTable({ data, onView, onEdit }: RolesTableProps) {
  return (
    <div className="bg-white rounded-[18px] shadow-sm border border-slate-100 overflow-hidden">
      <div className="overflow-x-auto">
        <table className="w-full">
          <thead className="bg-[#F7F7F7] border-b border-[#E0E0E0]">
            <tr>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Perfil</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Descripción</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Permisos Admin</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Permisos Proveedor</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Usuarios</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Estado</th>
              <th className="px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]">Acciones</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-[#E0E0E0]">
            {data.map((perfil) => (
              <tr key={perfil.id} className="hover:bg-[#F7F7F7] transition">
                <td className="px-6 py-4">
                  <span className="text-sm font-medium text-[#1A1A1A]">{perfil.nombre}</span>
                </td>
                <td className="px-6 py-4">
                  <span className="text-sm text-[#6A6A6A]">{perfil.descripcion}</span>
                </td>
                <td className="px-6 py-4">
                  <div className="flex flex-wrap gap-1">
                    {perfil.permisosAdmin.length > 0 ? (
                      perfil.permisosAdmin.slice(0, 2).map((permiso, idx) => (
                        <Badge key={idx} variant="permiso">
                          {permiso.description}
                        </Badge>
                      ))
                    ) : (
                      <span className="text-sm text-[#6A6A6A]">-</span>
                    )}
                    {perfil.permisosAdmin.length > 2 && (
                      <Badge variant="permiso">+{perfil.permisosAdmin.length - 2}</Badge>
                    )}
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="flex flex-wrap gap-1">
                    {perfil.permisosProveedor.length > 0 ? (
                      perfil.permisosProveedor.slice(0, 2).map((permiso, idx) => (
                        <Badge key={idx} variant="permiso">
                          {permiso.description}
                        </Badge>
                      ))
                    ) : (
                      <span className="text-sm text-[#6A6A6A]">-</span>
                    )}
                    {perfil.permisosProveedor.length > 2 && (
                      <Badge variant="permiso">+{perfil.permisosProveedor.length - 2}</Badge>
                    )}
                  </div>
                </td>
                <td className="px-6 py-4">
                  <span className="text-sm text-[#1A1A1A]">{perfil.usuarios}</span>
                </td>
                <td className="px-6 py-4">
                  <StatusBadge activo={perfil.activo} />
                </td>
                <td className="px-6 py-4">
                  <TableActions 
                    itemId={perfil.id} 
                    itemName={perfil.nombre} 
                    activo={perfil.activo}
                    onView={onView ? () => onView(perfil) : undefined}
                    onEdit={onEdit ? () => onEdit(perfil) : undefined}
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
