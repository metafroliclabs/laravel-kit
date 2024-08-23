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
        $data['created_at'] = $this->created_at->format('Y-m-d');
        unset($data['updated_at']);

        if (isset($data['user'])) {
            $data = $this->validateUser($data);
        }

        if (isset($data['notifiable_id'])) {
            $data = $this->validateNotification($data);
        }

        return $data;
    }

    protected function validateUser($data)
    {
        $data['user_name']   = $this->user->name;
        $data['user_email']  = $this->user->email;
        $data['user_avatar'] = $this->user->avatar;
        unset($data['user']);

        return $data;
    }

    protected function validateNotification($data)
    {
        $data['time'] = $this->created_at->format('H:i');
        $data['readable_time'] = $this->created_at->diffForHumans();

        return $data;
    }
}
