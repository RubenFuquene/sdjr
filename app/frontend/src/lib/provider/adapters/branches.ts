import type {
  CommerceBranchFromAPI,
  CommerceBranchHourFromAPI,
  CommerceBranchPhotoFromAPI,
} from "@/lib/api/branches";
import type {
  ProviderBranchCardViewModel,
  ProviderBranchScheduleItem,
} from "@/types/provider-branches";

const DAY_LABELS: Record<number, string> = {
  0: "Domingo",
  1: "Lunes",
  2: "Martes",
  3: "Miércoles",
  4: "Jueves",
  5: "Viernes",
  6: "Sábado",
};

function normalizeDayOfWeek(value: number | string): number | null {
  const numericValue = typeof value === "string" ? Number(value) : value;
  if (!Number.isInteger(numericValue) || numericValue < 0 || numericValue > 6) {
    return null;
  }

  return numericValue;
}

function toScheduleItems(hours: CommerceBranchHourFromAPI[] = []): ProviderBranchScheduleItem[] {
  return hours
    .map((hour) => {
      const dayOfWeek = normalizeDayOfWeek(hour.day_of_week);

      if (dayOfWeek === null) {
        return null;
      }

      return {
        dayOfWeek,
        dayLabel: DAY_LABELS[dayOfWeek],
        openTime: hour.open_time,
        closeTime: hour.close_time,
      };
    })
    .filter((item): item is ProviderBranchScheduleItem => item !== null)
    .sort((first, second) => first.dayOfWeek - second.dayOfWeek);
}

export function formatBranchSchedule(hours: CommerceBranchHourFromAPI[] = []): string {
  const items = toScheduleItems(hours);
  if (items.length === 0) {
    return "Sin horario definido";
  }

  return items
    .map((item) => `${item.dayLabel}: ${item.openTime}-${item.closeTime}`)
    .join(", ");
}

function isAbsoluteUrl(value: string): boolean {
  return /^https?:\/\//i.test(value);
}

function resolvePhotoUrl(photo?: CommerceBranchPhotoFromAPI): string | null {
  if (!photo) {
    return null;
  }

  if (photo.url && isAbsoluteUrl(photo.url)) {
    return photo.url;
  }

  if (photo.file_path && isAbsoluteUrl(photo.file_path)) {
    return photo.file_path;
  }

  return null;
}

export function getBranchCoverImage(photos: CommerceBranchPhotoFromAPI[] = []): string | null {
  if (photos.length === 0) {
    return null;
  }

  return resolvePhotoUrl(photos[0]);
}

export function commerceBranchToCardViewModel(
  branch: CommerceBranchFromAPI
): ProviderBranchCardViewModel {
  const fullAddress = [branch.address, branch.neighborhood, branch.city]
    .filter((value): value is string => Boolean(value && value.trim().length > 0))
    .join(", ");

  const phone = branch.phone?.trim() || "Sin teléfono";
  const photos = branch.photos ?? [];
  const hours = branch.hours ?? [];

  return {
    id: branch.id,
    commerceId: branch.commerce_id,
    name: branch.name,
    fullAddress: fullAddress || branch.address,
    phone,
    email: branch.email,
    isActive: branch.is_active,
    photosCount: photos.length,
    coverImageUrl: getBranchCoverImage(photos),
    scheduleText: formatBranchSchedule(hours),
    scheduleItems: toScheduleItems(hours),
    createdAt: branch.created_at,
    updatedAt: branch.updated_at,
  };
}

export function commerceBranchesToCardViewModels(
  branches: CommerceBranchFromAPI[] = []
): ProviderBranchCardViewModel[] {
  return branches.map(commerceBranchToCardViewModel);
}
