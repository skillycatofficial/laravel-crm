<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // Warehouses
            'warehouses' => $this->whenLoaded('warehouses', function() {
                return $this->warehouses->map(function($warehouse) {
                    return [
                        'id' => $warehouse->id,
                        'name' => $warehouse->name,
                        'description' => $warehouse->description,
                    ];
                });
            }),
            
            // Inventories
            'inventories' => $this->whenLoaded('inventories', function() {
                return $this->inventories->map(function($inventory) {
                    return [
                        'id' => $inventory->id,
                        'quantity' => $inventory->quantity,
                        'warehouse_id' => $inventory->warehouse_id,
                        'location_id' => $inventory->warehouse_location_id,
                    ];
                });
            }),
            
            // Tags
            'tags' => $this->whenLoaded('tags', function() {
                return $this->tags->map(function($tag) {
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'color' => $tag->color,
                    ];
                });
            }),
            
            // Counts
            'activities_count' => $this->when(isset($this->activities_count), $this->activities_count),
        ];
    }
}

