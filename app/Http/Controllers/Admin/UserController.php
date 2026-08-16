<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request, string $type)
    {
        abort_unless(in_array($type, ['admins', 'sellers'], true), 404);
        $role = $type === 'admins' ? 'admin' : 'seller';

        $users = User::query()
            ->where('role', $role)
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->string('q')->toString();
                $query->where(fn ($query) => $query->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'type' => $type,
            'title' => $type === 'admins' ? 'Administrativos' : 'Vendedores',
        ]);
    }

    public function create(string $type)
    {
        abort_unless(in_array($type, ['admins', 'sellers'], true), 404);

        return view('admin.users.edit', [
            'userRecord' => new User(['role' => $type === 'admins' ? 'admin' : 'seller', 'active' => true]),
            'type' => $type,
            'title' => $type === 'admins' ? 'Administrativo' : 'Vendedor',
        ]);
    }

    public function store(Request $request, string $type)
    {
        abort_unless(in_array($type, ['admins', 'sellers'], true), 404);

        User::create($this->validated($request, $type, true));

        return redirect()->route('admin.users.index', $type)->with('success', 'Cadastro criado.');
    }

    public function edit(string $type, User $user)
    {
        abort_unless(in_array($type, ['admins', 'sellers'], true), 404);

        return view('admin.users.edit', [
            'userRecord' => $user,
            'type' => $type,
            'title' => $type === 'admins' ? 'Administrativo' : 'Vendedor',
        ]);
    }

    public function update(Request $request, string $type, User $user)
    {
        abort_unless(in_array($type, ['admins', 'sellers'], true), 404);

        $user->update($this->validated($request, $type, false, $user));

        return back()->with('success', 'Cadastro atualizado.');
    }

    public function destroy(string $type, User $user)
    {
        abort_if(auth()->id() === $user->id, 422, 'Você não pode excluir o próprio usuário.');
        $user->delete();

        return redirect()->route('admin.users.index', $type)->with('success', 'Cadastro excluído.');
    }

    private function validated(Request $request, string $type, bool $creating, ?User $user = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'.($user ? ','.$user->id : '')],
            'password' => [$creating ? 'required' : 'nullable', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:80'],
            'mobile' => ['nullable', 'string', 'max:80'],
            'rg' => ['nullable', 'string', 'max:40'],
            'rg_issuer' => ['nullable', 'string', 'max:40'],
            'cpf' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:40'],
        ]);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $validated['role'] = $type === 'admins' ? 'admin' : 'seller';
        $validated['active'] = $request->boolean('active');
        $validated['default_seller'] = $request->boolean('default_seller');
        $validated['can_view_supplier'] = $request->boolean('can_view_supplier');
        $validated['can_view_cost'] = $request->boolean('can_view_cost');
        $validated['can_view_factor'] = $request->boolean('can_view_factor');

        if ($validated['role'] === 'admin') {
            $validated['can_view_supplier'] = true;
            $validated['can_view_cost'] = true;
            $validated['can_view_factor'] = true;
        }

        return $validated;
    }
}
