<style>
    .service-meta {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .service-tag {
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
    }
    .transaction-left{
        min-height: 480px !important;
    }
</style>
<div class="modal-dialog transaction-modal" style="width: 50%;">
    <div class="modal-content" style="background: #eee">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">{{$title}} - {{$dtData['reference_no']}}</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-7">
                    <div class="transaction-left">
                        <div class="contact-card"
                             style="display: flex;align-items: center;border-bottom: 2px solid #eee;padding-bottom: 10px;padding-top: 10px;margin-bottom: 10px">
                            @php
                                $src = !empty($dtData['partner']['avatar_new']) ? $dtData['partner']['avatar_new'] : asset('admin/assets/images/users/avatar-1.jpg');
                            @endphp
                            <a href="{{$src}}" data-lightbox="customer-profile" class="pull-left">
                                <img class="img-circle" src="{{$src}}" alt="" style="width: 70px;height: 70px">
                            </a>
                            <div class="member-info" style="padding-bottom: 0px;padding-left: 20px">
                                <div class="text-dark"><small>Chủ gian</small></div>
                                <h4 class="m-t-0 m-b-5">
                                    <b><a target="_blank" href="admin/partner/view/{{$dtData['partner']['id']}}" class="cursor" style="color: unset">{{$dtData['partner']['fullname'] ?? ''}}</a></b>
                                </h4>
                                <h4 class="m-t-0 m-b-5">
                                    <b><i class="fa fa-phone" aria-hidden="true"></i> <span class="phone_customer">{{$dtData['partner']['phone'] ?? ''}}</span></b>
                                </h4>
                            </div>
                        </div>
                        <div class="wrap-info-car">
                            <div class="info-car-left" style="width: 20%">
                                @php
                                    $image = !empty($dtData['service']['image']) ? $dtData['service']['image'] : null;
                                    $dtImage = $image;
                                @endphp
                                {!! loadImage($dtImage,'100px','img-rounded','',false); !!}
                            </div>
                            <div class="info-car-right" style="width: 80%">
                                <div>
                                    <span class="title-car"><a style="color: unset" href="admin/service/view/{{$dtData['service']['id']}}" target="_blank">{{$dtData['service']['name']}}</a></span>
                                </div>
                                <div class="service-meta">
                                    <div class="service-tag" style="background: {{$dtData['service']['group_category_service']['color']}};border: {{$dtData['service']['group_category_service']['color_border']}}"> <img src="{{$dtData['service']['category_service']['icon']}}" style="width: 20px"> {{$dtData['service']['category_service']['name'] ?? ''}}</div>
                                </div>
                                <div class="title-address">
                                    <img src="admin/assets/images/location.svg" style="width: 20px;margin-right: 5px">{{$dtData['service']['location']['address'] ?? ''}}
                                </div>
                            </div>
                        </div>
                        <div class="wrap-transaction-mortgage">
                            <div class="title-transaction-mortgage">Nhân viên CSKH</div>
                            @php
                                $htmlImage = '';
                            @endphp
                            <div style="display: flex;flex-wrap: wrap">{!! $htmlImage !!}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="transaction-right">
                        <div class="contact-card"
                             style="display: flex;align-items: center;border-bottom: 2px solid #eee;padding-bottom: 10px">
                            @php
                                $src = !empty($dtData['customer']['avatar_new']) ? $dtData['customer']['avatar_new'] : asset('admin/assets/images/users/avatar-1.jpg');
                            @endphp
                            <a href="{{$src}}" data-lightbox="customer-profile" class="pull-left">
                                <img class="img-circle" src="{{$src}}" alt="" style="width: 70px;height: 70px">
                            </a>
                            <div class="member-info" style="padding-bottom: 0px;padding-left: 20px">
                                <div class="text-dark"><small>Khách hàng</small></div>
                                <h4 class="m-t-0 m-b-5">
                                    <b><a target="_blank" href="admin/clients/view/{{$dtData['customer']['id']}}" class="cursor" style="color: unset">{{$dtData['customer']['fullname'] ?? ''}}</a></b>
                                </h4>
                                <h4 class="m-t-0 m-b-5">
                                    <b><i class="fa fa-phone" aria-hidden="true"></i> <span class="phone_customer">{{$dtData['customer']['phone'] ?? ''}}</span></b>
                                </h4>
                            </div>
                        </div>
                        <div class="wrap-transaction-price">
                            <div class="title-transaction-price">Thanh toán</div>
                            <div class="detail-transaction-price">
                                <div class="title-price">Phiếu thu</div>
                                <div class="value-price">{{$dtData['payment']['reference_no'] ?? ''}}</div>
                            </div>
                            <div class="detail-transaction-price">
                                <div class="title-price">Tổng tiền</div>
                                <div class="value-price">{{formatMoney($dtData['payment']['payment'] ?? 0 )}}đ</div>
                            </div>
                            @php
                                $dtImage = null;
                                if (!empty($dtData['payment'])){
                                   $dtImage = !empty($dtData['payment']['payment_mode']['image']) ? $dtData['payment']['payment_mode']['image'] : null;
                                }
                                 $htmlStatus = "";
                                if (!empty($dtData['payment'])){
                                    if ($dtData['payment']['status'] == 2) {
                                        $htmlStatus = ' <span class="label label-success">Đã thanh toán</span>';
                                    } else {
                                        $htmlStatus = ' <span class="label label-danger">Chưa thanh toán</span>';
                                    }
                                }
                            @endphp
                            <div class="detail-transaction-price " style="display: flex;align-items: center">
                                <div class="title-price">Phương thức thanh toán</div>
                                <div class="value-price" style="display: flex;align-items: center">{!! !empty($dtData['payment']) ? loadImage($dtImage). $dtData['payment']['payment_mode']['name'] : '' !!}</div>
                            </div>
                            <div class="detail-transaction-price border_bottom" style="display: flex;align-items: center;padding-bottom: 10px">
                                <div class="title-price">Trạng thái</div>
                                <div class="value-price" style="display: flex;align-items: center"><a class="dt-update" data-type="transaction" href="admin/payment/changeStatus/{{$dtData['payment']['id'] ?? 0}}">{!! $htmlStatus !!}</a></div>
                            </div>
                        </div>
                        <div class="wrap-transaction-price">
                            <div class="title-transaction-price">Bảng tính giá</div>
                            <div class="detail-transaction-price">
                                <div class="title-price">Số tiền
                                </div>
                                <div
                                    class="value-price">{{formatMoney($dtData['total'])}}đ
                                </div>
                            </div>
                            <div class="detail-transaction-price">
                                <div class="title-price">Số tiền giảm giá
                                </div>
                                <div class="value-price">{{formatMoney($dtData['total_discount'])}}đ</div>
                            </div>
                            <div class="detail-transaction-price">
                                <div class="title-price">Số tiền còn lại</div>
                                <div class="value-price">{{formatMoney($dtData['grand_total'])}}đ
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <a class="dt-modal hide click1"
               href="admin/transaction_bill/view/{{$dtData['id']}}" data-toggle="modal"
               data-target="#myModal"></a>
            <button type="button" class="btn btn-default"
                    data-dismiss="modal">{{lang('dt_close')}}</button>
        </div>
    </div>
</div>
<script>
</script>
