"use client";

import type { ProviderBranchCardViewModel } from "@/types/provider-branches";
import { BranchCard } from "./branch-card";

interface BranchesGridProps {
  branches: ProviderBranchCardViewModel[];
  onEditBranch?: (branch: ProviderBranchCardViewModel) => void;
  onDeleteBranch?: (branch: ProviderBranchCardViewModel) => void;
}

export function BranchesGrid({
  branches,
  onEditBranch,
  onDeleteBranch,
}: BranchesGridProps) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      {branches.map((branch) => (
        <BranchCard
          key={branch.id}
          branch={branch}
          onEdit={onEditBranch}
          onDelete={onDeleteBranch}
        />
      ))}
    </div>
  );
}
