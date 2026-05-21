<?php

namespace App\Http\Resources;

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
                    'interests' => CategoryResource::collection(
                        $this->user->relationLoaded('interests') ? $this->user->interests : collect()
                    ),
                ];
            }),
        ];
    }
}
