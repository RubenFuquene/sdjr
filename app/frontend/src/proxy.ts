import { NextResponse, type NextRequest } from "next/server";

const SESSION_COOKIE = "sdjr_session";
const BYPASS_AUTH = process.env.NEXT_PUBLIC_BYPASS_AUTH === "true";
const PUBLIC_ROUTES = ["/admin/login", "/provider/login", "/app/login"];
const LOGIN_BY_SEGMENT: Record<string, string> = {
  admin: "/admin/login",
  provider: "/provider/login",
  app: "/app/login",
};

// -------------------------------------------------------------
// Proxy guard (Next.js 16+ convention)
//  - BYPASS_AUTH=true => permite todo
//  - Sin cookie sdjr_session => redirige a login del segmento
//  - Con cookie => deja pasar (no valida rol aquí; rol se valida en layout protegido)
// -------------------------------------------------------------

export function proxy(req: NextRequest) {
  const { pathname, search } = req.nextUrl;

  if (BYPASS_AUTH) return NextResponse.next();
  if (PUBLIC_ROUTES.some((route) => pathname.startsWith(route))) return NextResponse.next();

  const hasSession = Boolean(req.cookies.get(SESSION_COOKIE)?.value);
  if (hasSession) return NextResponse.next();

  const segment = pathname.split("/").filter(Boolean)[0];
  const loginPath = LOGIN_BY_SEGMENT[segment] ?? "/";
  const url = req.nextUrl.clone();
  url.pathname = loginPath;
  if (pathname) {
    url.searchParams.set("redirectTo", pathname + (search ?? ""));
  }

  return NextResponse.redirect(url);
}

export const config = {
  matcher: ["/admin/:path*", "/provider/:path*", "/app/:path*"],
};
