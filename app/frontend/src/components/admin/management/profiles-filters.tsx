"use client";

import { Search } from "lucide-react";
import { Vista } from "@/types/admin";

interface ProfilesFiltersProps {
  vista: Vista;
  searchTerm: string;
  perfilFilter: string;
  perfiles: { id: number; nombre: string }[];
  onSearchChange: (value: string) => void;
  onPerfilChange: (value: string) => void;
  onSearch: () => void;
}

export function ProfilesFilters({
  vista,
  searchTerm,
  perfilFilter,
  perfiles,
  onSearchChange,
  onPerfilChange,
  onSearch,
}: ProfilesFiltersProps) {
  // Vista Perfiles solo tiene search simple
  if (vista === "perfiles") {
    return (
      <div className="bg-white rounded-[18px] shadow-sm p-6 border border-slate-100">
        <div className="flex gap-4">
          <div className="relative flex-1">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[#6A6A6A]" />
            <input
              type="text"
              placeholder="Buscar perfiles..."
              value={searchTerm}
              onChange={(e) => onSearchChange(e.target.value)}
              className="w-full h-[50px] pl-11 pr-4 border border-[#E0E0E0] rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] text-[#1A1A1A]"
            />
          </div>
          <button
            onClick={onSearch}
            className="px-6 h-[52px] bg-[#4B236A] text-white rounded-xl hover:bg-[#5D2B7D] transition shadow-lg flex items-center gap-2"
          >
            <Search className="w-4 h-4" />
            Buscar
          </button>
        </div>
      </div>
    );
  }

  // Otras vistas tienen filtros avanzados
  return (
    <div className="bg-white rounded-[18px] shadow-sm p-6 border border-slate-100">
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div className="relative md:col-span-2">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[#6A6A6A]" />
          <input
            type="text"
            placeholder="Buscar por nombre..."
            value={searchTerm}
            onChange={(e) => onSearchChange(e.target.value)}
            className="w-full h-[50px] pl-11 pr-4 border border-[#E0E0E0] rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] text-[#1A1A1A]"
          />
        </div>
        <select
          value={perfilFilter}
          onChange={(e) => onPerfilChange(e.target.value)}
          className="h-[50px] px-4 border border-[#E0E0E0] rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] text-[#1A1A1A]"
        >
          <option value="todos">Todos los perfiles</option>
          {perfiles.map((p) => (
            <option key={p.id} value={p.nombre}>
              {p.nombre}
            </option>
          ))}
        </select>
        <button
          onClick={onSearch}
          className="px-6 h-[52px] bg-[#4B236A] text-white rounded-xl hover:bg-[#5D2B7D] transition shadow-lg"
        >
          Buscar
        </button>
      </div>
    </div>
  );
}
