<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MentorAdminResource;
use App\Http\Resources\MentorResource;
use App\Models\Mentor;
use App\Models\MentorshipRequest;
use App\Notifications\MentorshipRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MentorController extends Controller
{
    public function index(Request $request)
    {
        $mentors = Mentor::where('is_active', true)
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($sub) => $sub->where('name', 'like', $term)
                    ->orWhere('title', 'like', $term)
                    ->orWhere('expertise', 'like', $term));
            })
            ->orderBy('name')
            ->get();

        return MentorResource::collection($mentors);
    }

    public function show(Mentor $mentor)
    {
        abort_unless($mentor->is_active, 404);

        return new MentorResource($mentor);
    }

    /**
     * Youth requests a mentoring conversation.
     */
    public function requestSession(Request $request, Mentor $mentor)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        MentorshipRequest::create([
            'mentor_id' => $mentor->id,
            'user_id' => $request->user()->id,
            'message' => $data['message'],
        ]);

        // Notify the mentor by email if we have an address (queued).
        if ($mentor->email) {
            Notification::route('mail', $mentor->email)
                ->notify(new MentorshipRequestNotification($request->user(), $data['message']));
        }

        return response()->json(['message' => 'Your request has been sent.']);
    }

    /**
     * Public application to become a mentor — created inactive (pending review).
     */
    public function apply(Request $request)
    {
        $data = $this->validateMentor($request);
        $data['is_active'] = false;

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('mentors', 'public');
        }

        Mentor::create($data);

        return response()->json(['message' => 'Your application has been submitted for review.'], 201);
    }

    /* -------------------- Admin -------------------- */

    public function adminIndex()
    {
        return MentorAdminResource::collection(Mentor::latest()->get());
    }

    public function store(Request $request)
    {
        $data = $this->validateMentor($request);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('mentors', 'public');
        }

        return new MentorAdminResource(Mentor::create($data));
    }

    public function update(Request $request, Mentor $mentor)
    {
        $data = $this->validateMentor($request, true);
        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        if ($request->hasFile('avatar')) {
            if ($mentor->avatar) {
                Storage::disk('public')->delete($mentor->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('mentors', 'public');
        }

        $mentor->update($data);

        return new MentorAdminResource($mentor->fresh());
    }

    public function destroy(Mentor $mentor)
    {
        if ($mentor->avatar) {
            Storage::disk('public')->delete($mentor->avatar);
        }
        $mentor->delete();

        return response()->json(['message' => 'Mentor deleted.']);
    }

    private function validateMentor(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'name' => [$required, 'string', 'max:255'],
            'title' => [$required, 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'expertise' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:5120'],
        ]);
    }
}
