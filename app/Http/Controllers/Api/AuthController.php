<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Nvo;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a youth (regular) user.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'city' => ['nullable', 'string', 'max:255'],
            'education_level' => ['nullable', Rule::in(['high_school', 'undergraduate', 'bachelor', 'master'])],
            'avatar' => ['nullable', 'string', 'max:255'],
            'interests' => ['array'],
            'interests.*' => ['integer', 'exists:categories,id'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => User::ROLE_YOUTH,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'city' => $data['city'] ?? null,
                'education_level' => $data['education_level'] ?? null,
                'avatar' => $data['avatar'] ?? null,
            ]);

            if (! empty($data['interests'])) {
                $user->interests()->sync($data['interests']);
            }

            return $user;
        });

        $user->notify(new WelcomeNotification());

        return $this->respondWithToken($user->load('interests'), 201);
    }

    /**
     * Register an NVO (organization) account.
     */
    public function registerNvo(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'organization_name' => ['required', 'string', 'max:255'],
            'pib' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => User::ROLE_NVO,
            ]);

            Nvo::create([
                'user_id' => $user->id,
                'organization_name' => $data['organization_name'],
                'pib' => $data['pib'] ?? null,
                'website' => $data['website'] ?? null,
                'description' => $data['description'] ?? null,
                'intro_message' => 'Welcome to our organization page!',
            ]);

            return $user;
        });

        $user->notify(new WelcomeNotification());

        return $this->respondWithToken($user->load('nvo'), 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $this->respondWithToken($user->load(['interests', 'nvo']));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user()->load(['interests', 'nvo']));
    }

    /**
     * Issue a fresh Sanctum token and return the user payload.
     */
    private function respondWithToken(User $user, int $status = 200)
    {
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ], $status);
    }
}
