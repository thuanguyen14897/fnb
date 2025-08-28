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

class GroupCategoryService extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dtImage = !empty($this->icon) ? env('STORAGE_URL').'/'.$this->icon : null;
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'color_border' => $this->color_border,
            'icon' => $dtImage,
        ];
    }

    public function with($request)
    {
        return [
            'base' => [
                'base' => env('STORAGE_URL'),
            ]
        ];
    }
}
