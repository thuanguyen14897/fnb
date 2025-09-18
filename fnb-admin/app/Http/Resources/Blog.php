<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Blog extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $descption = str_replace('src="/storage', 'src="'.asset('/storage').'', htmlspecialchars_decode($this->descption));
        $content = str_replace('src="/storage', 'src="'.asset('/storage').'', htmlspecialchars_decode($this->content));
        return [
            'id' => $this->id,
            'title' => $this->title,
            'descption' => $descption,
            'content' => $content,
            'image' => asset('storage/'.$this->image),
            'created_at' => $this->created_at,
        ];
    }
}
