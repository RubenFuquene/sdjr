// Centraliza los exports principales del panel admin para mantener import paths consistentes
export { DashboardShell } from "./layout/dashboard-shell";

export { ProfilesContent } from "./management/profiles-content";
export { ProfilesFilters } from "./management/profiles-filters";
export { RolesTable } from "./management/roles/roles-table";
export { RolesView } from "./management/roles/roles-view";
export { ProvidersTable } from "./management/providers/providers-table";
export { ProvidersView } from "./management/providers/providers-view";
export { UsersTable } from "./management/users/users-table";
export { AdministratorsTable } from "./management/administrators/administrators-table";
export { AdministratorsView } from "./management/administrators/administrators-view";

export { Badge, StatusBadge } from "./shared/badge";
export { TableActions } from "./shared/table-actions";
export { ErrorState } from "./shared/error-state";
export { TableLoadingState, CardLoadingState } from "./shared/loading-state";
export { PageHeader } from "./shared/page-header";

// Modales
export * from "./modals";
