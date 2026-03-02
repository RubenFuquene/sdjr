export default function ProviderBranchesLoading() {
  return (
    <div className="p-6 md:p-8">
      <div className="mb-6">
        <div className="h-9 w-44 animate-pulse rounded-md bg-gray-200" />
        <div className="mt-2 h-5 w-72 animate-pulse rounded-md bg-gray-100" />
      </div>

      <div className="rounded-[18px] border border-[#E0E0E0] bg-white p-6 md:p-8 shadow-sm">
        <div className="space-y-4">
          <div className="h-6 w-1/3 animate-pulse rounded-md bg-gray-100" />
          <div className="h-4 w-2/3 animate-pulse rounded-md bg-gray-100" />
          <div className="h-4 w-1/2 animate-pulse rounded-md bg-gray-100" />
          <div className="pt-2">
            <div className="h-[52px] w-56 animate-pulse rounded-[14px] bg-[#DDE8BB]" />
          </div>
        </div>
      </div>
    </div>
  );
}
