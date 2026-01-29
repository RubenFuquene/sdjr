/**
 * Tabla Presentacional de Usuarios
 * 
 * Responsabilidad única: Renderizar UI
 * - Recibe datos y handlers como props
 * - No tiene estado interno
 * - No hace llamadas API
 * - Solo renderiza basado en props
 * 
 * Patrón: Presentational Component (similar a ProvidersTable)
 */

import { Usuario } from "@/types/admin";
import { Badge, StatusBadge } from "@/components/admin/shared/badge";
import { TableActions } from "@/components/admin/shared/table-actions";
import { TABLE_STYLES } from "@/components/admin/shared/table-styles";

interface UsersTableProps {
  data: Usuario[];
  onView: (usuario: Usuario) => void;
  onEdit: (usuario: Usuario) => void;
  onToggle: (usuario: Usuario) => void;
  onDelete: (usuario: Usuario) => void;
}

export function UsersTable({ 
  data, 
  onView, 
  onEdit, 
  onToggle, 
  onDelete 
}: UsersTableProps) {
  return (
    <div className={TABLE_STYLES.container}>
      <div className="overflow-x-auto">
        <table className="w-full">
          <thead className={TABLE_STYLES.headerRow}>
            <tr>
              <th className={TABLE_STYLES.headerCell}>Nombres</th>
              <th className={TABLE_STYLES.headerCell}>Apellidos</th>
              <th className={TABLE_STYLES.headerCell}>Celular</th>
              <th className={TABLE_STYLES.headerCell}>Email</th>
              <th className={TABLE_STYLES.headerCell}>Perfil</th>
              <th className={TABLE_STYLES.headerCell}>Estado</th>
              <th className={TABLE_STYLES.headerCell}>Acciones</th>
            </tr>
          </thead>
          <tbody className={TABLE_STYLES.rowDivider}>
            {data.map((usuario) => (
              <tr key={usuario.id} className={TABLE_STYLES.bodyRow}>
                <td className={TABLE_STYLES.bodyCell}>
                  <span className={TABLE_STYLES.bodyCellText}>{usuario.nombres}</span>
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <span className={TABLE_STYLES.bodyCellText}>{usuario.apellidos}</span>
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <span className={TABLE_STYLES.bodyCellMuted}>{usuario.celular}</span>
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <span className={TABLE_STYLES.bodyCellMuted}>{usuario.email}</span>
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <Badge variant="perfil">{usuario.perfil}</Badge>
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <StatusBadge activo={usuario.activo} />
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <TableActions 
                    itemId={usuario.id} 
                    itemName={`${usuario.nombres} ${usuario.apellidos}`} 
                    activo={usuario.activo}
                    onView={() => onView(usuario)}
                    onEdit={() => onEdit(usuario)}
                    onToggle={() => onToggle(usuario)}
                    onDelete={() => onDelete(usuario)}
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
