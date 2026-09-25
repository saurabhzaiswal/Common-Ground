<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ListingResource extends JsonResource
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
            'detail' => $this->detail,
            'price' => $this->price,
            'country' => $this->country,
            'state' => $this->state,
            'city' => $this->city,
            'area' => $this->area,
            'images' => array_map(fn (string $path): array => [
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
            ], $this->images ?? []),
            'created_at' => $this->created_at,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'subcategory' => $this->whenLoaded('subcategory', fn () => $this->subcategory ? [
                'id' => $this->subcategory->id,
                'name' => $this->subcategory->name,
                'slug' => $this->subcategory->slug,
            ] : null),
            'seller' => $this->whenLoaded('user', function () use ($request): array {
                $seller = [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ];

                if ($request->routeIs('listings.show')) {
                    $seller['email'] = $this->user->email;
                }

                return $seller;
            }),
        ];
    }
}
