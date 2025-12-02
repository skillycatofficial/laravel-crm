<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'lead_value' => $this->lead_value,
            'status' => $this->status,
            'lost_reason' => $this->lost_reason,
            'expected_close_date' => $this->expected_close_date?->format('Y-m-d'),
            'closed_at' => $this->closed_at?->format('Y-m-d H:i:s'),
            'rotten_days' => $this->rotten_days,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // Relationships
            'user' => [
                'id' => $this->user->id ?? null,
                'name' => $this->user->name ?? null,
                'email' => $this->user->email ?? null,
                'image_url' => $this->user->image_url ?? null,
            ],
            'person' => $this->when($this->person, [
                'id' => $this->person->id ?? null,
                'name' => $this->person->name ?? null,
                'emails' => $this->person->emails ?? [],
                'contact_numbers' => $this->person->contact_numbers ?? [],
                'job_title' => $this->person->job_title ?? null,
                'organization' => [
                    'id' => $this->person->organization->id ?? null,
                    'name' => $this->person->organization->name ?? null,
                ],
            ]),
            'source' => [
                'id' => $this->source->id ?? null,
                'name' => $this->source->name ?? null,
            ],
            'type' => [
                'id' => $this->type->id ?? null,
                'name' => $this->type->name ?? null,
            ],
            'pipeline' => [
                'id' => $this->pipeline->id ?? null,
                'name' => $this->pipeline->name ?? null,
                'rotten_days' => $this->pipeline->rotten_days ?? null,
            ],
            'stage' => [
                'id' => $this->stage->id ?? null,
                'name' => $this->stage->name ?? null,
                'code' => $this->stage->code ?? null,
                'sort_order' => $this->stage->sort_order ?? null,
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
            
            // Products count
            'products_count' => $this->when(isset($this->products_count), $this->products_count),
            'activities_count' => $this->when(isset($this->activities_count), $this->activities_count),
            'quotes_count' => $this->when(isset($this->quotes_count), $this->quotes_count),
        ];
    }
}

