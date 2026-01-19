import Image from "next/image";
import { Store, ArrowRight } from "lucide-react";
import { Card, CardContent } from "@/components/provider/ui/card";

export function AuthBranding() {
  return (
    <div className="hidden lg:block">
      <div className="space-y-6">
        {/* Logo + Branding */}
        <div className="flex items-center gap-4">
          <div className="flex h-20 w-20 items-center justify-center rounded-[18px] bg-white p-3 shadow-lg">
            <Image
              src="/brand/provider/sumass-logo.png"
              alt="Sumass Logo"
              width={80}
              height={80}
              className="h-full w-full object-contain"
            />
          </div>
          <div>
            <h1 className="text-3xl font-bold text-[#4B236A]">Sumass</h1>
            <p className="text-[#4B236A]/70">Tu Sumass al planeta</p>
          </div>
        </div>

        {/* Descripción principal */}
        <div className="mt-12 space-y-4">
          <h2 className="text-2xl font-semibold text-[#4B236A]">
            Gestiona tu negocio de forma sostenible
          </h2>
          <p className="text-lg text-[#6A6A6A]">
            Únete a la plataforma que conecta proveedores responsables con
            clientes conscientes. Registra tu negocio, administra tus productos
            y suma al planeta.
          </p>
        </div>

        {/* Feature Cards */}
        <div className="grid grid-cols-2 gap-4">
          {/* Card 1: Fácil Gestión */}
          <Card className="border border-[#DDE8BB]/30 bg-white shadow-sm">
            <CardContent className="pt-6">
              <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-[14px] bg-[#DDE8BB]">
                <Store className="h-6 w-6 text-[#4B236A]" />
              </div>
              <h3 className="mb-2 font-semibold text-[#4B236A]">
                Fácil Gestión
              </h3>
              <p className="text-sm text-[#6A6A6A]">
                Administra productos y sucursales desde un solo lugar
              </p>
            </CardContent>
          </Card>

          {/* Card 2: Impacto Positivo */}
          <Card className="border border-[#DDE8BB]/30 bg-white shadow-sm">
            <CardContent className="pt-6">
              <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-[14px] bg-[#DDE8BB]">
                <ArrowRight className="h-6 w-6 text-[#4B236A]" />
              </div>
              <h3 className="mb-2 font-semibold text-[#4B236A]">
                Impacto Positivo
              </h3>
              <p className="text-sm text-[#6A6A6A]">
                Conecta con clientes que valoran la sostenibilidad
              </p>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
