const COMBINING_DIACRITICS_RANGE_START = 0x0300;
const COMBINING_DIACRITICS_RANGE_END = 0x036f;

/**
 * Normaliza texto para comparación/búsqueda: minúsculas, sin acentos, sin
 * espacios sobrantes. Recorre code points en vez de usar un regex con
 * literales Unicode (más robusto ante problemas de encoding del archivo fuente).
 */
export function normalizeSearchText(value: string): string {
  const decomposed = value.normalize('NFD');
  let result = '';

  for (const char of decomposed) {
    const codePoint = char.codePointAt(0) ?? 0;
    if (codePoint < COMBINING_DIACRITICS_RANGE_START || codePoint > COMBINING_DIACRITICS_RANGE_END) {
      result += char;
    }
  }

  return result.toLowerCase().trim();
}
