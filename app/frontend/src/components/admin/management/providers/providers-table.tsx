'use client';

import { ProveedorListItem } from "@/types/admin";
import { Badge, StatusBadge } from "@/components/admin/shared/badge";
import { TableActions } from "@/components/admin/shared/table-actions";
import { TABLE_STYLES } from "@/components/admin/shared/table-styles";

/**
 * Tabla Presentacional de Proveedores
 * 
 * Responsabilidad única: Renderizar tabla
 * - Sin lógica de negocio
 * - Props para handlers de acciones
 * - Componente puro y reutilizable
 */
interface ProvidersTableProps {
  data: ProveedorListItem[];
  onView?: (proveedor: ProveedorListItem) => void;
  onEdit?: (proveedor: ProveedorListItem) => void;
  onToggle?: (proveedor: ProveedorListItem) => void;
  onDelete?: (proveedor: ProveedorListItem) => void;
}

export function ProvidersTable({ 
  data, 
  onView, 
  onEdit, 
  onToggle, 
  onDelete 
}: ProvidersTableProps) {
  return (
    <div className={TABLE_STYLES.container}>
      <div className="overflow-x-auto">
        <table className="w-full">
          <thead className={TABLE_STYLES.headerRow}>
            <tr>
              <th className={TABLE_STYLES.headerCell}>Nombre Comercial</th>
              <th className={TABLE_STYLES.headerCell}>Representante Legal</th>
              <th className={TABLE_STYLES.headerCell}>Teléfono</th>
              <th className={TABLE_STYLES.headerCell}>Email</th>
              <th className={TABLE_STYLES.headerCell}>Perfil</th>
              <th className={TABLE_STYLES.headerCell}>Estado</th>
              <th className={TABLE_STYLES.headerCell}>Acciones</th>
            </tr>
          </thead>
          <tbody className={TABLE_STYLES.rowDivider}>
            {data.map((proveedor) => (
              <tr key={proveedor.id} className={TABLE_STYLES.bodyRow}>
                <td className={TABLE_STYLES.bodyCell}>
                  <span className={TABLE_STYLES.bodyCellBold}>{proveedor.nombreComercial}</span>
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <span className={TABLE_STYLES.bodyCellText}>{proveedor.representanteLegal}</span>
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <span className={TABLE_STYLES.bodyCellMuted}>{proveedor.telefono}</span>
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <span className={TABLE_STYLES.bodyCellMuted}>{proveedor.email}</span>
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <Badge variant="perfil">{proveedor.perfil}</Badge>
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <StatusBadge activo={proveedor.estado} />
                </td>
                <td className={TABLE_STYLES.bodyCell}>
                  <TableActions 
                    itemId={proveedor.id} 
                    itemName={proveedor.nombreComercial} 
                    activo={proveedor.estado}
                    onView={onView ? () => onView(proveedor) : undefined}
                    onEdit={onEdit ? () => onEdit(proveedor) : undefined}
                    onToggle={onToggle ? () => onToggle(proveedor) : undefined}
                    onDelete={onDelete ? () => onDelete(proveedor) : undefined}
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
