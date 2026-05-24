<?php

namespace App\Http\Resources;

use App\Support\Media;
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
            'title_en' => $this->title_en,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'excerpt_en' => $this->excerpt_en,
            'body' => $this->body,
            'body_en' => $this->body_en,
            'cover_image' => Media::url($this->cover_image),
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
