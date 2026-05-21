<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NvoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'organization_name' => $this->organization_name,
            'pib' => $this->pib,
            'website' => $this->website,
            'description' => $this->description,
            'intro_message' => $this->intro_message,
            'verified' => (bool) $this->verified,
        ];
    }
}
