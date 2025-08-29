@extends('admin.layouts.index')
@section('content')
    <style>
        .card-box {
            min-height: 180px;
        }
    </style>
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{lang('c_title_client')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/clients/list">{{lang('c_title_client')}}</a></li>
                <li class="active">{{ lang('dt_view_client') }}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <ul class="nav nav-tabs navtab-bg nav-justified">
                <li class="active">
                    <a href="#info" data-toggle="tab" aria-expanded="false">
                        <span class="visible-xs">Thông tin chung</span>
                        <span class="hidden-xs">Thông tin chung</span>
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="info">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="card-box">
                                <h5 class="text-muted text-uppercase m-t-0 m-b-20" style="color: black"><b>Thông tin tài
                                        khoản</b></h5>
                                <div class="contact-card" style="display: flex;flex-wrap: wrap">
                                    @php
                                        $src = !empty($client['avatar']) ? ($client['avatar']) : asset('admin/assets/images/users/avatar-1.jpg');
                                        $classesT = 'btn-danger';
                                        $contentT = 'Khách hàng';
                                        if($client['type_client'] == 2) {
                                            $classesT = 'btn-info';
                                            $contentT = 'Đối tác';
                                        }
                                        $str = "<a class='text-center btn btn-xs $classesT'>$contentT</a>";

                                        $classes = $client['active'] == 1 ? "btn-info" : "btn-danger";
                                        $content = $client['active'] == 1 ? "Hoạt động" : "Khoá";
                                        $strStatus = "<a class='dt-update text-center btn btn-xs $classes'>$content</a>";

                                    @endphp
                                    <div class="member-img"
                                         style="display: flex;flex-direction: column;align-items: center">
                                        <a href="{{$src}}" data-lightbox="customer-profile"
                                           class="display-block mbot5 pull-left">
                                            <img class="img-circle" src="{{$src}}" alt=""
                                                 style="width: 100px;height: 100px">
                                        </a>
                                        <h4 class="m-t-0 m-b-5"><b>{{$client['fullname']}}</b></h4>
                                    </div>
                                    <div class="member-info-new">
                                        <p class="text-dark member-info-detail"><span>{{lang('dt_phone_user')}}: </span><span>{{$client['phone']}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail"><span>{{lang('dt_email_user')}}: </span><span>{{$client['email']}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail"><span>{{lang('dt_date_created_customer')}}: </span><span>{{_dt($client['created_at'])}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>Địa chỉ : </span><span>{{$client['address']}}</span></p>
                                        <p class="text-dark member-info-detail">
                                            <span>{{lang('c_active_client')}}: </span><span>{!! $strStatus !!}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>Số cccd: </span><span>{{($client['number_cccd'])}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>Ngày cấp cccd: </span><span>{{ !empty($client['date_cccd']) ? _dthuan($client['date_cccd']) : ''}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>Nơi cấp cccd: </span><span>{{($client['issued_cccd'])}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>Số passport: </span><span>{{($client['number_passport'])}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>Ngày cấp passport: </span><span>{{!empty($client['date_passport']) ? _dthuan(($client['date_passport'])) : ''}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>Nơi cấp passport: </span><span>{{($client['issued_passport'])}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>{{lang('dt_province')}}: </span><span>{{($client['province']['Name'] ?? '')}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>{{lang('dt_wards')}}: </span><span>{{($client['wards']['Name'] ?? '')}}</span>
                                        </p>

                                        <p class="text-dark member-info-detail">
                                            <span>{{lang('Hạng mức hóa đơn')}}: </span><span>{!!(empty($client['active_limit_private']) ? '<a class="btn btn-xs btn-info">Chiết khấu theo hạng thành viên</a>' : '<a class="btn btn-xs btn-warning">Chiết khấu riêng</a>')!!}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>{{lang('Chiết khấu')}}: </span><span>{{(!empty($client['active_limit_private']) ? $client['radio_discount_private'] : $client['radio_discount_member']) }} % </span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>{{lang('Hạn mức hóa đơn mỗi lần thanh toán')}}: </span><span>{{(!empty($client['active_limit_private']) ? number_format($client['invoice_limit_private']) : number_format($client['invoice_limit_member']))}}</span>
                                        </p>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
@endsection
