"use client";

import { useMemo } from "react";
import { ChevronLeft, ChevronRight, MoreHorizontal } from "lucide-react";

interface PaginationProps {
  currentPage: number;
  lastPage: number;
  onPageChange: (page: number) => void;
  className?: string;
}

const MAX_VISIBLE_PAGES = 5;

function buildPageRange(currentPage: number, lastPage: number): (number | "ellipsis")[] {
  if (lastPage <= MAX_VISIBLE_PAGES) {
    return Array.from({ length: lastPage }, (_, i) => i + 1);
  }

  const range: (number | "ellipsis")[] = [];
  const showLeftEllipsis = currentPage > 3;
  const showRightEllipsis = currentPage < lastPage - 2;

  range.push(1);

  if (showLeftEllipsis) {
    range.push("ellipsis");
  }

  const start = Math.max(2, currentPage - 1);
  const end = Math.min(lastPage - 1, currentPage + 1);

  for (let page = start; page <= end; page += 1) {
    range.push(page);
  }

  if (showRightEllipsis) {
    range.push("ellipsis");
  }

  range.push(lastPage);

  return range;
}

function PaginationButton({
  children,
  isActive,
  disabled,
  onClick,
  ariaLabel
}: {
  children: React.ReactNode;
  isActive?: boolean;
  disabled?: boolean;
  onClick?: () => void;
  ariaLabel?: string;
}) {
  return (
    <button
      type="button"
      onClick={onClick}
      disabled={disabled}
      aria-label={ariaLabel}
      aria-current={isActive ? "page" : undefined}
      className={
        "h-[36px] min-w-[36px] px-2 rounded-lg text-sm font-medium transition " +
        (isActive
          ? "bg-[#4B236A] text-white shadow"
          : "bg-white text-[#1A1A1A] border border-[#E0E0E0] hover:border-[#4B236A] hover:text-[#4B236A]") +
        (disabled ? " opacity-50 cursor-not-allowed" : "")
      }
    >
      {children}
    </button>
  );
}

export function Pagination({ currentPage, lastPage, onPageChange, className }: PaginationProps) {
  const pages = useMemo(() => buildPageRange(currentPage, lastPage), [currentPage, lastPage]);

  if (lastPage <= 1) return null;

  const canGoPrev = currentPage > 1;
  const canGoNext = currentPage < lastPage;

  return (
    <nav
      role="navigation"
      aria-label="Pagination"
      className={`flex items-center justify-center gap-2 ${className ?? ""}`}
    >
      <PaginationButton
        ariaLabel="Página anterior"
        disabled={!canGoPrev}
        onClick={() => onPageChange(currentPage - 1)}
      >
        <ChevronLeft className="w-4 h-4" />
      </PaginationButton>

      <div className="flex items-center gap-2">
        {pages.map((page, index) => {
          if (page === "ellipsis") {
            return (
              <span
                key={`ellipsis-${index}`}
                className="h-[36px] min-w-[36px] flex items-center justify-center text-[#6A6A6A]"
              >
                <MoreHorizontal className="w-4 h-4" />
              </span>
            );
          }

          return (
            <PaginationButton
              key={page}
              isActive={page === currentPage}
              onClick={() => onPageChange(page)}
            >
              {page}
            </PaginationButton>
          );
        })}
      </div>

      <PaginationButton
        ariaLabel="Página siguiente"
        disabled={!canGoNext}
        onClick={() => onPageChange(currentPage + 1)}
      >
        <ChevronRight className="w-4 h-4" />
      </PaginationButton>
    </nav>
  );
}
