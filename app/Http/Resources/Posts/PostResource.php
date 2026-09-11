<?php

declare(strict_types=1);

namespace App\Http\Resources\Posts;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // if no resize image
        try {
            $resized_image = $this->getMedia('*')[0]->getUrl('resized-image');
        } catch (Exception $e) {
            $resized_image = '';
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'categories' => $this->categories,
            'content' => $this->content,
            'original_image' => count($this->getMedia('*')) > 0 ? $this->getMedia('*')[0]->getUrl() : null,
            'resized_image' => $resized_image,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
