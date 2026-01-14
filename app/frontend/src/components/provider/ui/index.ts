// Provider UI Components - Desacoplados (No compartidos)
// Estos componentes son específicos del panel de provider
// Ver: src/components/provider/

export { Button, buttonVariants } from "./button";
export type { VariantProps } from "class-variance-authority";

export {
  Card,
  CardHeader,
  CardFooter,
  CardTitle,
  CardAction,
  CardDescription,
  CardContent,
} from "./card";

export { Input } from "./input";

export { Label } from "./label";

export { Tabs, TabsList, TabsTrigger, TabsContent } from "./tabs";

export { Separator } from "./separator";

export { cn } from "./utils";
