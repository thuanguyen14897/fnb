<?php

namespace App\Http\Resources;

use App\Models\OtherAmenitiesService;
use App\Models\ReviewService;
use App\Models\ReviewServiceDetail;
use App\Models\ReviewServiceImage;
use App\Models\Transaction;
use App\Services\OtherAmenitisService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class Service extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dtImage = !empty($this->image) ? asset('storage/'.$this->image) : null;
        $total_review = ReviewService::where('service_id', $this->id)->count();
        $star = ReviewService::where('service_id', $this->id)->avg('star');
        if (!empty($this->homepage)){
            return [
                'id' => $this->id,
                'name' => $this->name,
                'image' => $dtImage,
                'star' => $star,
                'total_review' => $total_review,
                'distance' => [
                    'distance' => $this->distance
                ],
                'location' => [
                    'address' => $this->address,
                    'province_name' => !empty($this->province) ? $this->province->Name : null,
                    'wards_name' => !empty($this->ward) ? $this->ward->Name : null,
                    'province_id' => $this->province_id,
                    'wards_id' => $this->wards_id,
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                ],
                'other_amenities' => OtherAmenitis::collection($this->whenLoaded('other_amenities')),
            ];
        } else {
            $dtReview = null;
            $dtReviewImage = null;
            if (!empty($this->check_detail)){
                $loadedAmenities = $this->whenLoaded('other_amenities');
                $other_amenities_detail = OtherAmenitiesService::get()->map(function($item) use ($loadedAmenities) {
                    $item->active = $loadedAmenities->contains('id', $item->id);
                    return $item;
                });
                $dtReview = ReviewServiceDetail::select('content', DB::raw('count(*) as total'))->where('service_id', $this->id)->groupBy('content')->get();
                $dtReview = $dtReview->map(function ($item){
                    return [
                        'content' => $item->content,
                        'total' => $item->total,
                    ];
                });
                $dtReviewImage = ReviewServiceImage::select('id','image')->where('service_id', $this->id)->get();
                $dtReviewImage = $dtReviewImage->map(function ($item) {
                    $image_review = !empty($item->image) ? asset('storage/' . $item->image) : null;
                    return [
                        'id' => $item->id,
                        'image' => $image_review,
                    ];
                });
            }
            return [
                'id' => $this->id,
                'name' => $this->name,
                'image' => $dtImage,
                'image_store' => $this->whenLoaded('image_store', function () {
                    $collection = $this->image_store->map(function ($item) {
                        $image = !empty($item->image) ? asset('storage/' . $item->image) : null;
                        return [
                            'id' => $item->id,
                            'image' => $image,
                        ];
                    });

                    $collection->push([
                        'id' => 0,
                        'image' => !empty($this->image) ? asset('storage/' . $this->image) : null,
                    ]);

                    return $collection;
                }),
                'image_menu' =>$this->whenLoaded('image_menu', function () {
                    return $this->image_menu->map(function ($item) {
                        $dtImage = !empty($item->image) ? asset('storage/' . $item->image) : null;
                        return [
                            'id' => $item->id,
                            'image' => $dtImage,
                        ];
                    });
                }),
                'distance' => [
                    'distance' => $this->distance
                ],
                'location' => [
                    'address' => $this->address,
                    'province_name' => !empty($this->province) ? $this->province->Name : null,
                    'wards_name' => !empty($this->ward) ? $this->ward->Name : null,
                    'province_id' => $this->province_id,
                    'wards_id' => $this->wards_id,
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                ],
                'day' => $this->whenLoaded('day',function (){
                    return $this->day->map(function ($item) {
                        return [
                            'code' => $item->day,
                            'name' => getListDay($item->day),
                        ];
                    });
                }),
                'hour' => [
                    'hour_start' => $this->hour_start,
                    'hour_end' => $this->hour_end,
                ],
                'tag_review' => $dtReview,
                'image_review' => $dtReviewImage,
                'star' => $star,
                'total_review' => $total_review,
                'review' => !empty($this->check_detail) ? ReviewResource::collection($this->whenLoaded('review')) : [],
                'price' => $this->price,
                'phone_number' => $this->phone_number,
                'fanpage_facebook' => $this->fanpage_facebook,
                'link_website' => $this->link_website,
                'name_wifi' => $this->name_wifi,
                'pass_wifi' => $this->pass_wifi,
                'detail' => $this->detail,
                'rules' => $this->rules,
                'active' => $this->active,
                'hot' => $this->hot,
                'category_service' => CategoryService::make($this->whenLoaded('category_service')),
                'group_category_service' => GroupCategoryService::make($this->whenLoaded('group_category_service')),
                'other_amenities' => OtherAmenitis::collection($this->whenLoaded('other_amenities')),
                'other_amenities_detail' => !empty($this->check_detail) ? OtherAmenitis::collection($other_amenities_detail) : null,
                'customer' => $this->customer ?? null,
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
