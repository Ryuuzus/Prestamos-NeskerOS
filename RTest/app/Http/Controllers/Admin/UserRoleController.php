<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserRoleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $users = User::query()
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            }))
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        DB::transaction(function () use ($request, $user, $data) {
            // Serializa promociones y degradaciones para no dejar la aplicación sin admins.
            $admins = User::query()->where('role', User::ROLE_ADMIN)
                ->orderBy('id')->lockForUpdate()->get(['id']);
            $target = User::query()->lockForUpdate()->findOrFail($user->id);

            // No permitir degradar la cuenta con la que se está administrando.
            if ($target->is($request->user()) && $data['role'] !== User::ROLE_ADMIN) {
                throw ValidationException::withMessages([
                    'role' => 'No puedes quitarte a ti mismo el rol de administrador.',
                ]);
            }
            if ($target->role === User::ROLE_ADMIN && $data['role'] !== User::ROLE_ADMIN && $admins->count() <= 1) {
                throw ValidationException::withMessages([
                    'role' => 'Debe permanecer por lo menos un administrador.',
                ]);
            }

            $target->role = $data['role'];
            // Compatibilidad con vistas antiguas que aún consulten is_admin.
            $target->is_admin = $data['role'] === User::ROLE_ADMIN;
            $target->save();
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'El rol del usuario se actualizó correctamente.');
    }
}
