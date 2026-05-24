<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full mentor representation for the admin panel (includes contact + status).
 */
class MentorAdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title,
            'title_en' => $this->title_en,
            'bio' => $this->bio,
            'bio_en' => $this->bio_en,
            'expertise' => $this->expertise,
            'avatar' => $this->avatar ? asset('storage/'.$this->avatar) : null,
            'email' => $this->email,
            'linkedin' => $this->linkedin,
            'is_active' => (bool) $this->is_active,
            'call_ids' => $this->relationLoaded('calls') ? $this->calls->pluck('id') : [],
            'created_at' => $this->created_at,
        ];
    }
}
