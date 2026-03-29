type DateFormatOptions = {
  emptyFallback?: string;
  invalidFallback?: string;
  locale?: string;
};

export function formatDateDDMMYYYY(
  dateValue?: string | Date,
  options: DateFormatOptions = {}
): string {
  const {
    emptyFallback = 'N/A',
    invalidFallback = emptyFallback,
    locale = 'es-CO',
  } = options;

  if (!dateValue) {
    return emptyFallback;
  }

  const parsedDate = dateValue instanceof Date ? dateValue : new Date(dateValue);

  if (Number.isNaN(parsedDate.getTime())) {
    return invalidFallback;
  }

  return new Intl.DateTimeFormat(locale, {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  }).format(parsedDate);
}