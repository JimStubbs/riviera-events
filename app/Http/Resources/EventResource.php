<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'slug'        => $this->slug,
            'description' => $this->description,
            'excerpt'     => mb_strimwidth(html_entity_decode(strip_tags($this->description ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'), 0, 160, '…'),
            'start_date'  => $this->start_date?->toIso8601String(),
            'end_date'    => $this->end_date?->toIso8601String(),
            'organizer'   => $this->organizer,
            'website'     => $this->website,
            'is_premium'  => $this->is_premium,
            'is_all_day'  => $this->is_all_day,
            'views_count' => $this->views_count,
            'image_url'   => $this->image_url,
            'location'    => $this->whenLoaded('location', fn () => [
                'id'   => $this->location->id,
                'name' => $this->location->name,
                'city' => $this->location->city,
            ]),
            'category'    => $this->whenLoaded('category', fn () => [
                'id'    => $this->category->id,
                'name'  => $this->category->name,
                'color' => $this->category->color,
                'icon'  => $this->category->icon,
            ]),
        ];
    }
}
