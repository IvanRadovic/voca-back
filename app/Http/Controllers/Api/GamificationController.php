<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\User;
use App\Support\Gamification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GamificationController extends Controller
{
    /**
     * Points, level and badges for the authenticated user.
     */
    public function me(Request $request)
    {
        $counts = $this->countsFor($request->user()->id);
        $points = Gamification::points($counts);

        return response()->json([
            'counts' => $counts,
            'level' => Gamification::level($points),
            'badges' => Gamification::badges($counts),
        ]);
    }

    /**
     * Public leaderboard of youth users by points (optional city filter).
     */
    public function leaderboard(Request $request)
    {
        $city = $request->string('city')->value();

        // Aggregate activity counts per youth user in a few grouped queries.
        $applications = DB::table('applications')
            ->select('user_id', DB::raw('count(*) as total'),
                DB::raw("sum(case when status = 'completed' then 1 else 0 end) as completed"))
            ->groupBy('user_id')->pluck('total', 'user_id');
        $completed = DB::table('applications')->where('status', 'completed')
            ->select('user_id', DB::raw('count(*) as total'))->groupBy('user_id')->pluck('total', 'user_id');
        $reviews = DB::table('feedbacks')
            ->select('user_id', DB::raw('count(*) as total'))->groupBy('user_id')->pluck('total', 'user_id');
        $certificates = DB::table('certificates')
            ->select('user_id', DB::raw('count(*) as total'))->groupBy('user_id')->pluck('total', 'user_id');

        $users = User::where('role', User::ROLE_YOUTH)
            ->when($city, fn ($q) => $q->where('city', $city))
            ->get(['id', 'name', 'city']);

        $ranked = $users->map(function ($u) use ($applications, $completed, $reviews, $certificates) {
            $counts = [
                'applications' => (int) ($applications[$u->id] ?? 0),
                'completed' => (int) ($completed[$u->id] ?? 0),
                'reviews' => (int) ($reviews[$u->id] ?? 0),
                'certificates' => (int) ($certificates[$u->id] ?? 0),
            ];
            $points = Gamification::points($counts);

            return [
                'name' => $u->name,
                'city' => $u->city,
                'points' => $points,
                'level' => Gamification::level($points)['level'],
            ];
        })
            ->sortByDesc('points')
            ->values()
            ->take(20)
            ->map(fn ($row, $i) => ['rank' => $i + 1, ...$row]);

        return response()->json(['data' => $ranked]);
    }

    /** @return array{applications:int,completed:int,reviews:int,certificates:int} */
    private function countsFor(int $userId): array
    {
        return [
            'applications' => Application::where('user_id', $userId)->count(),
            'completed' => Application::where('user_id', $userId)->where('status', 'completed')->count(),
            'reviews' => DB::table('feedbacks')->where('user_id', $userId)->count(),
            'certificates' => DB::table('certificates')->where('user_id', $userId)->count(),
        ];
    }
}
