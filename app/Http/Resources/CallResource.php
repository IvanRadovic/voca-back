<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CallResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'image' => $this->image ? asset('storage/'.$this->image) : null,
            'type' => $this->type,
            'application_deadline' => $this->application_deadline,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'location' => $this->location,
            'is_online' => (bool) $this->is_online,
            'max_participants' => $this->max_participants,
            'price' => (float) $this->price,
            'is_free' => (float) $this->price === 0.0,
            'prerequisites' => $this->prerequisites ?? [],
            'status' => $this->status,
            'views' => $this->views,
            'average_rating' => $this->average_rating,
            'applications_count' => $this->whenCounted('applications'),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'nvo' => $this->whenLoaded('nvo', function () {
                return [
                    'id' => $this->nvo->id,
                    'nvo_id' => optional($this->nvo->nvo)->id,
                    'name' => $this->nvo->name,
                    'organization_name' => optional($this->nvo->nvo)->organization_name ?? $this->nvo->name,
                    'verified' => (bool) optional($this->nvo->nvo)->verified,
                ];
            }),
            // Per-user flags, only when authenticated.
            'is_saved' => $user
                ? $this->whenLoaded('savedByUsers', fn () => $this->savedByUsers->contains('id', $user->id))
                : false,
            'has_applied' => $user
                ? $this->whenLoaded('applications', fn () => $this->applications->contains('user_id', $user->id))
                : false,
            'my_application_status' => $user
                ? $this->whenLoaded('applications', fn () => optional($this->applications->firstWhere('user_id', $user->id))->status)
                : null,
            'created_at' => $this->created_at,
        ];
    }
}
