<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'date_of_birth' => optional($this->date_of_birth)->toDateString(),
            'city' => $this->city,
            'education_level' => $this->education_level,
            'avatar' => $this->avatar ? asset('storage/'.$this->avatar) : null,
            'bio' => $this->bio,
            'email_verified_at' => $this->email_verified_at,
            'interests' => CategoryResource::collection($this->whenLoaded('interests')),
            'nvo' => new NvoResource($this->whenLoaded('nvo')),
            'created_at' => $this->created_at,
        ];
    }
}
