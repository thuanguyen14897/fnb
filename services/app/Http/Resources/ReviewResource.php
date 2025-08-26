<?php

namespace App\Http\Resources;

use App\Models\PriceMonthCarDetail;
use App\Models\ReviewCar;
use App\Models\ServiceCar;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'star' => $this->star,
            'content' => $this->content,
            'date' => to_sql_date($this->created_at,true),
            'item' => $this->detail->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->content,
                ];
            }),
            'image' => $this->image->map(function ($item) {
                $image = !empty($item->image) ? asset('storage/' . $item->image) : null;
                return [
                    'id' => $item->id,
                    'name' => $image,
                ];
            }),
            'customer' => $this->customer,
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
