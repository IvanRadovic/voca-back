<?php

namespace App\Http\Resources;

use App\Models\Call;
use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class MentorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $calls = $this->relationLoaded('calls') ? $this->calls : collect();
        $feedbacks = $calls->flatMap(fn (Call $c) => $c->relationLoaded('feedbacks') ? $c->feedbacks : collect());

        [$past, $upcoming] = $calls->partition(
            fn (Call $c) => $c->status === Call::STATUS_FINISHED || ($c->end_date && $c->end_date->isPast())
        );

        return [
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title,
            'title_en' => $this->title_en,
            'bio' => $this->bio,
            'bio_en' => $this->bio_en,
            'expertise' => $this->expertise
                ? array_values(array_filter(array_map('trim', explode(',', $this->expertise))))
                : [],
            'avatar' => Media::url($this->avatar),
            'linkedin' => $this->linkedin,
            'rating' => $feedbacks->count() ? round($feedbacks->avg('rating'), 1) : null,
            'reviews_count' => $feedbacks->count(),
            'calls' => [
                'upcoming' => $this->mapCalls($upcoming->sortBy('start_date')->values()),
                'past' => $this->mapCalls($past->sortByDesc('end_date')->values()),
            ],
            'reviews' => $feedbacks->sortByDesc('created_at')->take(3)->map(fn ($f) => [
                'rating' => $f->rating,
                'comment' => $f->comment,
                'author' => $f->relationLoaded('user') ? $f->user?->name : null,
            ])->values(),
        ];
    }

    private function mapCalls(Collection $calls): array
    {
        return $calls->map(fn (Call $c) => [
            'id' => $c->id,
            'title' => $c->title,
            'type' => $c->type,
            'start_date' => $c->start_date,
            'end_date' => $c->end_date,
            'location' => $c->location,
            'is_online' => (bool) $c->is_online,
            'status' => $c->status,
        ])->all();
    }
}
