import Link from "next/link";

const navItems = [
  { href: "#quienes-somos", label: "Quienes somos" },
  { href: "#features", label: "Caracteristicas" },
  { href: "#paneles", label: "Paneles" },
  { href: "#comercios", label: "Comercios" },
];

export default function LandingHeader() {
  return (
    <header className="sticky top-6 z-20 flex flex-wrap items-center justify-between gap-4 rounded-[24px] border border-white/20 bg-white/10 px-6 py-4 text-white shadow-lg backdrop-blur-md">
      <div className="flex items-center gap-3">
        <div className="flex h-12 w-12 items-center justify-center rounded-[18px] bg-white shadow-md transition-shadow duration-300 hover:shadow-lg animate-bounce-soft">
          <span className="text-lg font-extrabold text-[#87ab69]">Ñ</span>
        </div>
        <div>
          <p className="text-sm font-medium text-white/90">Ñapa</p>
          <p className="text-base font-bold text-white">Economia circular</p>
        </div>
      </div>

      <nav
        className="hidden items-center gap-6 text-sm font-medium text-white/80 lg:flex"
        aria-label="Secciones de la landing"
      >
        {navItems.map((item) => (
          <a
            key={item.href}
            href={item.href}
            className="transition-colors duration-300 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/80"
          >
            {item.label}
          </a>
        ))}
      </nav>

      <div className="flex flex-wrap items-center gap-3 text-sm">
        <Link
          href="/admin/login"
          className="rounded-full border border-white/30 bg-white/10 px-4 py-2 backdrop-blur-sm transition-colors duration-300 hover:bg-white/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/80"
        >
          Acceso admin
        </Link>
        <Link
          href="/app"
          className="rounded-full bg-white px-4 py-2 font-semibold text-[#4b0082] transition-transform duration-300 hover:-translate-y-0.5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/80"
        >
          Quiero mi Ñapa
        </Link>
      </div>
    </header>
  );
}
