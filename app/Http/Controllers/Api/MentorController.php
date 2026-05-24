<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MentorResource;
use App\Models\Mentor;
use App\Models\MentorshipRequest;
use App\Notifications\MentorshipRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

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
}
