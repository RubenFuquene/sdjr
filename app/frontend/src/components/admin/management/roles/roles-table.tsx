import { Perfil } from "@/types/admin";
import { Badge, StatusBadge } from "@/components/admin/shared/badge";
import { TableActions } from "@/components/admin/shared/table-actions";
import { TABLE_STYLES } from "@/components/admin/shared/table-styles";

interface RolesTableProps {
  data: Perfil[];
  onView?: (perfil: Perfil) => void;
  onEdit?: (perfil: Perfil) => void;
}

export function RolesTable({ data, onView, onEdit }: RolesTableProps) {
  return (
    <div className={TABLE_STYLES.container}>
      <div className="overflow-x-auto">
        <table className="w-full">
          <thead className={TABLE_STYLES.headerRow}>
            <tr>
              <th className={TABLE_STYLES.headerCell}>Perfil</th>
              <th className={TABLE_STYLES.headerCell}>Descripción</th>
              <th className={TABLE_STYLES.headerCell}>Permisos Admin</th>
              <th className={TABLE_STYLES.headerCell}>Permisos Proveedor</th>
              <th className={TABLE_STYLES.headerCell}>Usuarios</th>
              <th className={TABLE_STYLES.headerCell}>Estado</th>
              <th className={TABLE_STYLES.headerCell}>Acciones</th>
            </tr>
          </thead>
          <tbody className={TABLE_STYLES.rowDivider}>
            {data.map((perfil) => (
              <tr key={perfil.id} className={TABLE_STYLES.bodyRow}>
                <td className={TABLE_STYLES.bodyCell}>
                  <span className={TABLE_STYLES.bodyCellBold}>{perfil.nombre}</span>
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <span className={TABLE_STYLES.bodyCellMuted}>{perfil.descripcion}</span>
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <div className="flex flex-wrap gap-1">
                    {perfil.permisosAdmin.length > 0 ? (
                      perfil.permisosAdmin.slice(0, 2).map((permiso, idx) => (
                        <Badge key={idx} variant="permiso">
                          {permiso.description}
                        </Badge>
                      ))
                    ) : (
                      <span className={TABLE_STYLES.bodyCellMuted}>-</span>
                    )}
                    {perfil.permisosAdmin.length > 2 && (
                      <Badge variant="permiso">+{perfil.permisosAdmin.length - 2}</Badge>
                    )}
                  </div>
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <div className="flex flex-wrap gap-1">
                    {perfil.permisosProveedor.length > 0 ? (
                      perfil.permisosProveedor.slice(0, 2).map((permiso, idx) => (
                        <Badge key={idx} variant="permiso">
                          {permiso.description}
                        </Badge>
                      ))
                    ) : (
                      <span className={TABLE_STYLES.bodyCellMuted}>-</span>
                    )}
                    {perfil.permisosProveedor.length > 2 && (
                      <Badge variant="permiso">+{perfil.permisosProveedor.length - 2}</Badge>
                    )}
                  </div>
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <span className={TABLE_STYLES.bodyCellText}>{perfil.usuarios}</span>
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <StatusBadge activo={perfil.activo} />
                </td>
                <td className={TABLE_STYLES.bodyCell}>
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
