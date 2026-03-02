"use client";

import { BranchesEmptyState } from './branches-empty-state';
import type { CommerceBranchFromAPI } from '@/lib/api/branches';
import type { ProviderBranchCardViewModel } from '@/types/provider-branches';
import { commerceBranchesToCardViewModels } from '@/lib/provider/adapters/branches';
import { BranchesGrid } from './branches-grid';

interface BranchesPageContentProps {
  branches: CommerceBranchFromAPI[];
  loading: boolean;
  error: string | null;
  hasCommerce: boolean;
  totalBranches: number;
  currentPage: number;
  lastPage: number;
  onPageChange: (page: number) => Promise<void>;
  onAddBranch: () => void;
  onEditBranch: (branch: ProviderBranchCardViewModel) => void;
  onDeleteBranch: (branch: ProviderBranchCardViewModel) => void;
}

export function BranchesPageContent({
  branches,
  loading,
  error,
  hasCommerce,
  totalBranches,
  currentPage,
  lastPage,
  onPageChange,
  onAddBranch,
  onEditBranch,
  onDeleteBranch,
}: BranchesPageContentProps) {
  const branchCards = commerceBranchesToCardViewModels(branches);

  if (loading) {
    return (
      <div className="bg-white rounded-[18px] shadow-sm p-6 md:p-8">
        <p className="text-gray-600">Cargando sucursales...</p>
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-white rounded-[18px] shadow-sm p-6 md:p-8 border border-red-200">
        <p className="text-red-600">{error}</p>
      </div>
    );
  }

  if (!hasCommerce || totalBranches === 0) {
    return <BranchesEmptyState onAddBranch={onAddBranch} />;
  }

  return (
    <div className="bg-white rounded-[18px] shadow-sm p-6 md:p-8 border border-[#E0E0E0]">

      <BranchesGrid
        branches={branchCards}
        onEditBranch={onEditBranch}
        onDeleteBranch={onDeleteBranch}
      />

      {lastPage > 1 && (
        <div className="mt-6 flex items-center justify-between border-t border-[#E0E0E0] pt-4">
          <button
            type="button"
            onClick={() => onPageChange(currentPage - 1)}
            disabled={currentPage === 1 || loading}
            className="h-[42px] px-4 rounded-[14px] border border-[#E0E0E0] text-[#1A1A1A] hover:bg-[#F7F7F7] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Anterior
          </button>

          <p className="text-sm text-[#6A6A6A]">
            Página <span className="font-medium text-[#1A1A1A]">{currentPage}</span> de{" "}
            <span className="font-medium text-[#1A1A1A]">{lastPage}</span>
          </p>

          <button
            type="button"
            onClick={() => onPageChange(currentPage + 1)}
            disabled={currentPage >= lastPage || loading}
            className="h-[42px] px-4 rounded-[14px] border border-[#E0E0E0] text-[#1A1A1A] hover:bg-[#F7F7F7] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Siguiente
          </button>
        </div>
      )}
    </div>
  );
}
