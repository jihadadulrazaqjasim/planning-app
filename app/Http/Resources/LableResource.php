<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title, 
            'task_id' => $this->task_id,
            'created_at' => $this->created_at->format('d/m/y h:m:s'),
            'updated_at' => $this->updated_at->format('d/m/y h:m:s'),
        ];
    }
}
