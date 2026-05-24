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
            'bio' => $this->bio,
            'expertise' => $this->expertise
                ? array_values(array_filter(array_map('trim', explode(',', $this->expertise))))
                : [],
            'avatar' => $this->avatar ? asset('storage/'.$this->avatar) : null,
            'linkedin' => $this->linkedin,
        ];
    }
}
