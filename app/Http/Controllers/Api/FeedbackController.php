<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeedbackResource;
use App\Models\Application;
use App\Models\Call;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Public list of reviews for a call.
     */
    public function index(Call $call)
    {
        return FeedbackResource::collection(
            $call->feedbacks()->with('user')->latest()->get()
        );
    }

    /**
     * Reviews authored by the authenticated user.
     */
    public function mine(Request $request)
    {
        return FeedbackResource::collection(
            Feedback::where('user_id', $request->user()->id)
                ->with('call')
                ->latest()
                ->get()
        );
    }

    /**
     * Leave a review after a completed event.
     */
    public function store(Request $request, Call $call)
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        // Only participants whose application is completed may review.
        $eligible = Application::where('call_id', $call->id)
            ->where('user_id', $request->user()->id)
            ->where('status', Application::STATUS_COMPLETED)
            ->exists();

        abort_unless($eligible, 403, 'You can only review events you have completed.');

        $feedback = Feedback::updateOrCreate(
            ['call_id' => $call->id, 'user_id' => $request->user()->id],
            $data
        );

        return new FeedbackResource($feedback->load('user'));
    }
}
