<?php

declare(strict_types=1);

namespace App\Http\Resources\Roles;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'value' => $this->id,
            'created_at' => $this->created_at->toDateString(),
            'updated_at' => $this->created_at->toDateString(),
        ];
    }
}
