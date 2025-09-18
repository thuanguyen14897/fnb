<div class="modal-dialog" style="width: 30%;">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">{{$title}}</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="wrap-payment">
                        <div class="title-payment">{{lang('dt_reference_no')}}</div>
                        <div class="value-payment">{{$dtData['reference_no']}}</div>
                    </div>
                    <div class="wrap-payment">
                        <div class="title-payment">{{lang('dt_date')}}</div>
                        <div class="value-payment">{{_dthuan($dtData['date'])}}</div>
                    </div>
                    <div class="wrap-payment">
                        <div class="title-payment">{{lang('Khách hàng')}}</div>
                        @php
                            $url = !empty(($dtData['customer']['avatar_new'])) ? $dtData['customer']['avatar_new'] : asset('admin/assets/images/users/avatar-1.jpg');
                        @endphp
                        <div class="value-payment">{!! '<div style="display: flex;align-items: center;flex-wrap: wrap">'.loadImageAvatar($url,'30px').($dtData['customer']['fullname'] ?? '').'</div>' !!}</div>
                    </div>
                    <div class="wrap-payment">
                        <div class="title-payment">{{lang('dt_transaction_bill')}}</div>
                        @php
                            $str = '<div><a class="dt-modal" href="admin/transaction_bill/view/'.$dtData['transaction_bill']['id'].'">'.$dtData['transaction_bill']['reference_no'].'</a></div>';
                        @endphp
                        <div class="value-payment">{!! $str !!}</div>
                    </div>
                    <div class="wrap-payment">
                        @php
                            if ($dtData['status'] == 1){
                               $htmlStatus = '<div class="dt-update label label-danger">Chờ thanh toán</div>';
                           } else {
                               $htmlStatus = '<div class="label label-success">Đã thanh toán</div>';
                           }
                        @endphp
                        <div class="title-payment">Trạng thái</div>
                        <div class="value-payment">{!! $htmlStatus !!}</div>
                    </div>
                    <div class="wrap-payment">
                        <div class="title-payment">{{lang('dt_payment_mode')}}</div>
                        <div class="value-payment">{{($dtData['payment_mode']['name'])}}</div>
                    </div>
                    <div class="wrap-payment">
                        <div class="title-payment">{{lang('dt_total')}}</div>
                        <div class="value-payment">{{formatMoney($dtData['payment'])}}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default"
                    data-dismiss="modal">{{lang('dt_close')}}</button>
        </div>
    </div>
</div>
<script>

</script>
