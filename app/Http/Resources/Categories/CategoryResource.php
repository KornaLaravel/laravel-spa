<?php

declare(strict_types=1);

namespace App\Http\Resources\Categories;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'posts' => $this->posts,
            'created_at' => $this->created_at,
            'updated_at' => $this->created_at,
        ];
    }
}
