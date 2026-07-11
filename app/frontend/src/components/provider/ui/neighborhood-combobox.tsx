'use client';

import { useEffect, useMemo, useRef, useState } from 'react';
import type { Neighborhood } from '@/types/location';
import { Input } from '@/components/provider/ui/input';
import { Label } from '@/components/provider/ui/label';
import { cn } from '@/components/provider/ui/utils';
import { normalizeSearchText } from '@/lib/utils/text-normalize';
import { AlertCircle, ChevronDown } from 'lucide-react';

interface NeighborhoodComboboxProps {
  neighborhoods: Neighborhood[];
  cityId: number | null;
  value: number | null;
  onChange: (id: number | null) => void;
  disabled?: boolean;
  loading?: boolean;
  label?: string;
  required?: boolean;
  error?: string | null;
}

const MAX_RESULTS = 50;

/**
 * Combobox de barrio con autocompletado en memoria.
 *
 * Reemplaza el dropdown nativo: con ~1.100 barrios sembrados por ciudad,
 * un <select> es inmanejable. El filtrado corre sobre los datos ya cargados
 * por el hook de ubicación (una sola carga por ciudad, sin request por tecla).
 */
export function NeighborhoodCombobox({
  neighborhoods,
  cityId,
  value,
  onChange,
  disabled = false,
  loading = false,
  label = 'Barrio',
  required = false,
  error,
}: NeighborhoodComboboxProps) {
  const [query, setQuery] = useState('');
  const [open, setOpen] = useState(false);
  const [highlightedIndex, setHighlightedIndex] = useState(0);
  const [previousValue, setPreviousValue] = useState<number | null>(value);
  const containerRef = useRef<HTMLDivElement>(null);

  const selectedNeighborhood = useMemo(
    () => neighborhoods.find((n) => n.id === value) ?? null,
    [neighborhoods, value]
  );

  // Sincroniza el texto mostrado cuando el value cambia desde afuera (reset en
  // cascada al cambiar de ciudad/departamento, o auto-match en modo edición).
  // Ajuste de estado durante el render (no en un efecto): evita un ciclo
  // adicional de render y respeta la regla react-hooks/set-state-in-effect.
  if (value !== previousValue) {
    setPreviousValue(value);
    setQuery(selectedNeighborhood?.name ?? '');
  }

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
        setOpen(false);
        setQuery(selectedNeighborhood?.name ?? '');
      }
    }

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [selectedNeighborhood]);

  const filtered = useMemo(() => {
    const normalizedQuery = normalizeSearchText(query);
    const source =
      normalizedQuery === ''
        ? neighborhoods
        : neighborhoods.filter((n) => normalizeSearchText(n.name).includes(normalizedQuery));

    return source.slice(0, MAX_RESULTS);
  }, [neighborhoods, query]);

  const hasNeighborhoods = neighborhoods.length > 0;
  const isDisabled = disabled || !cityId || loading || !hasNeighborhoods;

  const handleSelect = (neighborhood: Neighborhood) => {
    setQuery(neighborhood.name);
    // Sincroniza previousValue de una vez: evita que el ajuste de estado
    // durante el render (arriba) vuelva a pisar `query` en el próximo render.
    setPreviousValue(neighborhood.id);
    setOpen(false);
    onChange(neighborhood.id);
  };

  const handleQueryChange = (text: string) => {
    setQuery(text);
    setOpen(true);
    setHighlightedIndex(0);
    if (value !== null) {
      setPreviousValue(null);
      onChange(null);
    }
  };

  const handleKeyDown = (event: React.KeyboardEvent<HTMLInputElement>) => {
    if (!open && (event.key === 'ArrowDown' || event.key === 'ArrowUp')) {
      event.preventDefault();
      setOpen(true);
      return;
    }

    if (event.key === 'ArrowDown') {
      event.preventDefault();
      setHighlightedIndex((index) => Math.min(index + 1, filtered.length - 1));
    } else if (event.key === 'ArrowUp') {
      event.preventDefault();
      setHighlightedIndex((index) => Math.max(index - 1, 0));
    } else if (event.key === 'Enter') {
      event.preventDefault();
      const item = filtered[highlightedIndex];
      if (item) {
        handleSelect(item);
      }
    } else if (event.key === 'Escape') {
      event.preventDefault();
      setOpen(false);
      setQuery(selectedNeighborhood?.name ?? '');
    }
  };

  const listboxId = 'neighborhood-combobox-listbox';
  const activeOptionId = open && filtered[highlightedIndex] ? `neighborhood-option-${filtered[highlightedIndex].id}` : undefined;

  const placeholder = !cityId
    ? 'Selecciona una ciudad primero'
    : !hasNeighborhoods
      ? 'No hay barrios disponibles'
      : 'Escribe para buscar un barrio';

  return (
    <div className="space-y-2 w-full" ref={containerRef}>
      <Label htmlFor="neighborhood-combobox" className="text-sm font-medium text-[#1A1A1A]">
        {label}
        {required && <span className="text-red-500 ml-1">*</span>}
      </Label>

      <div className="relative">
        <Input
          id="neighborhood-combobox"
          role="combobox"
          aria-expanded={open}
          aria-autocomplete="list"
          aria-controls={listboxId}
          aria-activedescendant={activeOptionId}
          autoComplete="off"
          value={query}
          onChange={(event) => handleQueryChange(event.target.value)}
          onFocus={() => !isDisabled && setOpen(true)}
          onKeyDown={handleKeyDown}
          disabled={isDisabled}
          placeholder={placeholder}
          className={cn(
            'h-[50px] rounded-[14px] border-[#E0E0E0] pr-9',
            error && 'border-red-500'
          )}
        />
        <ChevronDown
          size={16}
          className="absolute right-3 top-1/2 -translate-y-1/2 text-[#6A6A6A] pointer-events-none"
        />

        {open && !isDisabled && (
          <ul
            id={listboxId}
            role="listbox"
            className="absolute z-50 mt-1 w-full max-h-[240px] overflow-auto rounded-[14px] border border-[#E0E0E0] bg-white shadow-lg py-1"
          >
            {filtered.length === 0 ? (
              <li className="px-4 py-2 text-sm text-[#6A6A6A]">No se encontraron barrios</li>
            ) : (
              filtered.map((neighborhood, index) => (
                <li
                  key={neighborhood.id}
                  id={`neighborhood-option-${neighborhood.id}`}
                  role="option"
                  aria-selected={neighborhood.id === value}
                  onMouseDown={(event) => event.preventDefault()}
                  onClick={() => handleSelect(neighborhood)}
                  onMouseEnter={() => setHighlightedIndex(index)}
                  className={cn(
                    'px-4 py-2 text-sm cursor-pointer text-[#1A1A1A]',
                    index === highlightedIndex && 'bg-[#DDE8BB]',
                    neighborhood.id === value && 'font-medium'
                  )}
                >
                  {neighborhood.name}
                </li>
              ))
            )}
          </ul>
        )}
      </div>

      {error && (
        <div className="flex items-center gap-2 text-sm text-red-600 mt-2">
          <AlertCircle className="w-4 h-4 flex-shrink-0" />
          <span>{error}</span>
        </div>
      )}

      {loading && cityId && <p className="text-xs text-[#6A6A6A] mt-1">Cargando barrios...</p>}
    </div>
  );
}
