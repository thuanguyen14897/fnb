<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class Transaction extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $day = date_range(_dthuan($this->date_start), _dthuan($this->date_end));
        $day = count($day);
        $image_service = [];
        $image_service_new = [];
        if (empty($this->check_detail)) {
            $image_service = $this->whenLoaded('transaction_day_item', function ($items) {
                return $items->map(function ($item) {
                    $images = $item->service['image_store'] ?? [];
                    return collect($images)->pluck('image');
                })->flatten(1)->toArray();
            });

            $service_id = $request->service_id;
            if (!empty($service_id)){
                $image_service_new = $this->whenLoaded('transaction_day_item', function ($items) use ($service_id) {
                    return $items->map(function ($item) use ($service_id) {
                        if ($item->service_id == $service_id) {
                            $images = $item->service['image_store'] ?? [];
                            return collect($images)->pluck('image');
                        }
                    })->filter()->flatten(1)->toArray();
                });
            }
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'reference_no' => $this->reference_no,
            'date' => $this->date,
            'image_service' => $image_service,
            'image_service_new' => $image_service_new,
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'fullname' => $this->customer->fullname,
                    'email' => $this->customer->email,
                    'phone' => $this->customer->phone,
                    'avatar' => !empty($this->customer->avatar) ? env('STORAGE_URL').'/'.$this->customer->avatar : null,
                ];
            }),
            'day' => [
                'day' => $day,
                'date_start' => $this->date_start,
                'date_end' => $this->date_end,
            ],
            'total_service' => count($this->transaction_day_item),
            'transaction_day' => TransactionDayResource::collection($this->whenLoaded('transaction_day')),
            'transaction_day_item' => TransactionDayItemResource::collection($this->whenLoaded('transaction_day_item')),
            'note' => $this->note,
            'status' => [
                'status' => $this->status,
                'name' => getValueStatusTransaction($this->status,'name'),
                'color' => getValueStatusTransaction($this->status,'color'),
                'background' => getValueStatusTransaction($this->status,'background'),
                'date_status' => $this->date_status,
                'note' => $this->note_status,
            ]
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
