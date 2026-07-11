"use client";

import dynamic from "next/dynamic";
import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { MapPin, Navigation } from "lucide-react";
import { useLocation } from "@/hooks/index";
import { getCities, getNeighborhoods } from "@/lib/api/location";
import { geocode, reverseGeocodePoint } from "@/lib/geolocation/geocoding-client";
import { matchNeighborhoodByName } from "@/lib/geolocation/neighborhood-match";
import { isLowConfidenceGeocode } from "@/lib/geolocation/geocode-confidence";
import type { GeocodeResult } from "@/lib/api/geocode";
import { CitySelect, DepartmentSelect, NeighborhoodCombobox } from "@/components/provider/ui";
import type {
  ProviderBranchFormFieldErrors,
  ProviderBranchFormInput,
} from "@/hooks/provider/use-provider-branch-form";
import { validateBranchForm } from "@/lib/provider/validations/branch-form";

const LocationMapField = dynamic(
  () => import("./location-map-field").then((module) => module.LocationMapField),
  { ssr: false }
);

const ADDRESS_BLUR_GEOCODE_DELAY_MS = 600;

/**
 * Construye una dirección corta y legible a partir del resultado de
 * geocoding (ej. "Calle 10 #5-20"). Si Nominatim no devuelve `road`, cae al
 * `display_name` completo (más largo, pero mejor que dejar el campo vacío).
 */
function buildSuggestedAddress(result: GeocodeResult): string | null {
  const { road, house_number } = result.address;
  if (road) {
    return house_number ? `${road} #${house_number}` : road;
  }
  return result.display_name;
}

export type BranchFormMode = "create" | "edit";

type DaySchedule = {
  dayOfWeek: number;
  label: string;
  enabled: boolean;
  openTime: string;
  closeTime: string;
};

type BranchHourInitialData = {
  day_of_week: number | string;
  open_time: string;
  close_time: string;
};

export interface BranchFormInitialData {
  name: string;
  address: string;
  latitude?: number | null;
  longitude?: number | null;
  phone?: string | null;
  email?: string | null;
  departmentName?: string | null;
  cityName?: string | null;
  neighborhoodName?: string | null;
  hours?: BranchHourInitialData[];
}

const INITIAL_SCHEDULE: DaySchedule[] = [
  { dayOfWeek: 1, label: "Lunes", enabled: false, openTime: "08:00", closeTime: "18:00" },
  { dayOfWeek: 2, label: "Martes", enabled: false, openTime: "08:00", closeTime: "18:00" },
  { dayOfWeek: 3, label: "Miércoles", enabled: false, openTime: "08:00", closeTime: "18:00" },
  { dayOfWeek: 4, label: "Jueves", enabled: false, openTime: "08:00", closeTime: "18:00" },
  { dayOfWeek: 5, label: "Viernes", enabled: false, openTime: "08:00", closeTime: "18:00" },
  { dayOfWeek: 6, label: "Sábado", enabled: false, openTime: "09:00", closeTime: "17:00" },
  { dayOfWeek: 0, label: "Domingo", enabled: false, openTime: "09:00", closeTime: "14:00" },
];

interface BranchFormProps {
  mode: BranchFormMode;
  initialData?: BranchFormInitialData | null;
  submitting: boolean;
  apiError: string | null;
  fieldErrors: ProviderBranchFormFieldErrors;
  onCancel: () => void;
  onSubmit: (input: ProviderBranchFormInput) => Promise<void>;
}

function buildDefaultSchedule(): DaySchedule[] {
  return INITIAL_SCHEDULE.map((day) => ({ ...day }));
}

function applyInitialHours(hours: BranchHourInitialData[] | undefined): DaySchedule[] {
  const base = buildDefaultSchedule();
  if (!hours || hours.length === 0) {
    return base;
  }

  const scheduleByDay = new Map(
    hours.map((hour) => [Number(hour.day_of_week), hour])
  );

  return base.map((day) => {
    const match = scheduleByDay.get(day.dayOfWeek);
    if (!match) {
      return day;
    }

    return {
      ...day,
      enabled: true,
      openTime: match.open_time,
      closeTime: match.close_time,
    };
  });
}

function getInitialBranchFormValues(
  mode: BranchFormMode,
  initialData?: BranchFormInitialData | null
) {
  if (mode === "create" || !initialData) {
    return {
      name: "",
      address: "",
      latitude: null,
      longitude: null,
      phone: "",
      email: "",
      schedule: buildDefaultSchedule(),
    };
  }

  return {
    name: initialData.name ?? "",
    address: initialData.address ?? "",
    latitude: initialData.latitude ?? null,
    longitude: initialData.longitude ?? null,
    phone: initialData.phone ?? "",
    email: initialData.email ?? "",
    schedule: applyInitialHours(initialData.hours),
  };
}

export function BranchForm({
  mode,
  initialData,
  submitting,
  apiError,
  fieldErrors,
  onCancel,
  onSubmit,
}: BranchFormProps) {
  const {
    departments,
    cities,
    neighborhoods,
    loading,
    selectedDept,
    selectedCity,
    selectedNeighborhood,
    setSelectedDept,
    setSelectedCity,
    setSelectedNeighborhood,
  } = useLocation();

  const initialValues = getInitialBranchFormValues(mode, initialData);

  const [name, setName] = useState(initialValues.name);
  const [address, setAddress] = useState(initialValues.address);
  const [latitude, setLatitude] = useState<number | null>(initialValues.latitude ?? null);
  const [longitude, setLongitude] = useState<number | null>(initialValues.longitude ?? null);
  const [phone, setPhone] = useState(initialValues.phone);
  const [email, setEmail] = useState(initialValues.email);
  const [schedule, setSchedule] = useState<DaySchedule[]>(initialValues.schedule);
  const [localErrors, setLocalErrors] = useState<ProviderBranchFormFieldErrors>({});
  const [recenterSignal, setRecenterSignal] = useState(0);
  const [geocoding, setGeocoding] = useState(false);
  const [reverseGeocoding, setReverseGeocoding] = useState(false);
  const [geocodeError, setGeocodeError] = useState<string | null>(null);
  const [neighborhoodMatchWarning, setNeighborhoodMatchWarning] = useState<string | null>(null);
  const nameInputRef = useRef<HTMLInputElement>(null);
  const addressBlurTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(() => {
    nameInputRef.current?.focus();
  }, [mode, initialData]);

  useEffect(() => {
    if (mode !== "edit" || !initialData?.departmentName || departments.length === 0 || selectedDept) {
      return;
    }

    const match = departments.find(
      (department) =>
        department.name.trim().toLowerCase() ===
        initialData.departmentName?.trim().toLowerCase()
    );

    if (match) {
      setSelectedDept(match.id);
    }
  }, [mode, initialData, departments, selectedDept, setSelectedDept]);

  useEffect(() => {
    if (mode !== "edit" || !initialData?.cityName || cities.length === 0 || selectedCity) {
      return;
    }

    const match = cities.find(
      (city) => city.name.trim().toLowerCase() === initialData.cityName?.trim().toLowerCase()
    );

    if (match) {
      setSelectedCity(match.id);
    }
  }, [mode, initialData, cities, selectedCity, setSelectedCity]);

  useEffect(() => {
    if (
      mode !== "edit" ||
      !initialData?.neighborhoodName ||
      neighborhoods.length === 0 ||
      selectedNeighborhood
    ) {
      return;
    }

    const match = neighborhoods.find(
      (neighborhood) =>
        neighborhood.name.trim().toLowerCase() ===
        initialData.neighborhoodName?.trim().toLowerCase()
    );

    if (match) {
      setSelectedNeighborhood(match.id);
    }
  }, [
    mode,
    initialData,
    neighborhoods,
    selectedNeighborhood,
    setSelectedNeighborhood,
  ]);

  const getFieldError = (...keys: string[]): string | undefined => {
    for (const key of keys) {
      if (localErrors[key]) {
        return localErrors[key];
      }

      if (fieldErrors[key]) {
        return fieldErrors[key];
      }
    }
    return undefined;
  };

  const getFieldErrorByPrefix = (prefix: string): string | undefined => {
    const localKey = Object.keys(localErrors).find((fieldKey) =>
      fieldKey.startsWith(prefix)
    );

    if (localKey) {
      return localErrors[localKey];
    }

    const key = Object.keys(fieldErrors).find((fieldKey) =>
      fieldKey.startsWith(prefix)
    );

    if (!key) {
      return undefined;
    }

    return fieldErrors[key];
  };

  const clearLocalError = (...keys: string[]) => {
    setLocalErrors((previous) => {
      const updated = { ...previous };
      keys.forEach((key) => {
        delete updated[key];
      });
      return updated;
    });
  };

  const enabledDayIndexMap = useMemo(() => {
    const map = new Map<number, number>();
    let index = 0;

    schedule.forEach((day) => {
      if (day.enabled) {
        map.set(day.dayOfWeek, index);
        index += 1;
      }
    });

    return map;
  }, [schedule]);

  const updateSchedule = (dayOfWeek: number, updates: Partial<DaySchedule>) => {
    setSchedule((previous) =>
      previous.map((day) =>
        day.dayOfWeek === dayOfWeek
          ? {
              ...day,
              ...updates,
            }
          : day
      )
    );
  };

  const handleDepartmentChange = (value: string) => {
    const departmentId = value ? Number(value) : null;
    setSelectedDept(departmentId);
    setSelectedCity(null);
    setSelectedNeighborhood(null);
    clearLocalError(
      "commerce_branch.department_id",
      "commerce_branch.city_id",
      "commerce_branch.neighborhood_id"
    );
  };

  const handleCityChange = (value: string) => {
    const cityId = value ? Number(value) : null;
    setSelectedCity(cityId);
    setSelectedNeighborhood(null);
    clearLocalError("commerce_branch.city_id", "commerce_branch.neighborhood_id");
  };

  const handleNeighborhoodChange = (neighborhoodId: number | null) => {
    setSelectedNeighborhood(neighborhoodId);
    clearLocalError("commerce_branch.neighborhood_id");
    setNeighborhoodMatchWarning(null);
  };

  useEffect(() => {
    return () => {
      if (addressBlurTimeoutRef.current) {
        clearTimeout(addressBlurTimeoutRef.current);
      }
    };
  }, []);

  /**
   * Resuelve depto/ciudad/barrio a partir de un resultado de geocoding, para
   * mantener coherencia con el punto físico (fuente de verdad). MVP: depto/
   * ciudad son siempre Bogotá D.C. (único registro sembrado) — si aún no
   * están seleccionados se resuelven directo contra la API en vez de esperar
   * el ciclo de fetch del hook de cascada. El barrio se resuelve por match
   * difuso de nombre (Nominatim no conoce nuestro catálogo de sectores).
   */
  const applyAdminSyncFromResult = useCallback(
    async (result: GeocodeResult) => {
      let resolvedDeptId = selectedDept;
      let resolvedCityId = selectedCity;
      let resolvedNeighborhoods = neighborhoods;

      if (!resolvedDeptId && departments.length > 0) {
        resolvedDeptId = departments[0].id;
        setSelectedDept(resolvedDeptId);
        clearLocalError("commerce_branch.department_id");
      }

      if (!resolvedCityId && resolvedDeptId) {
        try {
          const citiesResponse = await getCities({ department_id: resolvedDeptId, per_page: 1 });
          resolvedCityId = citiesResponse.data[0]?.id ?? null;
          if (resolvedCityId) {
            setSelectedCity(resolvedCityId);
            clearLocalError("commerce_branch.city_id");
          }
        } catch {
          resolvedCityId = null;
        }
      }

      if (!resolvedCityId) {
        return;
      }

      // El backend ya descarta artefactos administrativos (UPZ, Localidad):
      // si no hay barrio real detectado, no se puede sincronizar contra el
      // catálogo. No se toca la selección del usuario (podría ser una elección
      // manual válida), pero se limpia cualquier advertencia previa obsoleta.
      if (!result.address.neighborhood) {
        setNeighborhoodMatchWarning(null);
        return;
      }

      if (resolvedCityId !== selectedCity) {
        // Ciudad recién resuelta en esta misma llamada: el set de barrios
        // del hook todavía no la refleja (su fetch es asíncrono).
        try {
          const neighborhoodsResponse = await getNeighborhoods({
            city_id: resolvedCityId,
            per_page: 2000,
          });
          resolvedNeighborhoods = neighborhoodsResponse.data;
        } catch {
          resolvedNeighborhoods = [];
        }
      }

      // El barrio SIEMPRE se resincroniza con el punto (fuente de verdad):
      // si no hay match en el catálogo, se limpia la selección anterior en
      // vez de dejar un barrio que ya no corresponde al pin actual.
      const match = matchNeighborhoodByName(result.address.neighborhood, resolvedNeighborhoods);
      setSelectedNeighborhood(match ? match.id : null);
      if (match) {
        clearLocalError("commerce_branch.neighborhood_id");
        setNeighborhoodMatchWarning(null);
      } else {
        setNeighborhoodMatchWarning(
          `No encontramos "${result.address.neighborhood}" en nuestro catálogo de barrios. Selecciónalo manualmente.`
        );
      }
    },
    [selectedDept, selectedCity, departments, neighborhoods, setSelectedDept, setSelectedCity, setSelectedNeighborhood]
  );

  /**
   * Geocoding inverso: se dispara cada vez que el usuario fija/mueve el pin
   * en el mapa. El pin ya quedó fijado (fuente de verdad) antes de esperar
   * la respuesta — si Nominatim falla, la ubicación manual sigue siendo válida.
   */
  const handlePinChange = useCallback(
    async (lat: number, lng: number) => {
      setLatitude(lat);
      setLongitude(lng);
      clearLocalError("commerce_branch.location");

      setReverseGeocoding(true);
      const result = await reverseGeocodePoint(lat, lng);
      setReverseGeocoding(false);

      if (!result) {
        return;
      }

      // El pin es la fuente de verdad: la dirección sugerida siempre se
      // actualiza al mover el punto (evita inconsistencias pin↔dirección).
      const suggestedAddress = buildSuggestedAddress(result);
      if (suggestedAddress) {
        setAddress(suggestedAddress);
        clearLocalError("commerce_branch.address");
      }

      await applyAdminSyncFromResult(result);
    },
    [applyAdminSyncFromResult]
  );

  /**
   * Geocoding directo: dirección de texto → punto. Se dispara on-demand
   * (botón) o tras una pausa breve al salir del campo dirección (blur
   * debounced), nunca por cada tecla — respeta la política de uso de Nominatim.
   */
  const runLocateAddress = useCallback(async () => {
    if (!address.trim()) {
      return;
    }

    const cityName = cities.find((city) => city.id === selectedCity)?.name;
    const neighborhoodName = neighborhoods.find((n) => n.id === selectedNeighborhood)?.name;
    const query = [address.trim(), neighborhoodName, cityName ?? "Bogotá D.C."]
      .filter((part): part is string => Boolean(part))
      .join(", ");

    setGeocoding(true);
    setGeocodeError(null);
    const result = await geocode(query);
    setGeocoding(false);

    if (!result) {
      setGeocodeError(
        "No pudimos ubicar esa dirección automáticamente. Puedes marcarla manualmente en el mapa."
      );
      return;
    }

    setLatitude(result.lat);
    setLongitude(result.lng);
    setRecenterSignal((previous) => previous + 1);
    clearLocalError("commerce_branch.location");

    // Nominatim puede devolver la vía más "cercana" en vez de un no-match
    // cuando no encuentra la dirección exacta (ej. buscar "Cra 99f" y recibir
    // "Cra 71D") — el pin igual se coloca (es asistencia), pero se advierte.
    setGeocodeError(
      isLowConfidenceGeocode(address, result)
        ? "Encontramos una ubicación aproximada: la dirección exacta podría no coincidir. Verifica y ajusta el pin en el mapa si hace falta."
        : null
    );

    await applyAdminSyncFromResult(result);
  }, [address, cities, selectedCity, neighborhoods, selectedNeighborhood, applyAdminSyncFromResult]);

  const handleLocateButtonClick = () => {
    if (addressBlurTimeoutRef.current) {
      clearTimeout(addressBlurTimeoutRef.current);
      addressBlurTimeoutRef.current = null;
    }
    runLocateAddress();
  };

  const handleAddressBlur = () => {
    if (addressBlurTimeoutRef.current) {
      clearTimeout(addressBlurTimeoutRef.current);
    }
    addressBlurTimeoutRef.current = setTimeout(() => {
      runLocateAddress();
    }, ADDRESS_BLUR_GEOCODE_DELAY_MS);
  };

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    const activeHours = schedule
      .filter((day) => day.enabled)
      .map((day) => ({
        day_of_week: day.dayOfWeek,
        open_time: day.openTime,
        close_time: day.closeTime,
      }));

    const validationErrors = validateBranchForm({
      name,
      departmentId: selectedDept,
      cityId: selectedCity,
      neighborhoodId: selectedNeighborhood,
      address,
      latitude,
      longitude,
      phone,
      email,
      hours: activeHours,
    });

    if (Object.keys(validationErrors).length > 0) {
      setLocalErrors(validationErrors);
      return;
    }

    if (!selectedDept || !selectedCity || !selectedNeighborhood) {
      return;
    }

    setLocalErrors({});

    await onSubmit({
      departmentId: selectedDept,
      cityId: selectedCity,
      neighborhoodId: selectedNeighborhood,
      name,
      address,
      latitude,
      longitude,
      phone: phone || null,
      email: email || null,
      status: true,
      hours: activeHours,
    });
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div className="space-y-2">
        <label htmlFor="branch-name" className="text-sm text-[#1A1A1A]">
          Nombre de la Sede *
        </label>
        <input
          id="branch-name"
          ref={nameInputRef}
          type="text"
          value={name}
          onChange={(event) => {
            setName(event.target.value);
            clearLocalError("commerce_branch.name");
          }}
          className="w-full h-[50px] rounded-[14px] border border-[#E0E0E0] px-4 outline-none focus:ring-2 focus:ring-[#4B236A]/20"
          placeholder="Ej: Sucursal Centro"
        />
        {getFieldError("commerce_branch.name") && (
          <p className="text-sm text-red-600">{getFieldError("commerce_branch.name")}</p>
        )}
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <DepartmentSelect
          departments={departments}
          value={selectedDept}
          onChange={(id) => handleDepartmentChange(id?.toString() ?? "")}
          disabled={submitting}
          loading={loading.departments}
          required
          error={getFieldError("commerce_branch.department_id")}
        />

        <CitySelect
          cities={cities}
          departmentId={selectedDept}
          value={selectedCity}
          onChange={(id) => handleCityChange(id?.toString() ?? "")}
          disabled={submitting}
          loading={loading.cities}
          required
          error={getFieldError("commerce_branch.city_id")}
        />

        <NeighborhoodCombobox
          neighborhoods={neighborhoods}
          cityId={selectedCity}
          value={selectedNeighborhood}
          onChange={handleNeighborhoodChange}
          disabled={submitting}
          loading={loading.neighborhoods}
          required
          error={getFieldError("commerce_branch.neighborhood_id")}
        />
      </div>

      {neighborhoodMatchWarning && (
        <p className="text-sm text-amber-700 -mt-4">{neighborhoodMatchWarning}</p>
      )}

      <div className="space-y-2">
        <label htmlFor="branch-address" className="text-sm text-[#1A1A1A]">
          Dirección *
        </label>
        <div className="flex gap-2">
          <input
            id="branch-address"
            type="text"
            value={address}
            onChange={(event) => {
              setAddress(event.target.value);
              clearLocalError("commerce_branch.address");
            }}
            onBlur={handleAddressBlur}
            className="flex-1 h-[50px] rounded-[14px] border border-[#E0E0E0] px-4 outline-none focus:ring-2 focus:ring-[#4B236A]/20"
            placeholder="Ej: Calle 10 #5-20"
          />
          <button
            type="button"
            onClick={handleLocateButtonClick}
            disabled={submitting || geocoding || !address.trim()}
            className="h-[50px] px-4 rounded-[14px] border border-[#E0E0E0] bg-[#F7F7F7] hover:bg-[#DDE8BB] text-[#1A1A1A] transition-colors inline-flex items-center gap-2 disabled:opacity-60 shrink-0"
            title="Ubicar esta dirección en el mapa"
          >
            <Navigation size={16} className="text-[#4B236A]" />
            <span className="hidden sm:inline">{geocoding ? "Ubicando..." : "Ubicar en mapa"}</span>
          </button>
        </div>
        {getFieldError("commerce_branch.address") && (
          <p className="text-sm text-red-600">{getFieldError("commerce_branch.address")}</p>
        )}
        {geocodeError && <p className="text-sm text-amber-700">{geocodeError}</p>}
      </div>

      <div className="space-y-2">
        <label className="text-sm text-[#1A1A1A]">Ubicación en el mapa *</label>
        <p className="text-xs text-[#6A6A6A]">
          Haz clic en el mapa para fijar el punto exacto. La dirección y el barrio se
          sincronizan automáticamente con el punto seleccionado.
        </p>

        <LocationMapField
          latitude={latitude}
          longitude={longitude}
          onPinChange={handlePinChange}
          recenterSignal={recenterSignal}
        />

        <div className="flex items-center justify-between gap-2 min-h-[20px]">
          {latitude !== null && longitude !== null ? (
            <p className="text-sm text-[#1A1A1A] flex items-center gap-1.5">
              <MapPin size={14} className="text-[#4B236A]" />
              Coordenadas: <span className="font-medium">{latitude.toFixed(4)}</span>,{" "}
              <span className="font-medium">{longitude.toFixed(4)}</span>
            </p>
          ) : (
            <span />
          )}
          {reverseGeocoding && (
            <p className="text-xs text-[#6A6A6A]">Detectando ubicación...</p>
          )}
        </div>

        {getFieldError("commerce_branch.location") && (
          <p className="text-sm text-red-600">{getFieldError("commerce_branch.location")}</p>
        )}
      </div>

      <div className="space-y-2">
        <label htmlFor="branch-phone" className="text-sm text-[#1A1A1A]">
          Número de contacto
        </label>
        <input
          id="branch-phone"
          type="tel"
          inputMode="numeric"
          value={phone}
          onChange={(event) => {
            setPhone(event.target.value.replace(/\D/g, "").slice(0, 10));
            clearLocalError("commerce_branch.phone");
          }}
          className="w-full h-[50px] rounded-[14px] border border-[#E0E0E0] px-4 outline-none focus:ring-2 focus:ring-[#4B236A]/20"
          placeholder="Ej: 3001234567"
        />
        {getFieldError("commerce_branch.phone") && (
          <p className="text-sm text-red-600">{getFieldError("commerce_branch.phone")}</p>
        )}
      </div>

      <div className="space-y-2">
        <label htmlFor="branch-email" className="text-sm text-[#1A1A1A]">
          Correo electrónico
        </label>
        <input
          id="branch-email"
          type="email"
          value={email}
          onChange={(event) => {
            setEmail(event.target.value);
            clearLocalError("commerce_branch.email");
          }}
          className="w-full h-[50px] rounded-[14px] border border-[#E0E0E0] px-4 outline-none focus:ring-2 focus:ring-[#4B236A]/20"
          placeholder="Ej: sucursal@comercio.com"
        />
        {getFieldError("commerce_branch.email") && (
          <p className="text-sm text-red-600">{getFieldError("commerce_branch.email")}</p>
        )}
      </div>

      <div className="space-y-3">
        <p className="text-sm text-[#1A1A1A]">Horario de Atención *</p>
        <div className="border border-[#E0E0E0] rounded-[14px] p-4 space-y-3 bg-[#F7F7F7]">
          {schedule.map((day) => (
            <div key={day.dayOfWeek} className="rounded-[14px] border border-[#E0E0E0] bg-white p-3">
              <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <label className="inline-flex items-center gap-2 text-sm text-[#1A1A1A]">
                  <input
                    type="checkbox"
                    checked={day.enabled}
                    onChange={(event) => {
                      updateSchedule(day.dayOfWeek, { enabled: event.target.checked });
                      clearLocalError(
                        "commerce_branch_hours",
                        `commerce_branch_hours.${enabledDayIndexMap.get(day.dayOfWeek) ?? 0}.open_time`,
                        `commerce_branch_hours.${enabledDayIndexMap.get(day.dayOfWeek) ?? 0}.close_time`
                      );
                    }}
                    className="h-4 w-4 accent-[#4B236A]"
                    disabled={submitting}
                  />
                  {day.label}
                </label>

                {day.enabled && (
                  <div className="flex items-center gap-2">
                    {(() => {
                      const activeIndex = enabledDayIndexMap.get(day.dayOfWeek) ?? 0;
                      const openTimeError = getFieldError(`commerce_branch_hours.${activeIndex}.open_time`);
                      const closeTimeError = getFieldError(`commerce_branch_hours.${activeIndex}.close_time`);

                      return (
                        <>
                          <div>
                            <input
                              type="time"
                              value={day.openTime}
                              onChange={(event) => {
                                updateSchedule(day.dayOfWeek, { openTime: event.target.value });
                                clearLocalError(`commerce_branch_hours.${activeIndex}.open_time`);
                              }}
                              className="h-[42px] rounded-[14px] border border-[#E0E0E0] px-3"
                              disabled={submitting}
                            />
                            {openTimeError && (
                              <p className="text-xs text-red-600 mt-1">{openTimeError}</p>
                            )}
                          </div>
                          <span className="text-[#6A6A6A]">a</span>
                          <div>
                            <input
                              type="time"
                              value={day.closeTime}
                              onChange={(event) => {
                                updateSchedule(day.dayOfWeek, { closeTime: event.target.value });
                                clearLocalError(`commerce_branch_hours.${activeIndex}.close_time`);
                              }}
                              className="h-[42px] rounded-[14px] border border-[#E0E0E0] px-3"
                              disabled={submitting}
                            />
                            {closeTimeError && (
                              <p className="text-xs text-red-600 mt-1">{closeTimeError}</p>
                            )}
                          </div>
                        </>
                      );
                    })()}
                  </div>
                )}
              </div>
            </div>
          ))}
        </div>

        {(getFieldError("commerce_branch_hours") ||
          getFieldErrorByPrefix("commerce_branch_hours.")) && (
          <p className="text-sm text-red-600">
            {getFieldError("commerce_branch_hours") ??
              getFieldErrorByPrefix("commerce_branch_hours.")}
          </p>
        )}
      </div>

      {apiError && <p className="text-sm text-red-600">{apiError}</p>}

      <div className="flex justify-end gap-3 pt-2">
        <button
          type="button"
          onClick={onCancel}
          className="rounded-[14px] h-[48px] px-6 border border-[#DDE8BB] text-[#4B236A] hover:bg-[#DDE8BB] hover:text-[#4B236A] transition-colors"
          disabled={submitting}
        >
          Cancelar
        </button>
        <button
          type="submit"
          className="bg-[#4B236A] hover:bg-[#4B236A]/90 text-white rounded-[14px] h-[52px] px-6 shadow-md transition-colors disabled:opacity-60"
          disabled={submitting}
        >
          {submitting
            ? mode === "edit"
              ? "Actualizando..."
              : "Guardando..."
            : mode === "edit"
              ? "Actualizar Sucursal"
              : "Guardar Sucursal"}
        </button>
      </div>
    </form>
  );
}
