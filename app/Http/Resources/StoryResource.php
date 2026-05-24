<?php

namespace App\Http\Resources;

use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'image' => Media::url($this->image),
            'created_at' => $this->created_at,
            'author' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => Media::url($this->user->avatar),
                'city' => $this->user->city,
            ]),
            'call' => $this->whenLoaded('call', fn () => [
                'id' => $this->call->id,
                'title' => $this->call->title,
            ]),
        ];
    }
}
