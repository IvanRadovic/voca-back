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
     * Time-windowed analytics for the NVO (last 3 / 6 / 12 months).
     */
    public function nvoAnalytics(Request $request)
    {
        $userId = $request->user()->id;
        $period = (int) $request->integer('period', 6);
        if (! in_array($period, [3, 6, 12], true)) {
            $period = 6;
        }
        $from = now()->subMonths($period - 1)->startOfMonth();

        $callIdsAll = Call::where('user_id', $userId)->pluck('id');
        $callsInPeriod = Call::where('user_id', $userId)->where('created_at', '>=', $from)->get();

        $apps = Application::whereIn('call_id', $callIdsAll)
            ->where('created_at', '>=', $from)
            ->with('user:id,date_of_birth,city')
            ->get();

        // Monthly series of calls + applications.
        $appsByMonth = $apps->groupBy(fn ($a) => $a->created_at->format('Y-m'));
        $callsByMonth = $callsInPeriod->groupBy(fn ($c) => $c->created_at->format('Y-m'));
        $series = collect(range(0, $period - 1))->map(function ($i) use ($period, $appsByMonth, $callsByMonth) {
            $d = now()->subMonths($period - 1 - $i);
            $key = $d->format('Y-m');

            return [
                'month' => $d->format('M Y'),
                'applications' => optional($appsByMonth->get($key))->count() ?? 0,
                'calls' => optional($callsByMonth->get($key))->count() ?? 0,
            ];
        });

        // Age distribution.
        $buckets = ['15-18' => 0, '19-22' => 0, '23-26' => 0, '27-30' => 0, '31+' => 0, 'unknown' => 0];
        foreach ($apps as $a) {
            $dob = $a->user?->date_of_birth;
            if (! $dob) {
                $buckets['unknown']++;

                continue;
            }
            $age = $dob->age;
            match (true) {
                $age <= 18 => $buckets['15-18']++,
                $age <= 22 => $buckets['19-22']++,
                $age <= 26 => $buckets['23-26']++,
                $age <= 30 => $buckets['27-30']++,
                default => $buckets['31+']++,
            };
        }
        $ageDistribution = collect($buckets)->map(fn ($v, $k) => ['range' => $k, 'count' => $v])->values();

        // City distribution (top 6).
        $cityDistribution = $apps
            ->groupBy(fn ($a) => $a->user?->city ?: 'Unknown')
            ->map->count()
            ->sortDesc()
            ->take(6)
            ->map(fn ($v, $k) => ['city' => $k, 'count' => $v])
            ->values();

        // Top categories among calls in the period.
        $topCategories = DB::table('call_category')
            ->join('categories', 'categories.id', '=', 'call_category.category_id')
            ->whereIn('call_category.call_id', $callsInPeriod->pluck('id'))
            ->select('categories.name', DB::raw('count(*) as count'))
            ->groupBy('categories.name')
            ->orderByDesc('count')
            ->limit(6)
            ->get();

        $byStatus = $apps->groupBy('status')->map->count();

        return response()->json([
            'period' => $period,
            'calls_count' => $callsInPeriod->count(),
            'applications_count' => $apps->count(),
            'by_status' => [
                'pending' => $byStatus->get('pending', 0),
                'accepted' => $byStatus->get('accepted', 0),
                'rejected' => $byStatus->get('rejected', 0),
                'completed' => $byStatus->get('completed', 0),
            ],
            'series' => $series,
            'age_distribution' => $ageDistribution,
            'city_distribution' => $cityDistribution,
            'top_categories' => $topCategories,
            'total_views' => (int) $callsInPeriod->sum('views'),
            'average_rating' => round((float) Feedback::whereIn('call_id', $callIdsAll)
                ->where('created_at', '>=', $from)->avg('rating'), 1),
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
