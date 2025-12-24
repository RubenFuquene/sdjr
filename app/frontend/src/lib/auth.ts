import { cookies } from "next/headers";
import { redirect } from "next/navigation";

export type Role = "admin" | "provider" | "app";

type Session = {
  userId: string;
  email: string;
  role: Role;
  name?: string;
  last_name?: string;
  token?: string;
};

const BYPASS_AUTH = process.env.NEXT_PUBLIC_BYPASS_AUTH === "true";

const LOGIN_PATH_BY_ROLE: Record<Role, string> = {
  admin: "/admin/login",
  provider: "/provider/login",
  app: "/app/login",
};

/**
 * Fetch user session - simplified version for current implementation
 */
async function fetchSession(): Promise<Session | null> {
  if (BYPASS_AUTH) {
    return { userId: "demo", email: "demo@sumass.com", role: "admin" };
  }

  try {
    // Next.js 14/React 19: cookies() es async
    const cookieStore = await cookies();
    
    // Por ahora, buscar cookies simples que podamos setear después del login
    const sessionData = cookieStore.get("sdjr_session")?.value;
    
    if (!sessionData) {
      return null;
    }
    
    // Intentar decodificar datos de sesión básicos
    try {
      const parsed = JSON.parse(decodeURIComponent(sessionData));
      return {
        userId: parsed.userId || "unknown",
        email: parsed.email || "",
        role: parsed.role || "admin",
        name: parsed.name,
        last_name: parsed.last_name
      };
    } catch {
      return null;
    }
  } catch (error) {
    console.error("Error fetching session:", error);
    return null;
  }
}

/**
 * Map Laravel role to frontend role
 */
function mapLaravelRoleToRole(laravelRole: string): Role {
  switch (laravelRole) {
    case "provider":
      return "provider";
    case "customer":
      return "app";
    case "admin":
    default:
      return "admin";
  }
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
