import { normalizeSearchText } from '@/lib/utils/text-normalize';
import type { Neighborhood } from '@/types/location';

/**
 * Resuelve el barrio del catálogo cargado que mejor corresponde al nombre
 * devuelto por el geocoding inverso (Nominatim). Nominatim no conoce nuestros
 * 1.091 sectores catastrales — esto es un match difuso por nombre, no un id.
 *
 * Sin coincidencia confiable retorna null: nunca se inventa/asume un barrio,
 * queda para selección manual en el combobox.
 */
export function matchNeighborhoodByName(
  candidateName: string | null | undefined,
  neighborhoods: Neighborhood[]
): Neighborhood | null {
  if (!candidateName || neighborhoods.length === 0) {
    return null;
  }

  const normalizedCandidate = normalizeSearchText(candidateName);
  if (normalizedCandidate === '') {
    return null;
  }

  const exactMatch = neighborhoods.find(
    (neighborhood) => normalizeSearchText(neighborhood.name) === normalizedCandidate
  );
  if (exactMatch) {
    return exactMatch;
  }

  const partialMatch = neighborhoods.find((neighborhood) => {
    const normalizedName = normalizeSearchText(neighborhood.name);
    return normalizedName.includes(normalizedCandidate) || normalizedCandidate.includes(normalizedName);
  });

  return partialMatch ?? null;
}
