<?php

use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CityController;
use App\Http\Controllers\Api\V1\CommerceBasicDataController;
use App\Http\Controllers\Api\V1\CommerceController;
use App\Http\Controllers\Api\V1\CountryController;
use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\EstablishmentTypeController;
use App\Http\Controllers\Api\V1\LegalRepresentativeController;
use App\Http\Controllers\Api\V1\LogoutController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\NeighborhoodController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Authentication routes
    Route::post('login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {

        // Parametrization routes
        Route::apiResource('countries', CountryController::class);
        Route::apiResource('departments', DepartmentController::class);
        Route::apiResource('cities', CityController::class);
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('neighborhoods', NeighborhoodController::class);
        Route::apiResource('establishment-types', EstablishmentTypeController::class);

        // User Management routes
        Route::apiResource('users', UserController::class);
        Route::patch('users/{user}/status', [UserController::class, 'updateStatus']);

        // Endpoint para obtener usuarios administradores
        Route::get('administrators', [UserController::class, 'administrators']);

        // Endpoint para usuario autenticado
        Route::get('me', [MeController::class, 'authenticatedUser']);
        Route::get('me/permissions', [MeController::class, 'authenticatedUserPermissions']);

        // Endpoint para logout de usuario autenticado
        Route::post('logout', LogoutController::class);

        // Role and Permission Management routes
        Route::get('roles', [RoleController::class, 'index']);
        Route::post('roles', [RoleController::class, 'store']);
        Route::get('roles/{id}', [RoleController::class, 'show']);
        Route::put('roles/{id}', [RoleController::class, 'update']);
        Route::delete('roles/{id}', [RoleController::class, 'destroy']);
        Route::post('users/{user}/assign-roles-permissions', [RoleController::class, 'assignRolesPermissions']);
        Route::post('roles/{role}/assign-permissions', [RoleController::class, 'assignPermissionsToRole']);

        // Endpoint para obtener y crear permisos
        Route::post('permissions', [PermissionController::class, 'store']);
        Route::get('permissions', [PermissionController::class, 'index']);

        // Audit Log routes
        Route::get('audit-logs', [AuditLogController::class, 'index']);
        Route::get('audit-logs/{id}', [AuditLogController::class, 'show']);

        // Commerce routes
        Route::apiResource('commerces', CommerceController::class);
        Route::post('commerces/basic', [CommerceBasicDataController::class, 'store']);

        // Legal Representative routes
        Route::apiResource('legal-representatives', LegalRepresentativeController::class);

    });
});
