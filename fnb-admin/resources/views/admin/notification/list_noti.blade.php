@forelse($dtNotification as $notification)
    @php
        $json_data = json_decode($notification->json_data);
        $class = '';
        $href = '';
        if (!empty($json_data)){
             if (!empty($json_data->object) && $json_data->object == 'transaction'){
                $class = 'dt-modal';
                $href = 'href=admin/transaction/view/'.$notification->object_id.'';
            }
        }
     @endphp
    <a {{$href}} class="list-group-item {{$class}}" style="background: {{$notification->is_read == 0 ? 'aliceblue' : 'white'}}">
        <div class="media">
            <div class="pull-left p-r-10">
                <em {{$notification->is_read == 0 ? 'onclick=readSingleNoti(this,'.$notification->id.')' : ''}} style="cursor: pointer" class="fa fa-bell-o {{$notification->is_read == 0 ? 'noti-custom-not-read' : 'noti-custom'}} "></em>
            </div>
            <div class="media-body">
                <h5 class="media-heading">{{$notification->title}}</h5>
                <p class="m-0">
                    <small>{{$notification->content}}</small>
                </p>
                <p class="m-0" style="color: #8496AE">
                    <small>{{_dt_new($notification->created_at)}}</small>
                </p>
            </div>
        </div>
    </a>
@empty
    <div class="list-group-item" style="display: flex;justify-content: center;align-items: center;flex-direction: column">
        <img src="admin/assets/images/text-doc.svg">
        <div>Không có thông báo</div>
    </div>
@endforelse
<script>
    $(".next_noti").val("{{$next}}")
</script>
