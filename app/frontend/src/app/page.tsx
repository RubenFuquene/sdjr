import Link from "next/link";

export default function Home() {
  return (
    <div className="relative min-h-screen overflow-hidden bg-[#DDE8BB] text-[#1A1A1A]">
      <div className="pointer-events-none absolute inset-0">
        <div className="absolute -top-24 right-[-10%] h-72 w-72 rounded-full bg-[#C8D86D] opacity-60 blur-3xl" />
        <div className="absolute bottom-[-10%] left-[-5%] h-80 w-80 rounded-full bg-[#4B236A] opacity-15 blur-3xl" />
        <div className="absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-white/80 to-transparent" />
      </div>

      <main className="relative mx-auto flex min-h-screen w-full max-w-6xl flex-col gap-12 px-6 pb-16 pt-10 sm:px-10 lg:px-14">
        <header className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="flex h-12 w-12 items-center justify-center rounded-[18px] bg-white shadow-sm">
              <span className="text-lg font-semibold text-[#4B236A]">S</span>
            </div>
            <div>
              <p className="text-sm font-medium text-[#6A6A6A]">Sumass</p>
              <p className="text-base font-semibold">Plataforma unificada</p>
            </div>
          </div>
          <div className="hidden items-center gap-3 text-sm text-[#6A6A6A] sm:flex">
            <span className="rounded-full border border-[#E0E0E0] bg-white/70 px-4 py-2">
              Acceso rapido
            </span>
          </div>
        </header>

        <section className="grid gap-10 lg:grid-cols-[1.1fr_1fr] lg:items-center">
          <div className="space-y-6">
            <p className="inline-flex w-fit items-center gap-2 rounded-full border border-[#E0E0E0] bg-white/80 px-4 py-2 text-sm text-[#6A6A6A]">
              Paneles internos
              <span className="h-2 w-2 rounded-full bg-[#4B236A]" />
            </p>
            <h1 className="text-4xl font-semibold leading-[1.1] tracking-tight sm:text-5xl">
              Centraliza tu operacion en un solo lugar.
            </h1>
            <p className="max-w-xl text-lg leading-8 text-[#6A6A6A]">
              Elige el modulo adecuado para administrar o abastecer. Esta landing es
              el punto de entrada para los equipos internos de Sumass.
            </p>
            <div className="flex flex-col gap-6 sm:flex-row sm:gap-4">
              <div className="flex flex-col gap-3">
                <Link
                  href="/admin"
                  className="hidden h-[52px] items-center justify-center rounded-xl bg-[#4B236A] px-6 text-white shadow-lg transition hover:bg-[#5D2B7D] hover:shadow-xl sm:flex"
                >
                  Ir a administracion
                </Link>
                <div className="flex flex-col gap-3 sm:hidden">
                  <p className="text-sm font-medium text-[#6A6A6A]">Administracion en movil</p>
                  <div className="grid grid-cols-2 gap-3">
                    <a
                      href="#"
                      className="flex h-[52px] items-center justify-center rounded-xl border border-[#E0E0E0] bg-white px-4 text-sm font-semibold text-[#1A1A1A]"
                      aria-label="App Store administracion"
                    >
                      App Store
                    </a>
                    <a
                      href="#"
                      className="flex h-[52px] items-center justify-center rounded-xl border border-[#E0E0E0] bg-white px-4 text-sm font-semibold text-[#1A1A1A]"
                      aria-label="Google Play administracion"
                    >
                      Google Play
                    </a>
                  </div>
                </div>
              </div>
              <div className="flex flex-col gap-3">
                <Link
                  href="/provider"
                  className="hidden h-[52px] items-center justify-center rounded-xl border border-[#E0E0E0] bg-white px-6 text-[#4B236A] transition hover:border-[#C8D86D] sm:flex"
                >
                  Ir a proveedor
                </Link>
                <div className="flex flex-col gap-3 sm:hidden">
                  <p className="text-sm font-medium text-[#6A6A6A]">Proveedor en movil</p>
                  <div className="grid grid-cols-2 gap-3">
                    <a
                      href="#"
                      className="flex h-[52px] items-center justify-center rounded-xl border border-[#E0E0E0] bg-white px-4 text-sm font-semibold text-[#1A1A1A]"
                      aria-label="App Store proveedor"
                    >
                      App Store
                    </a>
                    <a
                      href="#"
                      className="flex h-[52px] items-center justify-center rounded-xl border border-[#E0E0E0] bg-white px-4 text-sm font-semibold text-[#1A1A1A]"
                      aria-label="Google Play proveedor"
                    >
                      Google Play
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div className="grid gap-5 sm:grid-cols-2">
            <div className="rounded-[18px] border border-[#E0E0E0] bg-white p-6 shadow-sm">
              <div className="mb-5 flex items-center justify-between">
                <span className="rounded-full bg-[#F7F7F7] px-3 py-1 text-xs font-medium text-[#6A6A6A]">
                  Administracion
                </span>
                <span className="text-sm font-semibold text-[#4B236A]">Core</span>
              </div>
              <h2 className="mb-3 text-xl font-semibold">Panel administrativo</h2>
              <p className="text-sm leading-6 text-[#6A6A6A]">
                Gestiona usuarios, pedidos y reportes con visibilidad total.
              </p>
              <Link
                href="/admin/login"
                className="mt-6 flex h-[52px] items-center justify-center rounded-xl bg-[#4B236A] px-5 text-white shadow-lg transition hover:bg-[#5D2B7D] hover:shadow-xl"
              >
                Entrar al panel
              </Link>
            </div>

            <div className="rounded-[18px] border border-[#E0E0E0] bg-white p-6 shadow-sm">
              <div className="mb-5 flex items-center justify-between">
                <span className="rounded-full bg-[#F7F7F7] px-3 py-1 text-xs font-medium text-[#6A6A6A]">
                  Proveedor
                </span>
                <span className="text-sm font-semibold text-[#4B236A]">Ops</span>
              </div>
              <h2 className="mb-3 text-xl font-semibold">Panel de proveedores</h2>
              <p className="text-sm leading-6 text-[#6A6A6A]">
                Administra stock, publicaciones y retiros en tiempo real.
              </p>
              <Link
                href="/provider"
                className="mt-6 flex h-[52px] items-center justify-center rounded-xl bg-[#4B236A] px-5 text-white shadow-lg transition hover:bg-[#5D2B7D] hover:shadow-xl"
              >
                Entrar al panel
              </Link>
            </div>
          </div>
        </section>
      </main>
    </div>
  );
}
