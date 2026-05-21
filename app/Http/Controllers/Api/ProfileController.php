<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Update the authenticated user's profile and interests.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'education_level' => ['nullable', Rule::in(['high_school', 'undergraduate', 'bachelor', 'master'])],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'max:5120'], // 5MB
            'interests' => ['sometimes', 'array'],
            'interests.*' => ['integer', 'exists:categories,id'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->fill(collect($data)->except('interests')->toArray());
        $user->save();

        if ($request->has('interests')) {
            $user->interests()->sync($data['interests'] ?? []);
        }

        return new UserResource($user->fresh(['interests', 'nvo']));
    }

    /**
     * Update the NVO organization details / intro message.
     */
    public function updateNvo(Request $request)
    {
        $user = $request->user();
        $nvo = $user->nvo;

        abort_unless($nvo, 404, 'NVO profile not found.');

        $data = $request->validate([
            'organization_name' => ['sometimes', 'string', 'max:255'],
            'pib' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'intro_message' => ['nullable', 'string', 'max:2000'],
        ]);

        $nvo->update($data);

        return new UserResource($user->fresh(['nvo']));
    }
}
