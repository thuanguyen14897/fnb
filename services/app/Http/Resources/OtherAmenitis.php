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

class OtherAmenitis extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dtImage = !empty($this->image) ? asset('storage/'.$this->image) : null;
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon' => $dtImage,
            'active' => $this->active ?? false,
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
