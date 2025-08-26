<?php

namespace App\Http\Resources;

use App\Models\ModuleNoti;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Notification extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $json_data = json_decode($this->json_data);
        $icon = null;
        $banner = null;
        if ($this->object_type == 401) {
            if (!empty($json_data)) {
                $module_noti_id = $json_data->module_noti_id;
                $dtModuleNoti = ModuleNoti::find($module_noti_id);
                $icon = !empty($dtModuleNoti->image) ? asset('storage/'.$dtModuleNoti->image) : null;
                $banner = !empty($dtModuleNoti->banner) ? asset('storage/'.$dtModuleNoti->banner) : null;
            }
        }
        return [
            'id' => $this->id,
            'object_id' => $this->object_id,
            'object_type' => $this->object_type,
            'json_data' => json_decode($this->json_data),
            'title' => ($this->type_customer == 'owen' || $this->type_customer == 'driver') ? $this->title_owen : $this->title,
            'content' => $this->content,
            'content_html' => str_replace('src="/storage', 'src="'.asset('/storage').'', $this->content_html),
            'created_at' => $this->created_at,
            'is_read' => $this->is_read,
            'customer_id' => $this->customer_id,
            'type_customer' => $this->type_customer,
            'icon' => $icon,
            'banner' => $banner,
        ];
    }
}
