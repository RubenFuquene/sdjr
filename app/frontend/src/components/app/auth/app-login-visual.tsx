import Image from "next/image";
import Link from "next/link";
import { ArrowLeft } from "lucide-react";

export default function AppLoginVisual() {
  return (
    <div className="bg-white">
      <div className="flex items-center gap-3 border-b border-[#E6E6E6] px-4 py-4">
        <Link
          href="/"
          className="text-[#5A1E6B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#5A1E6B]"
          aria-label="Volver"
        >
          <ArrowLeft className="h-6 w-6" />
        </Link>
        <h1 className="text-xl text-[#2E2E2E]">Bienvenido</h1>
      </div>

      <div className="px-6 py-6 text-center">
        <div className="mx-auto mb-4 flex h-32 w-32 items-center justify-center rounded-full bg-white">
          <Image
            src="/brand/logo-sumass.png"
            alt="Sumass Logo"
            width={112}
            height={112}
            className="h-28 w-28 object-contain"
            priority
          />
        </div>
        <h2 className="mb-2 text-2xl text-[#5A1E6B]">Sumass</h2>
        <p className="text-[#7A2E9A]">Ahorra dinero y salva alimentos</p>
      </div>
    </div>
  );
}
