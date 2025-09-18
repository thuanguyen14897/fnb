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
        if (!empty($this->check_list)){
            return [
                'id' => $this->id,
                'transaction' => [
                    'id' => $this->transaction->id,
                    'reference_no' => $this->transaction->reference_no,
                    'date' => $this->transaction->date,
                ],
                'transaction_day' => [
                    'id' => $this->transaction_day->id,
                    'date' => $this->transaction_day->date,
                ],
                'hour' => $this->hour,
                'note' => $this->note,
                'status' => [
                    'status' => $this->status,
                    'name' => getValueStatusTransactionItem($this->status, 'name'),
                    'color' => getValueStatusTransactionItem($this->status, 'color'),
                    'date_status' => $this->date_status,
                    'note' => $this->note_status,
                ],
                'service' => $this->service
            ];
        } else {
            return [
                'id' => $this->id,
                'hour' => $this->hour,
                'note' => $this->note,
                'status' => [
                    'status' => $this->status,
                    'name' => getValueStatusTransactionItem($this->status, 'name'),
                    'color' => getValueStatusTransactionItem($this->status, 'color'),
                    'date_status' => $this->date_status,
                    'note' => $this->note_status,
                ],
                'service' => $this->service
            ];
        }
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
