<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $service = $this->service ?? null;
        $dtService = null;
        if (!empty($service)){
            $dtService = [
                'id' => $service['id'],
                'name' => $service['name'],
                'image' => $service['image'],
            ];
        }
        if ($this->check_rose == 1){
            return [
                'id' => $this->id,
                'reference_no' => $this->reference_no,
                'date' => $this->date,
                'payment' => $this->payment,
                'revenue_partner' => $this->revenue_partner,
                'percent_partner' => $this->percent_partner,
                'customer' => [
                    'id' => $this->cus_id,
                    'fullname' => $this->cus_fullname,
                    'phone' => $this->cus_phone,
                    'avatar' => $this->cus_avatar,
                ],
                'service' => $dtService,
            ];
        } else {
            return [
                'id' => $this->id,
                'reference_no' => $this->reference_no,
                'date' => $this->date,
                'payment' => $this->payment,
                'customer' => $this->whenLoaded('customer'),
                'transaction_bill' => $this->whenLoaded('transaction_bill', function () {
                    return [
                        'id' => $this->transaction_bill->id,
                        'reference_no' => $this->transaction_bill->reference_no,
                    ];
                }),
                'service' => $dtService,
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
