"use client";

import { Eye, Edit2, Power, Trash2 } from "lucide-react";

interface TableActionsProps {
  itemId: number;
  itemName: string;
  activo?: boolean;
  onView?: () => void;
  onEdit?: () => void;
  onToggle?: () => void;
  onDelete?: () => void;
}

export function TableActions({ 
  itemId, 
  itemName, 
  activo = true,
  onView,
  onEdit,
  onToggle,
  onDelete
}: TableActionsProps) {
  const handleView = () => {
    if (onView) {
      onView();
    } else {
      console.log("Ver:", itemId, itemName);
      // TODO: Implementar modal de vista
    }
  };

  const handleEdit = () => {
    if (onEdit) {
      onEdit();
    } else {
      console.log("Editar:", itemId, itemName);
      // TODO: Implementar modal de edición
    }
  };

  const handleToggle = () => {
    if (onToggle) {
      onToggle();
    } else {
      console.log("Toggle status:", itemId, itemName, !activo);
      // TODO: Implementar cambio de estado
    }
  };

  const handleDelete = () => {
    if (onDelete) {
      onDelete();
    } else {
      console.log("Eliminar:", itemId, itemName);
      // TODO: Implementar confirmación y eliminación
    }
  };

  return (
    <div className="flex items-center gap-2">
      <button
        onClick={handleView}
        className="p-2 text-[#6A6A6A] hover:bg-[#6A6A6A]/10 rounded-lg transition"
        title="Ver"
        aria-label={`Ver ${itemName}`}
      >
        <Eye className="w-4 h-4" />
      </button>
      <button
        onClick={handleEdit}
        className="p-2 text-[#4B236A] hover:bg-[#4B236A]/10 rounded-lg transition"
        title="Editar"
        aria-label={`Editar ${itemName}`}
      >
        <Edit2 className="w-4 h-4" />
      </button>
      <button
        onClick={handleToggle}
        className="p-2 text-[#10B981] hover:bg-[#10B981]/10 rounded-lg transition"
        title={activo ? "Desactivar" : "Activar"}
        aria-label={`${activo ? "Desactivar" : "Activar"} ${itemName}`}
      >
        <Power className="w-4 h-4" />
      </button>
      <button
        onClick={handleDelete}
        className="p-2 text-red-600 hover:bg-red-600/10 rounded-lg transition"
        title="Eliminar"
        aria-label={`Eliminar ${itemName}`}
      >
        <Trash2 className="w-4 h-4" />
      </button>
    </div>
  );
}
