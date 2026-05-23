<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CallResource;
use App\Models\Call;
use App\Models\Feedback;
use App\Models\Nvo;

class NvoController extends Controller
{
    /**
     * Public organization profile: details, stats and their active calls.
     */
    public function show(Nvo $nvo)
    {
        $nvo->load('user');
        $callIds = Call::where('user_id', $nvo->user_id)->pluck('id');

        $calls = Call::query()
            ->where('user_id', $nvo->user_id)
            ->where('status', Call::STATUS_ACTIVE)
            ->with(['categories', 'nvo.nvo'])
            ->withCount('applications')
            ->withAvg('feedbacks', 'rating')
            ->latest()
            ->get();

        return response()->json([
            'nvo' => [
                'id' => $nvo->id,
                'organization_name' => $nvo->organization_name,
                'description' => $nvo->description,
                'intro_message' => $nvo->intro_message,
                'website' => $nvo->website,
                'verified' => (bool) $nvo->verified,
                'member_since' => $nvo->user->created_at,
            ],
            'stats' => [
                'calls' => $callIds->count(),
                'applications' => \App\Models\Application::whereIn('call_id', $callIds)->count(),
                'average_rating' => round((float) Feedback::whereIn('call_id', $callIds)->avg('rating'), 1),
            ],
            'calls' => CallResource::collection($calls),
        ]);
    }
}
