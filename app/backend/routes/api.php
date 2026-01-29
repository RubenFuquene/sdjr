<?php

use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BankController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CityController;
use App\Http\Controllers\Api\V1\CommerceBasicDataController;
use App\Http\Controllers\Api\V1\CommerceBranchController;
use App\Http\Controllers\Api\V1\CommerceController;
use App\Http\Controllers\Api\V1\CountryController;
use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\DocumentUploadController;
use App\Http\Controllers\Api\V1\EstablishmentTypeController;
use App\Http\Controllers\Api\V1\ForgotPasswordController;
use App\Http\Controllers\Api\V1\LegalDocumentController;
use App\Http\Controllers\Api\V1\LegalRepresentativeController;
use App\Http\Controllers\Api\V1\LogoutController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\NeighborhoodController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\PqrsTypeController;
use App\Http\Controllers\Api\V1\PriorityTypeController;
use App\Http\Controllers\Api\V1\ProductCategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProviderRegisterController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\SupportStatusController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Registro público de proveedores
    Route::post('provider/register', [ProviderRegisterController::class, '__invoke']);

    // Authentication routes
    Route::post('login', [AuthController::class, 'login']);

    // Password forgot endpoint
    Route::post('password/forgot', [ForgotPasswordController::class, 'forgot']);
    Route::post('password/reset', [ForgotPasswordController::class, 'reset']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {

        // Support Status Management routes
        Route::apiResource('support-statuses', SupportStatusController::class);

        // Bank Management routes
        Route::apiResource('banks', BankController::class);

        // Parametrization routes
        Route::apiResource('countries', CountryController::class);
        Route::apiResource('departments', DepartmentController::class);
        Route::apiResource('cities', CityController::class);
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('neighborhoods', NeighborhoodController::class);
        Route::apiResource('establishment-types', EstablishmentTypeController::class);
        Route::apiResource('pqrs-types', PqrsTypeController::class);
        Route::apiResource('priority-types', PriorityTypeController::class);

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
        Route::patch('roles/{id}/status', [RoleController::class, 'patchStatus']);
        Route::post('roles/{role}/assign-permissions', [RoleController::class, 'assignPermissionsToRole']);

        // Endpoint para obtener y crear permisos
        Route::post('permissions', [PermissionController::class, 'store']);
        Route::get('permissions', [PermissionController::class, 'index']);

        // Audit Log routes
        Route::get('audit-logs', [AuditLogController::class, 'index']);
        Route::get('audit-logs/{id}', [AuditLogController::class, 'show']);

        // Commerce routes
        Route::apiResource('commerces', CommerceController::class);
        Route::patch('commerces/{id}/status', [CommerceController::class, 'patchStatus']);
        Route::patch('commerces/{id}/verification', [CommerceController::class, 'patchVerification']);
        Route::get('commerces/{commerce_id}/branches', [CommerceController::class, 'getBranchesByCommerceId']);
        Route::get('commerces/{commerce_id}/payout-methods', [CommerceController::class, 'getPayoutMethodsByCommerceId']);
        Route::post('commerces/basic', [CommerceBasicDataController::class, 'store']);

        // Legal Documents
        Route::get('legal-documents', [LegalDocumentController::class, 'index']);
        Route::get('legal-documents/{type}', [LegalDocumentController::class, 'showByType']);

        // Commerce Branches
        Route::apiResource('commerce-branches', CommerceBranchController::class);

        // Legal Representative routes
        Route::apiResource('legal-representatives', LegalRepresentativeController::class);

        // Document Upload Endpoints
        Route::prefix('documents')->group(function () {
            Route::post('/presigned', [DocumentUploadController::class, 'presigned']);
            Route::post('/confirm', [DocumentUploadController::class, 'confirm']);
        });

        // Product Management routes
        Route::apiResource('products', ProductController::class);

        Route::prefix('products/commerce')->group(function () {
            Route::get('{commerce_id}', [ProductController::class, 'byCommerce']);
            Route::get('branch/{branch_id}', [ProductController::class, 'byCommerceBranch']);
            Route::post('/', [ProductController::class, 'store']);
            Route::put('/{id}', [ProductController::class, 'update']);
            Route::delete('/{commerce_id}', [ProductController::class, 'deleted']);
            Route::post('package-items', [ProductController::class, 'storePackageItems']);
            Route::put('package-items/{id}', [ProductController::class, 'updatePackageItems']);
        });

        Route::apiResource('product-categories', ProductCategoryController::class);
    });
});
