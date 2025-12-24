type LoginPayload = {
  email: string;
  password: string;
};

export type LoginResult = {
  ok: true;
  redirectTo?: string;
  user?: {
    id: string;
    email: string;
    rol: string;
    name?: string;
    last_name?: string;
  };
  token?: string;
};


export async function login({ email, password }: LoginPayload): Promise<LoginResult> {
  if (!email || !password) {
    throw new Error("Ingresa correo y contraseña.");
  }

  // Conectar con el endpoint real de Laravel
  try {
    const response = await fetch("http://localhost:8000/api/v1/login", {
      method: "POST",
      headers: { 
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      body: JSON.stringify({ email, password }),
    });

    if (!response.ok) {
      const errorData = await response.json().catch(() => null);
      
      // Manejar errores específicos de la API
      if (response.status === 422) {
        // Error de validación
        throw new Error(errorData?.message || "Credenciales inválidas");
      }
      
      throw new Error(errorData?.message || "Error del servidor");
    }

    const data = await response.json();
    
    // Verificar que la respuesta tenga el formato esperado
    if (data?.message === "Login successful" && data?.data) {
      const user = data.data;
      
      // Determinar rol del usuario (por ahora asumir admin si no hay roles)
      const userRole = user.roles && user.roles.length > 0 ? user.roles[0] : "admin";
      
      // Determinar redirección según el rol del usuario
      let redirectTo = "/admin/dashboard";
      if (userRole === "provider") {
        redirectTo = "/provider/dashboard";
      } else if (userRole === "customer") {
        redirectTo = "/app/dashboard";
      }
      
      return { 
        ok: true, 
        redirectTo,
        user: {
          id: user.id?.toString() || "unknown",
          email: user.email || "",
          rol: userRole,
          name: user.name,
          last_name: user.last_name
        },
        token: data.token
      };
    }
    
    throw new Error("Formato de respuesta inesperado");
  } catch (error) {
    // Re-lanzar errores conocidos
    if (error instanceof Error) {
      throw error;
    }
    
    // Error genérico de red u otros
    throw new Error("No se pudo conectar con el servidor");
  }
}
