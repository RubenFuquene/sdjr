"use client";

import Image from "next/image";
import { Clock, Edit, Image as ImageIcon, MapPin, Phone, Trash2 } from "lucide-react";
import type { ProviderBranchCardViewModel } from "@/types/provider-branches";

interface BranchCardProps {
  branch: ProviderBranchCardViewModel;
  onEdit?: (branch: ProviderBranchCardViewModel) => void;
  onDelete?: (branch: ProviderBranchCardViewModel) => void;
}

export function BranchCard({ branch, onEdit, onDelete }: BranchCardProps) {
  return (
    <article className="overflow-hidden rounded-[18px] border border-[#E0E0E0] bg-white shadow-sm hover:shadow-lg transition-shadow">
      <div className="h-40 bg-[#F7F7F7] relative">
        {branch.coverImageUrl ? (
          <Image
            src={branch.coverImageUrl}
            alt={branch.name}
            fill
            className="object-cover"
            sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
          />
        ) : (
          <div className="w-full h-full flex items-center justify-center">
            <ImageIcon className="w-12 h-12 text-[#6A6A6A]" />
          </div>
        )}

        <div className="absolute top-2 right-2 rounded-full bg-white/90 backdrop-blur-sm px-3 py-1 text-sm text-[#1A1A1A]">
          {branch.photosCount} fotos
        </div>
      </div>

      <div className="p-5 space-y-3">
        <h3 className="text-[#1A1A1A] font-semibold text-lg">{branch.name}</h3>

        <div className="space-y-2 text-sm text-[#6A6A6A]">
          <div className="flex items-start gap-2">
            <MapPin className="w-4 h-4 mt-0.5 text-[#4B236A]" />
            <p className="line-clamp-2">{branch.fullAddress}</p>
          </div>

          <div className="flex items-start gap-2">
            <Clock className="w-4 h-4 mt-0.5 text-[#4B236A]" />
            <p className="line-clamp-2">{branch.scheduleText}</p>
          </div>

          <div className="flex items-center gap-2">
            <Phone className="w-4 h-4 text-[#4B236A]" />
            <p>{branch.phone}</p>
          </div>
        </div>

        <div className="flex gap-2 pt-1">
          <button
            type="button"
            onClick={() => onEdit?.(branch)}
            className="flex-1 h-[42px] rounded-[14px] border border-[#E0E0E0] text-[#1A1A1A] hover:bg-[#F7F7F7] transition-colors inline-flex items-center justify-center gap-2"
          >
            <Edit className="w-4 h-4" />
            Editar
          </button>
          <button
            type="button"
            onClick={() => onDelete?.(branch)}
            className="h-[42px] w-[42px] rounded-[14px] border border-[#E0E0E0] text-red-600 hover:bg-red-50 transition-colors inline-flex items-center justify-center"
            aria-label={`Eliminar sucursal ${branch.name}`}
          >
            <Trash2 className="w-4 h-4" />
          </button>
        </div>
      </div>
    </article>
  );
}
