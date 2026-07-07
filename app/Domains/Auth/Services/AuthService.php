<?php

namespace App\Domains\Auth\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
        ]);

        $token = $user->createToken('auth')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        if ($user->status !== 'active') {
            throw new AuthenticationException('Account is not active.');
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'companies' => $user->activeMemberships()
                ->with('company:id,uuid,name,logo')
                ->get(['id', 'company_id', 'role_id', 'is_owner', 'status']),
        ];
    }

    public function logout(User $user): void
    {
        if ($token = $user->currentAccessToken()) {
            $token->delete();
        }
    }

    public function profile(User $user): array
    {
        return [
            'user' => $user,
            'companies' => $user->activeMemberships()
                ->with(['company:id,uuid,name,logo', 'role:id,name,slug'])
                ->get(),
        ];
    }
}
