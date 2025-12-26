// Centraliza los exports principales del panel admin para mantener import paths consistentes
export { DashboardShell } from "./layout/dashboard-shell";

export { ProfilesContent } from "./management/perfiles/profiles-content";
export { ProfilesFilters } from "./management/perfiles/profiles-filters";
export { ProfilesTable } from "./management/perfiles/profiles-table";
export { ProvidersTable } from "./management/proveedores/providers-table";
export { UsersTable } from "./management/usuarios/users-table";
export { AdministratorsTable } from "./management/administradores/administrators-table";

export { Badge, StatusBadge } from "./shared/badge";
export { TableActions } from "./shared/table-actions";
export { ErrorState } from "./shared/error-state";
export { TableLoadingState, CardLoadingState } from "./shared/loading-state";
