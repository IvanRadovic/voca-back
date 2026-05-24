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
            'bio' => $this->bio,
            'expertise' => $this->expertise,
            'avatar' => $this->avatar ? asset('storage/'.$this->avatar) : null,
            'email' => $this->email,
            'linkedin' => $this->linkedin,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
