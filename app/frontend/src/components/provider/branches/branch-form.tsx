"use client";

import { useEffect, useMemo, useRef, useState } from "react";
import { useLocation } from "@/hooks";
import type {
  ProviderBranchFormFieldErrors,
  ProviderBranchFormInput,
} from "@/hooks/provider/use-provider-branch-form";
import { validateBranchForm } from "@/lib/provider/validations/branch-form";

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

  const [name, setName] = useState("");
  const [address, setAddress] = useState("");
  const [phone, setPhone] = useState("");
  const [email, setEmail] = useState("");
  const [schedule, setSchedule] = useState<DaySchedule[]>(buildDefaultSchedule);
  const [localErrors, setLocalErrors] = useState<ProviderBranchFormFieldErrors>({});
  const nameInputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    nameInputRef.current?.focus();
  }, [mode, initialData]);

  useEffect(() => {
    if (mode === "create" || !initialData) {
      setName("");
      setAddress("");
      setPhone("");
      setEmail("");
      setSchedule(buildDefaultSchedule());
      setLocalErrors({});
      setSelectedDept(null);
      setSelectedCity(null);
      setSelectedNeighborhood(null);
      return;
    }

    setName(initialData.name ?? "");
    setAddress(initialData.address ?? "");
    setPhone(initialData.phone ?? "");
    setEmail(initialData.email ?? "");
    setSchedule(applyInitialHours(initialData.hours));
    setLocalErrors({});
    setSelectedDept(null);
    setSelectedCity(null);
    setSelectedNeighborhood(null);
  }, [
    mode,
    initialData,
    setSelectedDept,
    setSelectedCity,
    setSelectedNeighborhood,
  ]);

  const filteredCities = useMemo(() => {
    if (!selectedDept) return [];
    return cities.filter((city) => city.department_id === selectedDept);
  }, [cities, selectedDept]);

  const filteredNeighborhoods = useMemo(() => {
    if (!selectedCity) return [];
    return neighborhoods.filter((neighborhood) => neighborhood.city_id === selectedCity);
  }, [neighborhoods, selectedCity]);

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
    if (mode !== "edit" || !initialData?.cityName || filteredCities.length === 0 || selectedCity) {
      return;
    }

    const match = filteredCities.find(
      (city) => city.name.trim().toLowerCase() === initialData.cityName?.trim().toLowerCase()
    );

    if (match) {
      setSelectedCity(match.id);
    }
  }, [mode, initialData, filteredCities, selectedCity, setSelectedCity]);

  useEffect(() => {
    if (
      mode !== "edit" ||
      !initialData?.neighborhoodName ||
      filteredNeighborhoods.length === 0 ||
      selectedNeighborhood
    ) {
      return;
    }

    const match = filteredNeighborhoods.find(
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
    filteredNeighborhoods,
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

  const handleNeighborhoodChange = (value: string) => {
    const neighborhoodId = value ? Number(value) : null;
    setSelectedNeighborhood(neighborhoodId);
    clearLocalError("commerce_branch.neighborhood_id");
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
        <div className="space-y-2">
          <label htmlFor="branch-department" className="text-sm text-[#1A1A1A]">
            Departamento *
          </label>
          <select
            id="branch-department"
            value={selectedDept ?? ""}
            onChange={(event) => handleDepartmentChange(event.target.value)}
            className="w-full h-[50px] rounded-[14px] border border-[#E0E0E0] px-4 bg-white outline-none focus:ring-2 focus:ring-[#4B236A]/20"
            disabled={loading.departments || submitting}
          >
            <option value="">Selecciona departamento</option>
            {departments.map((department) => (
              <option key={department.id} value={department.id}>
                {department.name}
              </option>
            ))}
          </select>
          {getFieldError("commerce_branch.department_id") && (
            <p className="text-sm text-red-600">{getFieldError("commerce_branch.department_id")}</p>
          )}
        </div>

        <div className="space-y-2">
          <label htmlFor="branch-city" className="text-sm text-[#1A1A1A]">
            Ciudad *
          </label>
          <select
            id="branch-city"
            value={selectedCity ?? ""}
            onChange={(event) => handleCityChange(event.target.value)}
            className="w-full h-[50px] rounded-[14px] border border-[#E0E0E0] px-4 bg-white outline-none focus:ring-2 focus:ring-[#4B236A]/20"
            disabled={!selectedDept || loading.cities || submitting}
          >
            <option value="">Selecciona ciudad</option>
            {filteredCities.map((city) => (
              <option key={city.id} value={city.id}>
                {city.name}
              </option>
            ))}
          </select>
          {getFieldError("commerce_branch.city_id") && (
            <p className="text-sm text-red-600">{getFieldError("commerce_branch.city_id")}</p>
          )}
        </div>

        <div className="space-y-2">
          <label htmlFor="branch-neighborhood" className="text-sm text-[#1A1A1A]">
            Barrio *
          </label>
          <select
            id="branch-neighborhood"
            value={selectedNeighborhood ?? ""}
            onChange={(event) => handleNeighborhoodChange(event.target.value)}
            className="w-full h-[50px] rounded-[14px] border border-[#E0E0E0] px-4 bg-white outline-none focus:ring-2 focus:ring-[#4B236A]/20"
            disabled={!selectedCity || loading.neighborhoods || submitting}
          >
            <option value="">Selecciona barrio</option>
            {filteredNeighborhoods.map((neighborhood) => (
              <option key={neighborhood.id} value={neighborhood.id}>
                {neighborhood.name}
              </option>
            ))}
          </select>
          {getFieldError("commerce_branch.neighborhood_id") && (
            <p className="text-sm text-red-600">{getFieldError("commerce_branch.neighborhood_id")}</p>
          )}
        </div>
      </div>

      <div className="space-y-2">
        <label htmlFor="branch-address" className="text-sm text-[#1A1A1A]">
          Dirección *
        </label>
        <input
          id="branch-address"
          type="text"
          value={address}
          onChange={(event) => {
            setAddress(event.target.value);
            clearLocalError("commerce_branch.address");
          }}
          className="w-full h-[50px] rounded-[14px] border border-[#E0E0E0] px-4 outline-none focus:ring-2 focus:ring-[#4B236A]/20"
          placeholder="Ej: Calle 10 #5-20"
        />
        {getFieldError("commerce_branch.address") && (
          <p className="text-sm text-red-600">{getFieldError("commerce_branch.address")}</p>
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
