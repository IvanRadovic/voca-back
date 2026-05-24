<?php

namespace App\Support;

/**
 * Pure scoring logic for the youth gamification system.
 * Points and badges are derived from activity counts (no stored state).
 */
class Gamification
{
    public const POINTS = [
        'application' => 10,
        'completed' => 15,   // extra on top of the application points
        'review' => 15,
        'certificate' => 20,
    ];

    // Cumulative point thresholds for each level (index = level - 1).
    public const LEVELS = [0, 50, 150, 300, 500, 800, 1200];

    /** @param array{applications:int,completed:int,reviews:int,certificates:int} $c */
    public static function points(array $c): int
    {
        return $c['applications'] * self::POINTS['application']
            + $c['completed'] * self::POINTS['completed']
            + $c['reviews'] * self::POINTS['review']
            + $c['certificates'] * self::POINTS['certificate'];
    }

    /**
     * @return array{level:int,points:int,current_floor:int,next_threshold:int|null,progress:int}
     */
    public static function level(int $points): array
    {
        $level = 1;
        foreach (self::LEVELS as $i => $threshold) {
            if ($points >= $threshold) {
                $level = $i + 1;
            }
        }
        $floor = self::LEVELS[$level - 1];
        $next = self::LEVELS[$level] ?? null;
        $progress = $next === null ? 100 : (int) round((($points - $floor) / ($next - $floor)) * 100);

        return [
            'level' => $level,
            'points' => $points,
            'current_floor' => $floor,
            'next_threshold' => $next,
            'progress' => $progress,
        ];
    }

    /**
     * @param array{applications:int,completed:int,reviews:int,certificates:int} $c
     * @return array<int,array{key:string,earned:bool}>
     */
    public static function badges(array $c): array
    {
        $defs = [
            ['key' => 'first_step', 'earned' => $c['applications'] >= 1],
            ['key' => 'active', 'earned' => $c['applications'] >= 5],
            ['key' => 'veteran', 'earned' => $c['applications'] >= 10],
            ['key' => 'finisher', 'earned' => $c['completed'] >= 3],
            ['key' => 'reviewer', 'earned' => $c['reviews'] >= 3],
            ['key' => 'certified', 'earned' => $c['certificates'] >= 1],
        ];

        return $defs;
    }
}
