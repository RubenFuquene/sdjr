import { redirect } from "next/navigation";
import { getSession } from "@/lib/auth";

export const dynamic = "force-dynamic";

export default async function AppIndexPage() {
  const session = await getSession();

  if (!session || session.role !== "app") {
    redirect("/app/login");
  }

  redirect("/app/dashboard");
}
