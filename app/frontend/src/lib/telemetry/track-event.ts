export type TelemetryPayload = Record<string, unknown>;

declare global {
  interface Window {
    dataLayer?: unknown[];
  }
}

/**
 * MVP telemetry helper.
 * - In production, pushes events to dataLayer if available.
 * - In development, logs to console for debugging.
 */
export function trackEvent(event: string, payload: TelemetryPayload = {}): void {
  if (typeof window === "undefined") {
    return;
  }

  const eventData = {
    event,
    timestamp: new Date().toISOString(),
    ...payload,
  };

  if (Array.isArray(window.dataLayer)) {
    window.dataLayer.push(eventData);
  }

  if (process.env.NODE_ENV !== "production") {
    console.info("[telemetry]", eventData);
  }
}
