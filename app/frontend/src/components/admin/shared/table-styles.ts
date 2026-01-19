/**
 * Estilos centralizados para tablas administrador
 * 
 * Garantiza consistencia visual a lo largo del panel admin
 * y facilita cambios globales de estilo.
 * 
 * @example
 * import { TABLE_STYLES } from '@/components/admin/shared/table-styles';
 * 
 * <div className={TABLE_STYLES.container}>
 *   <div className="overflow-x-auto">
 *     <table className="w-full">
 *       <thead className={TABLE_STYLES.headerRow}>
 *         <tr>
 *           <th className={TABLE_STYLES.headerCell}>Nombre</th>
 *         </tr>
 *       </thead>
 *       <tbody className={TABLE_STYLES.rowDivider}>
 *         {data.map((item) => (
 *           <tr key={item.id} className={TABLE_STYLES.bodyRow}>
 *             <td className={TABLE_STYLES.bodyCell}>
 *               <span className={TABLE_STYLES.bodyCellText}>{item.name}</span>
 *             </td>
 *           </tr>
 *         ))}
 *       </tbody>
 *     </table>
 *   </div>
 * </div>
 */

export const TABLE_STYLES = {
  // ============================================
  // Contenedor Principal
  // ============================================
  container:
    'bg-white rounded-[18px] shadow-sm border border-slate-100 overflow-hidden',

  // ============================================
  // Header (Encabezados)
  // ============================================
  /** Clase para <thead> */
  headerRow: 'bg-[#F7F7F7] border-b border-[#E0E0E0]',
  
  /** Clase para <th> en header */
  headerCell: 'px-6 py-4 text-left text-sm font-medium text-[#1A1A1A]',

  // ============================================
  // Body (Cuerpo de la tabla)
  // ============================================
  /** Clase para <tr> en tbody */
  bodyRow: 'hover:bg-[#F7F7F7] transition',
  
  /** Clase para <td> base */
  bodyCell: 'px-6 py-4',
  
  /** Texto estándar en celdas */
  bodyCellText: 'text-sm text-[#1A1A1A]',
  
  /** Texto secundario/muted en celdas */
  bodyCellMuted: 'text-sm text-[#6A6A6A]',
  
  /** Texto bold en celdas */
  bodyCellBold: 'text-sm font-medium text-[#1A1A1A]',

  // ============================================
  // Divisores y Bordes
  // ============================================
  /** Clase para <tbody> para separar filas */
  rowDivider: 'divide-y divide-[#E0E0E0]',
} as const;
