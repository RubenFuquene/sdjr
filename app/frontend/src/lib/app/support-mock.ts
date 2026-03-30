export type SupportTicketStatus = "open" | "in-progress" | "resolved" | "closed";

export type SupportTicket = {
  id: number;
  title: string;
  category: string;
  createdAt: string;
  status: SupportTicketStatus;
  lastUpdate: string;
  orderId?: string;
};

export type SupportMessage = {
  id: number;
  sender: "user" | "support";
  content: string;
  timestamp: string;
};

export const SUPPORT_TICKETS: SupportTicket[] = [
  {
    id: 1001,
    title: "Problema con el pago",
    category: "Pago",
    createdAt: "19 nov 2025",
    status: "in-progress",
    lastUpdate: "Hace 2 horas",
    orderId: "#ORD-4521",
  },
  {
    id: 1002,
    title: "Pedido no recogido a tiempo",
    category: "Pedido",
    createdAt: "18 nov 2025",
    status: "open",
    lastUpdate: "Hace 1 dia",
    orderId: "#ORD-4489",
  },
  {
    id: 1003,
    title: "Error en la aplicacion",
    category: "Tecnico",
    createdAt: "15 nov 2025",
    status: "resolved",
    lastUpdate: "Hace 5 dias",
  },
  {
    id: 1004,
    title: "Consulta sobre promociones",
    category: "Otro",
    createdAt: "10 nov 2025",
    status: "closed",
    lastUpdate: "Hace 10 dias",
  },
];

export const SUPPORT_MESSAGES_BY_TICKET: Record<number, SupportMessage[]> = {
  1001: [
    {
      id: 1,
      sender: "user",
      content: "Hice un pago pero no se reflejo en mi pedido.",
      timestamp: "19 nov, 2:30 p.m.",
    },
    {
      id: 2,
      sender: "support",
      content: "Gracias por escribirnos. Estamos validando el pago con nuestro equipo.",
      timestamp: "19 nov, 3:15 p.m.",
    },
  ],
  1002: [
    {
      id: 1,
      sender: "user",
      content: "No alcance a recoger mi pedido y quiero saber si aplica reprogramacion.",
      timestamp: "18 nov, 10:40 a.m.",
    },
  ],
};

export function getTicketById(ticketId: number): SupportTicket | null {
  return SUPPORT_TICKETS.find((ticket) => ticket.id === ticketId) ?? null;
}

export function getMessagesByTicketId(ticketId: number): SupportMessage[] {
  return SUPPORT_MESSAGES_BY_TICKET[ticketId] ?? [];
}
