import L from "leaflet";

/**
 * Constantes visuales para componentes de mapa
 * Colores, tamaños, estilos según paleta de Sumass
 */

// Paleta de colores basada en Figma design reference
export const COLORS = {
  // Usuario
  userGps: "#2563eb", // Azul para GPS (preciso)
  userIp: "#eab308", // Amarillo para IP fallback
  
  // Comercios (para futuro)
  commerce: "#dc2626", // Rojo para comercios
  commerceSelected: "#ff6b6b", // Rojo más claro para seleccionado
  
  // UI
  border: "#e0e0e0",
  background: "#ffffff",
  text: "#1a1a1a",
  mutedText: "#6a6a6a",
};

/**
 * Tamaños de elementos del mapa
 */
export const SIZES = {
  // Marcadores
  markerWidth: 32,
  markerHeight: 40,
  markerAnchorX: 16, // Centro horizontal
  markerAnchorY: 40, // Bottom (donde está el punto)
  
  // Popup
  popupMaxWidth: 250,
  popupMinWidth: 200,
  
  // Iconos dentro de marcadores
  iconSize: 24,
};

/**
 * Crea icono personalizado de SVG para marcador de usuario (GPS)
 * Retorna icono compatible con Leaflet
 */
export function createGpsMarkerIcon(): L.Icon {
  const svgString = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
      <circle cx="12" cy="12" r="8" fill="${COLORS.userGps}"/>
      <circle cx="12" cy="12" r="4" fill="white"/>
    </svg>
  `;

  return L.icon({
    iconUrl: `data:image/svg+xml;base64,${btoa(svgString)}`,
    iconSize: [SIZES.markerWidth, SIZES.markerHeight],
    iconAnchor: [SIZES.markerAnchorX, SIZES.markerAnchorY],
    popupAnchor: [0, -SIZES.markerAnchorY],
    className: "user-location-marker-gps",
  });
}

/**
 * Crea icono personalizado de SVG para marcador de usuario (IP fallback)
 * Retorna icono compatible con Leaflet
 */
export function createIpMarkerIcon(): L.Icon {
  const svgString = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
      <circle cx="12" cy="12" r="8" fill="${COLORS.userIp}"/>
      <circle cx="12" cy="12" r="4" fill="white"/>
      <path d="M12 2 L14 8 L20 10 L14 12 L12 18 L10 12 L4 10 L10 8 Z" fill="${COLORS.userIp}" opacity="0.3"/>
    </svg>
  `;

  return L.icon({
    iconUrl: `data:image/svg+xml;base64,${btoa(svgString)}`,
    iconSize: [SIZES.markerWidth, SIZES.markerHeight],
    iconAnchor: [SIZES.markerAnchorX, SIZES.markerAnchorY],
    popupAnchor: [0, -SIZES.markerAnchorY],
    className: "user-location-marker-ip",
  });
}

/**
 * Crea icono personalizado para comercios (futuro)
 * Ejemplo: marcador rojo con ícono de tienda
 */
export function createCommerceMarkerIcon(isSelected: boolean = false): L.Icon {
  const color = isSelected ? COLORS.commerceSelected : COLORS.commerce;
  const svgString = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
      <circle cx="12" cy="12" r="8" fill="${color}"/>
      <circle cx="12" cy="12" r="4" fill="white"/>
      <text x="12" y="15" text-anchor="middle" font-size="10" fill="${color}" font-weight="bold">S</text>
    </svg>
  `;

  return L.icon({
    iconUrl: `data:image/svg+xml;base64,${btoa(svgString)}`,
    iconSize: [SIZES.markerWidth, SIZES.markerHeight],
    iconAnchor: [SIZES.markerAnchorX, SIZES.markerAnchorY],
    popupAnchor: [0, -SIZES.markerAnchorY],
    className: isSelected ? "commerce-marker-selected" : "commerce-marker",
  });
}

/**
 * Estilos CSS adicionales para popups de Leaflet
 * Se pueden inyectar en globals.css o importar dinámicamente
 */
export const POPUP_STYLES = `
.leaflet-popup-content-wrapper {
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.leaflet-popup-tip-container {
  position: absolute;
  width: 100%;
  height: 100%;
  overflow: visible;
}

.leaflet-popup {
  margin-bottom: 20px;
}

.location-popup {
  font-family: inherit;
  font-size: 14px;
  line-height: 1.5;
}

.location-popup__title {
  font-weight: 600;
  color: ${COLORS.text};
  margin-bottom: 8px;
}

.location-popup__detail {
  font-size: 12px;
  color: ${COLORS.mutedText};
  margin: 4px 0;
}

.location-popup__cta {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid ${COLORS.border};
}

.location-popup__button {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 600;
  color: #2563eb;
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px 0;
  transition: color 0.2s;
}

.location-popup__button:hover {
  color: #1d4ed8;
  text-decoration: underline;
}
`;

/**
 * Configuración de accesibilidad para popups
 */
export const POPUP_ACCESSIBILITY = {
  closeButton: true,
  autoClose: true,
  // closeOnClick: false, // mantener abierto al clickear afuera
  autoPan: true,
  autoPanPaddingTopLeft: [50, 50],
  autoPanPaddingBottomRight: [50, 50],
};
