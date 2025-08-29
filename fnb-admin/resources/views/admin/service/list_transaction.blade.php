@forelse($dtTransaction as $transaction)
    <div class="panel panel-color panel-custom"
         style="border: 1px solid #eee;border-radius: 10px;">
            <div class="panel-heading"
                 style="background-color: {{$transaction['status']['color']}}; display: flex;justify-content: space-between;align-items: center">
                <h3 class="panel-title">{{$transaction['status']['name']}}</h3>
                <div style="color: white">{{getDiffForHumans($transaction['date'])}}</div>
            </div>
            <div class="panel-body">
                <div class="wrap-info-car" style="border-bottom: unset !important;">
                    <div class="info-car-left info-car-left-new">
                        @php
                            $image_service = !empty($transaction['image_service_new']) ? $transaction['image_service_new'] : null;
                            $dtImage = null;
                            if (!empty($image_service)){
                                $dtImage = !empty($image_service[0]) ? $image_service[0] : null;
                            }
                        @endphp
                        {!! loadImage($dtImage,'250px','img-rounded','',false,'200px'); !!}
                    </div>
                    <div class="info-car-right info-car-right-new" style="padding-top: 10px">
                        <div>
                            <span class="title-car">{{$transaction['reference_no']}}</span>
                        </div>
                        <div>
                            <span class="title-car">{{!empty($transaction['customer']) ? $transaction['customer']['fullname'] : ''}}</span>
                        </div>
                        <div style="margin-top: 10px;margin-bottom: 10px;display: flex">
                            <div style="display: flex;margin-right: 10px">
                                <img src="admin/assets/images/day.png" style="width: 20px;margin-right: 5px">
                                <div>{{$transaction['day']['day']}} ngày</div>
                            </div>
                            <div style="display: flex">
                                <img src="admin/assets/images/location.png" style="width: 20px;margin-right: 5px">
                                <div>{{$transaction['total_service']}} điểm đến</div>
                            </div>
                        </div>
                        <div style="margin-top: 10px;margin-bottom: 10px">
                            <i class="fa fa-calendar"></i> Bắt đầu: {{_dt_new($transaction['day']['date_start'],false)}}
                        </div>
                        <div style="margin-bottom: 10px">
                            <i class="fa fa-calendar"></i> Kết thúc: {{_dt_new($transaction['day']['date_end'],false)}}
                        </div>
                    </div>
                    </a>
                </div>
            </div>
    </div>
@empty
@endforelse
<script>
    $(".next").val("{{$next}}");
</script>
