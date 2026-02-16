import { Administrador } from "@/types/admin";
import { Badge, StatusBadge } from "@/components/admin/shared/badge";
import { TableActions } from "@/components/admin/shared/table-actions";
import { TABLE_STYLES } from "@/components/admin/shared/table-styles";

interface AdministratorsTableProps {
  data: Administrador[];
  onView?: (admin: Administrador) => void;
  onEdit?: (admin: Administrador) => void;
  onToggle?: (admin: Administrador) => void;
  onDelete?: (admin: Administrador) => void;
}

export function AdministratorsTable({
  data,
  onView,
  onEdit,
  onToggle,
  onDelete,
}: AdministratorsTableProps) {
  return (
    <div className={TABLE_STYLES.container}>
      <div className="overflow-x-auto">
        <table className="w-full">
          <thead className={TABLE_STYLES.headerRow}>
            <tr>
              <th className={TABLE_STYLES.headerCell}>Nombres</th>
              <th className={TABLE_STYLES.headerCell}>Apellidos</th>
              <th className={TABLE_STYLES.headerCell}>Correo</th>
              <th className={TABLE_STYLES.headerCell}>Área</th>
              <th className={TABLE_STYLES.headerCell}>Perfil</th>
              <th className={TABLE_STYLES.headerCell}>Estado</th>
              <th className={TABLE_STYLES.headerCell}>Acciones</th>
            </tr>
          </thead>
          <tbody className={TABLE_STYLES.rowDivider}>
            {data.map((admin) => (
              <tr key={admin.id} className={TABLE_STYLES.bodyRow}>
                <td className={TABLE_STYLES.bodyCell}>
                  <span className={TABLE_STYLES.bodyCellText}>{admin.nombres}</span>
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <span className={TABLE_STYLES.bodyCellText}>{admin.apellidos}</span>
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <span className={TABLE_STYLES.bodyCellMuted}>{admin.correo}</span>
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <span className={TABLE_STYLES.bodyCellMuted}>{admin.area}</span>
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <Badge variant="perfil">{admin.perfil}</Badge>
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <StatusBadge activo={admin.activo} />
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <TableActions 
                    itemId={admin.id} 
                    itemName={`${admin.nombres} ${admin.apellidos}`} 
                    activo={admin.activo}
                    onView={onView ? () => onView(admin) : undefined}
                    onEdit={onEdit ? () => onEdit(admin) : undefined}
                    onToggle={onToggle ? () => onToggle(admin) : undefined}
                    onDelete={onDelete ? () => onDelete(admin) : undefined}
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
