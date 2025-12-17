import { cookies } from "next/headers";
import { redirect } from "next/navigation";

export type Role = "admin" | "provider" | "app";

type Session = {
  userId: string;
  email: string;
  role: Role;
};

const SESSION_COOKIE = "sdjr_session";
const ROLE_COOKIE = "sdjr_role";
const BYPASS_AUTH = process.env.NEXT_PUBLIC_BYPASS_AUTH === "true";

const LOGIN_PATH_BY_ROLE: Record<Role, string> = {
  admin: "/admin/login",
  provider: "/provider/login",
  app: "/app/login",
};

// -------------------------------------------------------------
// Dummies de sesión/rol (quitar al conectar backend real)
//  - BYPASS_AUTH=true => siempre retorna sesión admin de prueba
//  - Cookie sdjr_session presente => sesión válida con rol de cookie
//  - Sin cookie => null (redirige a login)
// -------------------------------------------------------------

/**
 * Dev-only stub. Replace with a real call to `/api/me` when backend is ready.
 * Expected shape: { userId, email, role }
 */
async function fetchSession(): Promise<Session | null> {
  if (BYPASS_AUTH) {
    return { userId: "demo", email: "demo@sumass.com", role: "admin" };
  }

  const hasSession = cookies().has(SESSION_COOKIE);
  if (!hasSession) return null;

  // TODO: Reemplazar con decodificación real de token o fetch a `/api/me`.
  const role = (cookies().get(ROLE_COOKIE)?.value as Role | undefined) ?? "admin";
  const email = cookies().get("sdjr_email")?.value ?? "user@sumass.com";
  return { userId: "unknown", email, role };
}

export async function getSession(): Promise<Session | null> {
  return fetchSession();
}

export async function getSessionOrRedirect(requiredRole: Role, redirectTo?: string): Promise<Session> {
  const session = await fetchSession();
  if (!session) {
    return redirect(buildLoginUrl(requiredRole, redirectTo));
  }

  if (session.role !== requiredRole) {
    // Dummy: si roles no coinciden, redirige a home. Ajustar a página de 403 si se requiere.
    return redirect("/");
  }

  return session;
}

function buildLoginUrl(role: Role, redirectTo?: string) {
  const loginPath = LOGIN_PATH_BY_ROLE[role];
  if (!redirectTo) return loginPath;

  const url = new URL(loginPath, "http://localhost");
  url.searchParams.set("redirectTo", redirectTo);
  return `${url.pathname}?${url.searchParams.toString()}`;
}
