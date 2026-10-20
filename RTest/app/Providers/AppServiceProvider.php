<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Estos permisos se aplican tanto al sidebar como a rutas HTTP.
        // SOLO el rol user puede solicitar, editar o cancelar sus propias reservaciones.
        Gate::define('manage-own-reservations', fn (User $user) =>
            $user->hasRole(User::ROLE_USER));

        Gate::define('review-reservations', fn (User $user) =>
            $user->hasAnyRole(User::ROLE_MODERATOR, User::ROLE_ADMIN));

        Gate::define('view-catalogs', fn (User $user) =>
            $user->hasAnyRole(User::ROLE_MODERATOR, User::ROLE_ADMIN));

        Gate::define('manage-catalogs', fn (User $user) =>
            $user->hasRole(User::ROLE_ADMIN));

        Gate::define('view-history', fn (User $user) =>
            $user->hasAnyRole(User::ROLE_MODERATOR, User::ROLE_ADMIN));

        // Excel queda reservado al administrador, incluso si se escribe su URL manualmente.
        Gate::define('export-data', fn (User $user) =>
            $user->hasRole(User::ROLE_ADMIN));

        Gate::define('import-data', fn (User $user) =>
            $user->hasRole(User::ROLE_ADMIN));

        // La exportación masiva histórica se limita al administrador.
        // Los recibos individuales se autorizan por propietario o personal de moderación.
        Gate::define('export-pdf', fn (User $user) =>
            $user->hasRole(User::ROLE_ADMIN));

        Gate::define('view-receipt', fn (User $user, \App\Models\Reservation $reservation) =>
            ($reservation->user_id === $user->getKey() || $user->can('review-reservations'))
            && in_array($reservation->status, [
                \App\Models\Reservation::STATUS_APPROVED,
                \App\Models\Reservation::STATUS_COMPLETED,
            ], true));

        Gate::define('manage-users', fn (User $user) =>
            $user->hasRole(User::ROLE_ADMIN));
    }
}
