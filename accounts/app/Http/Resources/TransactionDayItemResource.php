<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TransactionDayItemResource extends JsonResource
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
            'hour' => $this->hour,
            'note' => $this->note,
            'status' => [
                'status' => $this->status,
                'name' => getValueStatusTransactionItem($this->status,'name'),
                'color' => getValueStatusTransactionItem($this->status,'color'),
                'date_status' => $this->date_status,
                'note' => $this->note_status,
            ],
            'service' => $this->service
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
