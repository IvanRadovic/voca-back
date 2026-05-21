<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CallResource;
use App\Models\Call;
use Illuminate\Http\Request;

class SavedCallController extends Controller
{
    /**
     * The authenticated user's wishlist.
     */
    public function index(Request $request)
    {
        $calls = $request->user()
            ->savedCalls()
            ->with(['categories', 'nvo.nvo'])
            ->withAvg('feedbacks', 'rating')
            ->latest('saved_calls.created_at')
            ->paginate($request->integer('per_page', 15));

        return CallResource::collection($calls);
    }

    /**
     * Toggle a call in/out of the wishlist.
     */
    public function toggle(Request $request, Call $call)
    {
        $result = $request->user()->savedCalls()->toggle($call->id);

        return response()->json([
            'saved' => count($result['attached']) > 0,
        ]);
    }
}
