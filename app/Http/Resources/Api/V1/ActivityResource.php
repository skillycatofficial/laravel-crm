<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
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
            'type' => $this->type,
            'location' => $this->location,
            'comment' => $this->comment,
            'additional' => $this->additional,
            'schedule_from' => $this->schedule_from?->format('Y-m-d H:i:s'),
            'schedule_to' => $this->schedule_to?->format('Y-m-d H:i:s'),
            'is_done' => (bool) $this->is_done,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // User
            'user' => [
                'id' => $this->user->id ?? null,
                'name' => $this->user->name ?? null,
                'email' => $this->user->email ?? null,
                'image_url' => $this->user->image_url ?? null,
            ],
            
            // Participants
            'participants' => $this->whenLoaded('participants', function() {
                return $this->participants->map(function($participant) {
                    return [
                        'id' => $participant->id,
                        'user' => [
                            'id' => $participant->user->id ?? null,
                            'name' => $participant->user->name ?? null,
                            'email' => $participant->user->email ?? null,
                        ],
                        'person' => $participant->person ? [
                            'id' => $participant->person->id,
                            'name' => $participant->person->name,
                            'emails' => $participant->person->emails,
                        ] : null,
                    ];
                });
            }),
            
            // Files
            'files' => $this->whenLoaded('files', function() {
                return $this->files->map(function($file) {
                    return [
                        'id' => $file->id,
                        'name' => $file->name,
                        'path' => $file->path,
                        'size' => $file->size,
                    ];
                });
            }),
            
            // Related entities (counts to avoid pivot table issues)
            'leads_count' => $this->leads_count ?? 0,
            'persons_count' => $this->persons_count ?? 0,
            
            // Full relationships (only when explicitly loaded)
            'leads' => $this->whenLoaded('leads', function() {
                return $this->leads->map(function($lead) {
                    return [
                        'id' => $lead->id,
                        'title' => $lead->title,
                        'status' => $lead->status,
                    ];
                });
            }),
            'persons' => $this->whenLoaded('persons', function() {
                return $this->persons->map(function($person) {
                    return [
                        'id' => $person->id,
                        'name' => $person->name,
                        'emails' => $person->emails,
                    ];
                });
            }),
        ];
    }
}

