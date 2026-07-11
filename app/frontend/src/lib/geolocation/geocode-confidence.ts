import { normalizeSearchText } from '@/lib/utils/text-normalize';
import type { GeocodeResult } from '@/lib/api/geocode';

/**
 * Extrae el primer número de vía (ej. "99f", "71d") de un texto de dirección.
 */
function extractStreetNumber(text: string): string | null {
  const match = normalizeSearchText(text).match(/\d+[a-z]?/);
  return match ? match[0] : null;
}

/**
 * Nominatim, cuando no encuentra una coincidencia exacta, suele devolver la
 * vía más "cercana" en su índice — que puede ser una calle/carrera totalmente
 * distinta a la tecleada (ej. buscar "Carrera 99f" y recibir "Carrera 71D").
 * El resultado sigue siendo 200 OK, sin ninguna señal de baja confianza.
 *
 * Esta heurística compara el número de vía tecleado contra el devuelto: si
 * ambos existen y difieren, es señal de que el punto puede no corresponder
 * a la dirección real — el pin se coloca igual (es una asistencia, no una
 * fuente de verdad), pero se advierte para que el usuario verifique/ajuste.
 */
export function isLowConfidenceGeocode(typedAddress: string, result: GeocodeResult): boolean {
  if (!result.address.road) {
    return true;
  }

  const typedNumber = extractStreetNumber(typedAddress);
  const resultNumber = extractStreetNumber(result.address.road);

  if (!typedNumber || !resultNumber) {
    return false;
  }

  return typedNumber !== resultNumber;
}
