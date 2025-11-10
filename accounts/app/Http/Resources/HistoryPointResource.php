<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class HistoryPointResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $title = '';
        if ($this->object_type == 'payment'){
            $title = 'Giới thiệu bạn bè';
        } elseif ($this->object_type == 'point_payment'){
            $title = 'Chi tiêu trong quý';
        } elseif ($this->object_type == 'point_purchase'){
            $title = 'Số lần mua hàng trong quý';
        } elseif ($this->object_type == 'point_long_term'){
            $title = 'Thời gian gắn bó';
        } elseif ($this->object_type == 'reset_point'){
            $title = 'Reset điểm cuối quý';
        }
        $storageUrl = config('app.storage_url');
        return [
            'id' => $this->id,
            'title' => $title,
            'customer' => [
                'id' => $this->customer->id,
                'fullname' => $this->customer->fullname,
                'avatar' => !empty($this->customer->avatar) ? $storageUrl.'/'.$this->customer->avatar : null,
            ],
            'type_check' => $this->type_check,
            'created_at' => $this->created_at,
            'point' => $this->point,
        ];
    }

    public function with($request)
    {
        return [
            'base' => [
                'base' => asset('storage'),
            ]
        ];
    }
}
