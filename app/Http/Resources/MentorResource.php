<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MentorResource extends JsonResource
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
            'expertise' => $this->expertise
                ? array_values(array_filter(array_map('trim', explode(',', $this->expertise))))
                : [],
            'avatar' => $this->avatar ? asset('storage/'.$this->avatar) : null,
            'linkedin' => $this->linkedin,
        ];
    }
}
