import Link from "next/link";
import LandingFooter from "@/components/shared/landing/Footer";
import LandingHeader from "@/components/shared/landing/Header";

export default function Home() {
  return (
    <div
      className="relative min-h-screen overflow-hidden text-[#1a1a1a]"
      style={{ fontFamily: "var(--font-outfit)" }}
    >
      <div className="absolute inset-0 bg-gradient-to-br from-[#d9e6b3] via-[#87ab69] to-[#4b0082]" />
      <div className="pointer-events-none absolute inset-0">
        <div className="absolute -top-28 right-[-8%] h-96 w-96 rounded-full bg-white/30 blur-3xl animate-bounce-soft" />
        <div className="absolute bottom-[-20%] left-[-10%] h-80 w-80 rounded-full bg-white/15 blur-3xl" />
        <div className="absolute inset-x-0 top-0 h-28 bg-gradient-to-b from-white/30 to-transparent" />
      </div>

      <main className="relative mx-auto flex min-h-screen w-full max-w-6xl flex-col gap-16 px-6 pb-20 pt-10 sm:px-10 lg:px-14">
        <LandingHeader />

        <section
          id="hero"
          className="flex scroll-mt-32 flex-col gap-12 lg:flex-row lg:items-center lg:gap-16"
        >
          <div className="space-y-6 lg:w-3/5">
            <p className="inline-flex w-fit items-center gap-2 rounded-full border border-white/30 bg-white/15 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
              Plataforma TooGoodToGo para Latam
              <span className="h-2 w-2 rounded-full bg-white" />
            </p>
            <h1 className="text-5xl font-extrabold leading-[1.05] tracking-tight text-white sm:text-6xl lg:text-7xl xl:text-8xl">
              Reduce desperdicios y crea impacto real
            </h1>
            <p className="max-w-xl text-lg leading-8 text-white/90 sm:text-xl">
              Ñapa conecta negocios con ciudadanos conscientes. Rescata alimentos a precio justo y acelera la economia circular.
            </p>

            <div className="flex flex-col gap-4 pt-4 sm:flex-row">
              <Link
                href="/app"
                className="flex h-16 w-full items-center justify-center rounded-xl bg-[#87ab69] px-8 text-base font-semibold text-white shadow-lg transition-all duration-300 hover:bg-[#74954a] hover:shadow-xl hover:scale-105 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/80 sm:w-auto"
              >
                Quiero mi Ñapa
              </Link>
              <Link
                href="/provider"
                className="flex h-16 w-full items-center justify-center rounded-xl border-2 border-white/80 bg-white/10 px-8 text-base font-semibold text-white backdrop-blur-sm transition-all duration-300 hover:bg-white/20 hover:scale-105 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/80 sm:w-auto"
              >
                Soy un comercio
              </Link>
            </div>

            <div className="grid grid-cols-1 gap-4 pt-6 sm:grid-cols-3">
              <div className="rounded-lg border border-white/20 bg-white/10 p-4 backdrop-blur-sm">
                <p className="text-2xl font-bold text-white">50K+</p>
                <p className="text-sm text-white/80">Alimentos salvados</p>
              </div>
              <div className="rounded-lg border border-white/20 bg-white/10 p-4 backdrop-blur-sm">
                <p className="text-2xl font-bold text-white">1500+</p>
                <p className="text-sm text-white/80">Negocios activos</p>
              </div>
              <div className="rounded-lg border border-white/20 bg-white/10 p-4 backdrop-blur-sm">
                <p className="text-2xl font-bold text-white">2M+</p>
                <p className="text-sm text-white/80">Impacto social</p>
              </div>
            </div>
          </div>

          <div className="relative lg:w-2/5">
            <div className="relative rounded-[28px] border border-white/25 bg-white/15 p-6 shadow-2xl backdrop-blur-md">
              <div className="relative aspect-[4/5] overflow-hidden rounded-[22px] bg-gradient-to-br from-[#d9e6b3] to-[#87ab69] shadow-xl">
                <div className="absolute inset-0 rotate-[-6deg] scale-110 bg-white/15" />
                <div className="absolute inset-0 flex items-center justify-center">
                  <div className="rounded-2xl bg-white/25 px-6 py-8 text-center shadow-lg backdrop-blur-sm">
                    <p className="text-sm font-semibold uppercase tracking-wide text-white/80">Rescate diario</p>
                    <p className="mt-2 text-3xl font-extrabold text-white">+3.2K</p>
                    <p className="text-sm text-white/80">packs vendidos</p>
                  </div>
                </div>
              </div>

              <div className="mt-6 grid grid-cols-2 gap-3">
                <div className="rounded-lg border border-white/20 bg-white/10 p-3 text-center">
                  <p className="text-xs font-semibold uppercase tracking-wide text-white/90">Sustentable</p>
                </div>
                <div className="rounded-lg border border-white/20 bg-white/10 p-3 text-center">
                  <p className="text-xs font-semibold uppercase tracking-wide text-white/90">Asequible</p>
                </div>
                <div className="rounded-lg border border-white/20 bg-white/10 p-3 text-center">
                  <p className="text-xs font-semibold uppercase tracking-wide text-white/90">Impacto</p>
                </div>
                <div className="rounded-lg border border-white/20 bg-white/10 p-3 text-center">
                  <p className="text-xs font-semibold uppercase tracking-wide text-white/90">Rapido</p>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section
          id="quienes-somos"
          className="grid scroll-mt-32 gap-10 rounded-[28px] border border-white/20 bg-white/10 p-8 backdrop-blur-md lg:grid-cols-2 lg:items-center"
        >
          <div className="space-y-5">
            <p className="text-sm font-semibold uppercase tracking-[0.2em] text-white/80">
              Quienes somos
            </p>
            <h2 className="text-3xl font-bold text-white sm:text-4xl">
              Unimos comercios y ciudadanos para salvar alimentos en Colombia.
            </h2>
            <p className="text-lg leading-7 text-white/85">
              En Ñapa impulsamos la economia circular con tecnologia, datos y una red de aliados que transforma excedentes en oportunidades. Cada compra ayuda a reducir desperdicios y fortalece comunidades locales.
            </p>
            <div className="flex flex-wrap gap-3">
              <span className="rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white/80">
                Sostenibilidad real
              </span>
              <span className="rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white/80">
                Impacto medible
              </span>
              <span className="rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white/80">
                Comunidad activa
              </span>
            </div>
          </div>
          <div className="relative">
            <div className="rounded-[24px] border border-white/20 bg-gradient-to-br from-[#d9e6b3] to-[#87ab69] p-6 shadow-xl">
              <div className="flex items-center justify-between rounded-[20px] bg-white/20 p-5 backdrop-blur-sm">
                <div>
                  <p className="text-sm font-semibold uppercase tracking-wide text-white/80">
                    Rescate semanal
                  </p>
                  <p className="text-3xl font-extrabold text-white">18.4K</p>
                  <p className="text-sm text-white/80">packs recuperados</p>
                </div>
                <div className="rounded-full bg-white/30 px-4 py-2 text-sm font-semibold text-white">
                  +23%
                </div>
              </div>
              <div className="mt-5 grid gap-3">
                <div className="flex items-center justify-between rounded-lg border border-white/20 bg-white/10 px-4 py-3 text-sm text-white/85">
                  <span>Comercios activos</span>
                  <span className="font-semibold">1,500+</span>
                </div>
                <div className="flex items-center justify-between rounded-lg border border-white/20 bg-white/10 px-4 py-3 text-sm text-white/85">
                  <span>Usuarios recurrentes</span>
                  <span className="font-semibold">36K</span>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section id="features" className="space-y-10 scroll-mt-32">
          <div className="flex flex-col gap-3 text-white">
            <p className="text-sm font-semibold uppercase tracking-[0.2em] text-white/80">
              Caracteristicas
            </p>
            <h2 className="text-3xl font-bold sm:text-4xl">
              Todo lo que necesitas para activar tu impacto
            </h2>
            <p className="max-w-2xl text-lg text-white/85">
              Desde la gestion de excedentes hasta la experiencia de compra, Ñapa acompana el ciclo completo con herramientas simples y datos accionables.
            </p>
          </div>
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            {[
              {
                title: "Publicaciones rapidas",
                desc: "Crea lotes en minutos con alertas y stock en tiempo real.",
              },
              {
                title: "Visibilidad local",
                desc: "Llega a clientes cercanos con mapas y recomendaciones.",
              },
              {
                title: "Pagos seguros",
                desc: "Cobros integrados y conciliacion automatica por retiro.",
              },
              {
                title: "Reportes de impacto",
                desc: "Mide alimentos salvados y beneficios economicos.",
              },
            ].map((item) => (
              <div
                key={item.title}
                className="rounded-[20px] border border-white/20 bg-white/10 p-6 text-white shadow-lg transition-transform duration-300 hover:-translate-y-2"
              >
                <div className="mb-4 h-10 w-10 rounded-full bg-white/20" />
                <h3 className="text-lg font-semibold">{item.title}</h3>
                <p className="mt-2 text-sm leading-6 text-white/80">{item.desc}</p>
              </div>
            ))}
          </div>
        </section>

        <section id="paneles" className="grid scroll-mt-32 gap-6 md:grid-cols-2">
          <div className="rounded-[24px] border border-white/20 bg-white/10 p-7 text-white shadow-xl">
            <div className="mb-6 flex items-center justify-between">
              <span className="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide">
                Admin
              </span>
              <span className="text-sm font-semibold">Core</span>
            </div>
            <h3 className="text-2xl font-semibold">Panel administrativo</h3>
            <p className="mt-3 text-sm leading-6 text-white/80">
              Supervisa operaciones, reportes de impacto y control de usuarios desde un dashboard unificado.
            </p>
            <Link
              href="/admin/login"
              className="mt-6 inline-flex h-14 items-center justify-center rounded-xl bg-white text-[#4b0082] px-6 text-sm font-semibold shadow-md transition-transform duration-300 hover:-translate-y-1"
            >
              Entrar al panel
            </Link>
          </div>

          <div className="rounded-[24px] border border-white/20 bg-white/10 p-7 text-white shadow-xl">
            <div className="mb-6 flex items-center justify-between">
              <span className="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide">
                Proveedores
              </span>
              <span className="text-sm font-semibold">Ops</span>
            </div>
            <h3 className="text-2xl font-semibold">Panel de comercios</h3>
            <p className="mt-3 text-sm leading-6 text-white/80">
              Administra inventario, publicaciones y retiros desde cualquier dispositivo.
            </p>
            <Link
              href="/provider"
              className="mt-6 inline-flex h-14 items-center justify-center rounded-xl border border-white/70 px-6 text-sm font-semibold text-white transition-transform duration-300 hover:-translate-y-1 hover:bg-white/10"
            >
              Entrar al panel
            </Link>
          </div>
        </section>

        <section
          id="comercios"
          className="scroll-mt-32 rounded-[28px] border border-white/20 bg-white/10 p-8 text-white shadow-xl"
        >
          <div className="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div className="space-y-3">
              <p className="text-sm font-semibold uppercase tracking-[0.2em] text-white/80">
                Para comercios
              </p>
              <h2 className="text-3xl font-bold sm:text-4xl">
                Conviertete en aliado Ñapa
              </h2>
              <p className="max-w-2xl text-lg text-white/85">
                Publica excedentes, mejora tu rentabilidad y participa en un movimiento sostenible que beneficia a tu comunidad.
              </p>
            </div>
            <div className="flex flex-col gap-4 sm:flex-row">
              <Link
                href="/provider"
                className="flex h-16 w-full items-center justify-center rounded-xl bg-[#87ab69] px-8 text-base font-semibold text-white shadow-lg transition-all duration-300 hover:bg-[#74954a] hover:shadow-xl focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/80 sm:w-auto"
              >
                Quiero sumarme
              </Link>
              <Link
                href="/admin"
                className="flex h-16 w-full items-center justify-center rounded-xl border border-white/70 px-8 text-base font-semibold text-white transition-all duration-300 hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/80 sm:w-auto"
              >
                Hablar con el equipo
              </Link>
            </div>
          </div>
        </section>

        <LandingFooter />
      </main>
    </div>
  );
}
