<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\User\ResetUserPasswordRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // ---------------------------------------------------------------
    // GET /users
    // ---------------------------------------------------------------

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $actor = $request->user();

        $users = User::query()
            ->with('company')
            ->when($actor->isCompanyAdmin(), fn ($q) => $q->where('company_id', $actor->company_id))
            ->orderBy('name')
            ->get();

        return view('users.index', compact('users'));
    }

    // ---------------------------------------------------------------
    // GET /users/create
    // ---------------------------------------------------------------

    public function create(Request $request)
    {
        $this->authorize('create', User::class);

        $actor     = $request->user();
        $companies = $actor->isHoldingAdmin()
            ? Company::orderBy('name')->get()
            : Company::where('id', $actor->company_id)->get();

        $roles = UserRole::cases();

        return view('users.create', compact('companies', 'roles'));
    }

    // ---------------------------------------------------------------
    // POST /users
    // ---------------------------------------------------------------

    public function store(StoreUserRequest $request)
    {
        $actor = $request->user();

        // company_admin always assigns to their own company.
        $companyId = $actor->isHoldingAdmin()
            ? $request->validated('company_id')
            : $actor->company_id;

        User::create([
            'name'       => $request->validated('name'),
            'email'      => $request->validated('email'),
            'password'   => $request->validated('password'),
            'role'       => $request->validated('role'),
            'company_id' => $companyId,
            'is_active'  => true,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    // ---------------------------------------------------------------
    // GET /users/{user}
    // ---------------------------------------------------------------

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return view('users.show', compact('user'));
    }

    // ---------------------------------------------------------------
    // GET /users/{user}/edit
    // ---------------------------------------------------------------

    public function edit(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $actor     = $request->user();
        $companies = $actor->isHoldingAdmin()
            ? Company::orderBy('name')->get()
            : Company::where('id', $actor->company_id)->get();

        $roles = UserRole::cases();

        return view('users.edit', compact('user', 'companies', 'roles'));
    }

    // ---------------------------------------------------------------
    // PUT /users/{user}
    // ---------------------------------------------------------------

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());

        return redirect()->route('users.show', $user)
            ->with('success', 'Data user berhasil diperbarui.');
    }

    // ---------------------------------------------------------------
    // POST /users/{user}/activate
    // Toggles is_active flag.
    // ---------------------------------------------------------------

    public function activate(Request $request, User $user)
    {
        $this->authorize('activate', $user);

        $user->update(['is_active' => ! $user->is_active]);

        $label = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('users.show', $user)
            ->with('success', "User berhasil {$label}.");
    }

    // ---------------------------------------------------------------
    // POST /users/{user}/reset-password
    // ---------------------------------------------------------------

    public function resetPassword(ResetUserPasswordRequest $request, User $user)
    {
        $user->update(['password' => $request->validated('password')]);

        return redirect()->route('users.show', $user)
            ->with('success', 'Password berhasil direset.');
    }
}
