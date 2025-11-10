<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TransactionBillResource extends JsonResource
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
            'reference_no' => $this->reference_no,
            'date' => $this->date,
            'total' => $this->total,
            'discount' => $this->discount,
            'total_discount' => $this->total_discount,
            'grand_total' => $this->grand_total,
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'fullname' => $this->customer->fullname,
                    'email' => $this->customer->email,
                    'phone' => $this->customer->phone,
                    'avatar_new' => !empty($this->customer->avatar) ? env('STORAGE_URL').'/'.$this->customer->avatar : null,
                    'membership' => $this->customer->membership
                ];
            }),
            'partner' => $this->whenLoaded('partner', function () {
                return [
                    'id' => $this->partner->id,
                    'fullname' => $this->partner->fullname,
                    'email' => $this->partner->email,
                    'phone' => $this->partner->phone,
                    'avatar_new' => !empty($this->partner->avatar) ? env('STORAGE_URL').'/'.$this->partner->avatar : null,
                ];
            }),
            'service' => $this->service,
            'payment' => $this->payment,
            'note' => $this->note,
            'status' => [
                'id' => $this->status,
                'name' => getValueStatusTransactionBill($this->status,'name'),
                'color' => getValueStatusTransactionBill($this->status,'color'),
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
