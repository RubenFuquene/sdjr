type LoginPayload = {
  email: string;
  password: string;
};

export type LoginResult = {
  ok: true;
  redirectTo?: string;
};

const API_DELAY_MS = 650;

const wait = (ms: number) => new Promise((resolve) => setTimeout(resolve, ms));

export async function login({ email, password }: LoginPayload): Promise<LoginResult> {
  if (!email || !password) {
    throw new Error("Ingresa correo y contraseña.");
  }

  await wait(API_DELAY_MS);

  // -------------------------------------------------------------
  // Dummies de autenticación (quitar al conectar API real)
  //  - Caso exitoso: email "admin@sumass.com" + password "Admin123"
  //  - Caso fallido: email "blocked@sumass.com" -> error de cuenta bloqueada
  //  - Otros: error de credenciales inválidas
  // -------------------------------------------------------------
  if (email === "admin@sumass.com" && password === "Admin123") {
    return { ok: true, redirectTo: "/admin/dashboard" };
  }

  if (email === "blocked@sumass.com") {
    throw new Error("Tu cuenta está bloqueada (dummy). Usa otro usuario o espera backend real.");
  }

  throw new Error("Credenciales inválidas (dummy). Ajusta al endpoint real cuando esté listo.");

  // TODO: Reemplazar este bloque con el fetch real a Laravel, por ejemplo:
  // const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/login`, {
  //   method: "POST",
  //   headers: { "Content-Type": "application/json" },
  //   credentials: "include",
  //   body: JSON.stringify({ email, password }),
  // });
  // if (!response.ok) {
  //   const payload = await response.json().catch(() => null);
  //   throw new Error(payload?.message ?? "Credenciales inválidas");
  // }
  // const data = await response.json();
  // return { ok: true, redirectTo: data?.redirectTo ?? "/admin/dashboard" };
}
