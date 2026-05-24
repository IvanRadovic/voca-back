<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'issued_at' => $this->issued_at,
            'recipient' => $this->whenLoaded('user', fn () => $this->user->name),
            'call' => $this->whenLoaded('call', fn () => [
                'id' => $this->call->id,
                'title' => $this->call->title,
                'type' => $this->call->type,
            ]),
            'organization' => $this->whenLoaded('call', function () {
                $this->call->loadMissing('nvo.nvo');

                return optional($this->call->nvo->nvo)->organization_name ?? optional($this->call->nvo)->name;
            }),
        ];
    }
}
