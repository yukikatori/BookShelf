<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'published_date' => $this->published_date,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'created_at' => $this->created_at,
            'user_id' => $this->user_id,
            'genres' => GenreResource::collection($this->genres),
            'avg_rating' => $this->reviews_avg_rating
                ? round($this->reviews_avg_rating, 1)
                : null,
            'reviews_count' => $this->reviews_count,
        ];
    }
}
