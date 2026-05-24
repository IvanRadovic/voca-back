<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'cover_image' => $this->cover_image ? asset('storage/'.$this->cover_image) : null,
            'published_at' => $this->published_at,
            'author' => $this->whenLoaded('author', fn () => [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ]),
            'can_edit' => $request->user()
                ? ($request->user()->id === $this->author_id || $request->user()->isAdmin())
                : false,
        ];
    }
}
