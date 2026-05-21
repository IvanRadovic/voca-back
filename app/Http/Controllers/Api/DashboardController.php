<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Call;
use App\Models\Feedback;
use App\Models\Nvo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Aggregate stats for the authenticated NVO dashboard.
     */
    public function nvoStats(Request $request)
    {
        $userId = $request->user()->id;
        $callIds = Call::where('user_id', $userId)->pluck('id');

        return response()->json([
            'calls_count' => $callIds->count(),
            'active_calls' => Call::where('user_id', $userId)->where('status', Call::STATUS_ACTIVE)->count(),
            'applications_count' => Application::whereIn('call_id', $callIds)->count(),
            'pending_applications' => Application::whereIn('call_id', $callIds)
                ->where('status', Application::STATUS_PENDING)->count(),
            'total_views' => (int) Call::where('user_id', $userId)->sum('views'),
            'total_saves' => DB::table('saved_calls')->whereIn('call_id', $callIds)->count(),
            'average_rating' => round((float) Feedback::whereIn('call_id', $callIds)->avg('rating'), 1),
            'recent_feedbacks' => Feedback::whereIn('call_id', $callIds)
                ->with(['user:id,name', 'call:id,title'])
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    /**
     * Public landing-page platform stats.
     */
    public function platformStats()
    {
        return response()->json([
            'nvos' => Nvo::count(),
            'calls' => Call::count(),
            'youth' => User::where('role', User::ROLE_YOUTH)->count(),
            'applications' => Application::count(),
        ]);
    }

    /**
     * Basic admin overview (room for the admin role).
     */
    public function adminStats()
    {
        return response()->json([
            'users' => User::count(),
            'nvos' => Nvo::count(),
            'calls' => Call::count(),
            'applications' => Application::count(),
        ]);
    }
}
