<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
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
            'emails' => $this->emails,
            'contact_numbers' => $this->contact_numbers,
            'job_title' => $this->job_title,
            'unique_id' => $this->unique_id,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // Relationships
            'organization' => $this->when($this->organization, [
                'id' => $this->organization->id ?? null,
                'name' => $this->organization->name ?? null,
                'address' => $this->organization->address ?? null,
            ]),
            'user' => [
                'id' => $this->user->id ?? null,
                'name' => $this->user->name ?? null,
                'email' => $this->user->email ?? null,
            ],
            
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
            'leads_count' => $this->when(isset($this->leads_count), $this->leads_count),
            'activities_count' => $this->when(isset($this->activities_count), $this->activities_count),
        ];
    }
}

