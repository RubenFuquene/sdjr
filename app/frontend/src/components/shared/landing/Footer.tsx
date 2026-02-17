import Link from "next/link";

export default function LandingFooter() {
  return (
    <footer className="rounded-[28px] border border-white/20 bg-white/10 p-8 text-white shadow-xl">
      <div className="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">
        <div className="space-y-4">
          <div className="flex items-center gap-3">
            <div className="flex h-11 w-11 items-center justify-center rounded-[16px] bg-white text-lg font-extrabold text-[#87ab69]">
              Ñ
            </div>
            <div>
              <p className="text-sm font-medium text-white/90">Ñapa</p>
              <p className="text-base font-bold text-white">Economia circular</p>
            </div>
          </div>
          <p className="max-w-md text-sm text-white/80">
            Conectamos excedentes con comunidades. Cada pedido ayuda a reducir el desperdicio de alimentos y fortalecer el impacto social en Colombia.
          </p>
        </div>

        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div className="space-y-3">
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-white/70">
              Plataforma
            </p>
            <div className="flex flex-col gap-2 text-sm text-white/80">
              <Link
                href="/app"
                className="hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/80"
              >
                Descarga la app
              </Link>
              <Link
                href="/provider"
                className="hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/80"
              >
                Soy un comercio
              </Link>
              <Link
                href="/admin/login"
                className="hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/80"
              >
                Acceso admin
              </Link>
            </div>
          </div>

          <div className="space-y-3">
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-white/70">
              Impacto
            </p>
            <div className="flex flex-col gap-2 text-sm text-white/80">
              <a
                href="#features"
                className="hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/80"
              >
                Caracteristicas
              </a>
              <a
                href="#quienes-somos"
                className="hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/80"
              >
                Mision y vision
              </a>
              <a
                href="#comercios"
                className="hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/80"
              >
                Alianzas
              </a>
            </div>
          </div>

          <div className="space-y-3">
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-white/70">
              Contacto
            </p>
            <div className="flex flex-col gap-2 text-sm text-white/80">
              <a
                href="mailto:hola@napa.co"
                className="hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/80"
              >
                hola@napa.co
              </a>
              <a
                href="tel:+573001112233"
                className="hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/80"
              >
                +57 300 111 2233
              </a>
            </div>
          </div>
        </div>
      </div>

      <div className="mt-8 flex flex-col gap-3 border-t border-white/10 pt-6 text-xs text-white/70 sm:flex-row sm:items-center sm:justify-between">
        <p>© 2026 Ñapa. Todos los derechos reservados.</p>
        <div className="flex flex-wrap gap-4">
          <a
            href="#"
            className="hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/80"
          >
            Terminos
          </a>
          <a
            href="#"
            className="hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/80"
          >
            Privacidad
          </a>
          <a
            href="#"
            className="hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/80"
          >
            Soporte
          </a>
        </div>
      </div>
    </footer>
  );
}
