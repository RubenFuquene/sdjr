export default function AdminLoading() {
  return (
    <div className="flex h-screen bg-gray-50">
      {/* Sidebar Skeleton - Figma colors */}
      <aside className="w-64 bg-[#DDE8BB] shadow-xl animate-pulse border-r border-[#C8D86D]">
        {/* Logo area */}
        <div className="p-6 border-b border-[#C8D86D]">
          <div className="flex items-center gap-3 mb-2">
            <div className="w-12 h-12 bg-white rounded-xl" />
            <div className="flex-1">
              <div className="h-4 w-20 bg-[#C8D86D]/50 rounded mb-1" />
              <div className="h-3 w-28 bg-[#C8D86D]/40 rounded" />
            </div>
          </div>
        </div>
        
        {/* Menu items skeleton */}
        <nav className="p-4 space-y-2">
          {[1, 2, 3, 4, 5, 6].map((item) => (
            <div
              key={item}
              className="flex items-center gap-3 px-4 py-3 rounded-xl bg-[#C8D86D]/30"
            >
              <div className="w-5 h-5 bg-[#C8D86D]/50 rounded" />
              <div className="h-3 w-32 bg-[#C8D86D]/50 rounded" />
            </div>
          ))}
        </nav>
      </aside>

      {/* Main Content Area */}
      <div className="flex-1 flex flex-col">
        {/* Header Skeleton - Figma colors */}
        <header className="bg-[#DDE8BB] border-b border-[#C8D86D] shadow-sm animate-pulse">
          <div className="flex items-center justify-between px-6 py-4">
            <div>
              <div className="h-5 w-56 bg-[#C8D86D]/50 rounded mb-2" />
              <div className="h-4 w-64 bg-[#C8D86D]/40 rounded" />
            </div>
            
            <div className="flex items-center gap-4">
              <div className="w-10 h-10 bg-[#C8D86D]/40 rounded-xl" />
              <div className="w-36 h-12 bg-white/50 rounded-xl border border-[#C8D86D]" />
              <div className="w-10 h-10 bg-[#C8D86D]/40 rounded-xl" />
            </div>
          </div>
        </header>

        {/* Content Area Skeleton */}
        <main className="flex-1 p-6 overflow-auto animate-pulse bg-white">
          {/* Page title */}
          <div className="mb-6">
            <div className="h-7 w-56 bg-gray-300 rounded mb-2" />
            <div className="h-4 w-80 bg-gray-200 rounded" />
          </div>

          {/* Content cards */}
          <div className="grid grid-cols-1 gap-6">
            {[1, 2, 3].map((card) => (
              <div key={card} className="bg-gray-50 rounded-[18px] p-6 border border-[#E0E0E0]">
                <div className="h-5 w-40 bg-gray-300 rounded mb-4" />
                <div className="space-y-3">
                  <div className="h-4 w-full bg-gray-200 rounded" />
                  <div className="h-4 w-3/4 bg-gray-200 rounded" />
                  <div className="h-4 w-5/6 bg-gray-200 rounded" />
                </div>
              </div>
            ))}
          </div>
        </main>
      </div>
    </div>
  );
}
