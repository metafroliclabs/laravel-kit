<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneralResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
    
        if (!empty($data['image'])) {
            $data['image'] = asset($data['image']);
        }
    
        if (!empty($data['file'])) {
            $data['file'] = asset($data['file']);
        }
    
        unset($data['updated_at']);
    
        return $data;
    }
}
