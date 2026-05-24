<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'image' => $this->image ? asset('storage/'.$this->image) : null,
            'created_at' => $this->created_at,
            'author' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => $this->user->avatar ? asset('storage/'.$this->user->avatar) : null,
                'city' => $this->user->city,
            ]),
            'call' => $this->whenLoaded('call', fn () => [
                'id' => $this->call->id,
                'title' => $this->call->title,
            ]),
        ];
    }
}
