export type TermsSection = {
  id: string;
  title: string;
  paragraphs: string[];
  bullets?: string[];
};

export const TERMS_AND_CONDITIONS_SECTIONS: TermsSection[] = [
  {
    id: "acceptance",
    title: "1. Aceptación de los términos",
    paragraphs: [
      "Al continuar con el registro como proveedor, aceptas los términos y condiciones de uso de la plataforma y el cumplimiento de la normatividad aplicable.",
    ],
  },
  {
    id: "requirements",
    title: "2. Requisitos del proveedor",
    paragraphs: [
      "Para operar en Sumass debes mantener información legal y comercial vigente.",
    ],
    bullets: [
      "Contar con permisos y licencias vigentes para operar.",
      "Proveer información veraz y actualizada del negocio.",
      "Mantener estándares de calidad y seguridad en los productos.",
    ],
  },
  {
    id: "responsibilities",
    title: "3. Responsabilidades y cumplimiento",
    paragraphs: [
      "Eres responsable de la calidad de tus productos, tiempos de atención y cumplimiento de políticas comerciales, operativas y legales dentro de Sumass.",
    ],
  },
  {
    id: "updates",
    title: "4. Modificaciones",
    paragraphs: [
      "Sumass podrá actualizar estos términos cuando sea necesario. Las actualizaciones relevantes serán notificadas para su revisión y aceptación.",
    ],
  },
];

export const TERMS_ACCEPTANCE_LABEL =
  "He leído y acepto los términos y condiciones detallados en este documento.";

export const TERMS_ACCEPTANCE_HELP_TEXT =
  "Debes aceptar esta condición para poder continuar con la habilitación de tu cuenta como proveedor.";
