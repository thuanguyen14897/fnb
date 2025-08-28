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
        $image_service = null;
        if (empty($this->check_detail)) {
            $image_service = $this->whenLoaded('transaction_day_item', function () {
                $collection = $this->transaction_day_item->map(function ($item) {
                    $service = $item->service;
                    return [
                        'service_id' => $service['id'],
                        'image' => $service['image_store'],
                    ];
                });
                return $collection;
            });
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'reference_no' => $this->reference_no,
            'date' => $this->date,
            'image_service' => $image_service,
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
            'transaction_day_item' => TransactionDayItemResource::collection($this->whenLoaded('transaction_day_item')),
            'note' => $this->note,
            'status' => [
                'status' => $this->status,
                'name' => getValueStatusTransaction($this->status,'name'),
                'color' => getValueStatusTransaction($this->status,'color'),
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
