export interface ProviderBranchScheduleItem {
  dayOfWeek: number;
  dayLabel: string;
  openTime: string;
  closeTime: string;
}

export interface ProviderBranchCardViewModel {
  id: number;
  commerceId: number;
  name: string;
  fullAddress: string;
  latitude: number | null;
  longitude: number | null;
  phone: string;
  email: string | null;
  isActive: boolean;
  photosCount: number;
  coverImageUrl: string | null;
  scheduleText: string;
  scheduleItems: ProviderBranchScheduleItem[];
  createdAt: string;
  updatedAt: string;
}
