<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class QuoteResource extends JsonResource
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
            'subject' => $this->subject,
            'description' => $this->description,
            'billing_address' => $this->billing_address,
            'shipping_address' => $this->shipping_address,
            'discount_percent' => $this->discount_percent,
            'discount_amount' => $this->discount_amount,
            'tax_amount' => $this->tax_amount,
            'adjustment_amount' => $this->adjustment_amount,
            'sub_total' => $this->sub_total,
            'grand_total' => $this->grand_total,
            'expired_at' => $this->expired_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // Relationships
            'user' => [
                'id' => $this->user->id ?? null,
                'name' => $this->user->name ?? null,
                'email' => $this->user->email ?? null,
            ],
            'person' => $this->when($this->person, [
                'id' => $this->person->id ?? null,
                'name' => $this->person->name ?? null,
                'emails' => $this->person->emails ?? [],
                'contact_numbers' => $this->person->contact_numbers ?? [],
            ]),
            
            // Items
            'items' => $this->whenLoaded('items', function() {
                return $this->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'name' => $item->name,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'amount' => $item->amount,
                        'discount_amount' => $item->discount_amount,
                        'tax_amount' => $item->tax_amount,
                        'total' => $item->total,
                    ];
                });
            }),
            
            // Leads
            'leads' => $this->whenLoaded('leads', function() {
                return $this->leads->map(function($lead) {
                    return [
                        'id' => $lead->id,
                        'title' => $lead->title,
                        'status' => $lead->status,
                        'lead_value' => $lead->lead_value,
                    ];
                });
            }),
        ];
    }
}

