<?php

namespace App\Http\Resources;

use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'message' => $this->message,
            'created_at' => $this->created_at,
            'call' => new CallResource($this->whenLoaded('call')),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'city' => $this->user->city,
                    'education_level' => $this->user->education_level,
                    'age' => $this->user->age,
                    'gender' => $this->user->gender,
                    'headline' => $this->user->headline,
                    'about' => $this->user->about,
                    'education' => $this->user->education,
                    'work_experience' => $this->user->work_experience,
                    'skills' => $this->user->skills,
                    'linkedin' => $this->user->linkedin,
                    'phone' => $this->user->phone,
                    'avatar' => Media::url($this->user->avatar),
                    'interests' => CategoryResource::collection(
                        $this->user->relationLoaded('interests') ? $this->user->interests : collect()
                    ),
                ];
            }),
        ];
    }
}
