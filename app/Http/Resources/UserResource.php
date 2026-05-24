<?php

namespace App\Http\Resources;

use App\Support\Media;
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
            'avatar' => Media::url($this->avatar),
            'bio' => $this->bio,
            'headline' => $this->headline,
            'about' => $this->about,
            'education' => $this->education,
            'work_experience' => $this->work_experience,
            'skills' => $this->skills,
            'linkedin' => $this->linkedin,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'age' => $this->age,
            'email_verified_at' => $this->email_verified_at,
            'interests' => CategoryResource::collection($this->whenLoaded('interests')),
            'nvo' => new NvoResource($this->whenLoaded('nvo')),
            'created_at' => $this->created_at,
        ];
    }
}
