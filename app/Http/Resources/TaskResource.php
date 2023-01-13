<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'descriptoin' => $this->description,
            'image' => $this->image,
            'due_date' => $this->due_date,
            'current_status' => $this->current_status,
            'user_id' => $this->user_id,
            'board_id' => $this->board_id,
            'created_at' => $this->created_at->format('d/m/y h:m:s'),
            'updated_at' => $this->updated_at->format('d/m/y h:m:s'),
        ];
    }
}
