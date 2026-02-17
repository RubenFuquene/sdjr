"use client";

import { Pagination } from "./pagination";

interface TablePaginationProps {
  currentPage: number;
  lastPage: number;
  perPage: number;
  total: number;
  onPageChange: (page: number) => void;
  className?: string;
}

export function TablePagination({
  currentPage,
  lastPage,
  perPage,
  total,
  onPageChange,
  className
}: TablePaginationProps) {
  if (total === 0) return null;

  const from = (currentPage - 1) * perPage + 1;
  const to = Math.min(currentPage * perPage, total);

  return (
    <div
      className={
        `flex flex-col gap-3 md:flex-row md:items-center md:justify-between ` +
        `border border-[#E0E0E0] rounded-[18px] bg-white p-4 ${className ?? ""}`
      }
    >
      <div className="text-sm text-[#6A6A6A]">
        Mostrando <span className="font-semibold text-[#1A1A1A]">{from}–{to}</span> de{" "}
        <span className="font-semibold text-[#1A1A1A]">{total}</span> resultados
      </div>

      <Pagination
        currentPage={currentPage}
        lastPage={lastPage}
        onPageChange={onPageChange}
      />
    </div>
  );
}
