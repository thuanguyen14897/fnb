@extends('admin.layouts.index')
@section('content')
    <style>
        .card-box {
            min-height: 180px;
        }
        .tree ul {
            padding-top: 20px;
            position: relative;
            transition: all 0.5s;
            -webkit-transition: all 0.5s;
            -moz-transition: all 0.5s;
        }

        .tree li {
            float: left;
            text-align: center;
            list-style-type: none;
            position: relative;
            padding: 20px 5px 0 5px;
            transition: all 0.5s;
            -webkit-transition: all 0.5s;
            -moz-transition: all 0.5s;
        }

        .tree li::before, .tree li::after {
            content: '';
            position: absolute;
            top: 0;
            right: 50%;
            border-top: 1px solid #ccc;
            width: 50%;
            height: 20px;
        }

        .tree li::after {
            right: auto;
            left: 50%;
            border-left: 1px solid #ccc;
        }

        .tree li:only-child::before, .tree li:only-child::after {
            display: none;
        }

        .tree li:only-child {
            padding-top: 0;
        }

        .tree li:first-child::before, .tree li:last-child::after {
            border: 0 none;
        }

        .tree li:last-child::before {
            border-right: 1px solid #ccc;
            border-radius: 0 5px 0 0;
        }

        .tree li:first-child::after {
            border-radius: 5px 0 0 0;
        }
        .tree ul ul::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            border-left: 1px solid #ccc;
            width: 0;
            height: 20px;
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
                <li>
                    <a href="#favourite" data-toggle="tab" aria-expanded="false">
                        <span class="visible-xs">Gian hàng yêu thích</span>
                        <span class="hidden-xs">Gian hàng yêu thích</span>
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

                                        $strReferral = '';
                                        $referral_level = $client['referral_level'];
                                        if (!empty($referral_level)) {
                                            if ($referral_level['parent']['type_client'] == 1) {
                                                $strReferral = "<a class='text-center label label-danger' target='_blank' href='admin/clients/view/" . $referral_level['parent_id'] . "'>" . $referral_level['referral_code'] . "</a>";
                                            } else {
                                                $strReferral = "<a class='text-center label label-danger' target='_blank' href='admin/partner/view/" . $referral_level['parent_id'] . "'>" . $referral_level['referral_code'] . "</a>";
                                            }
                                        }
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
                                            <span>{{lang('dt_province')}}: </span><span>{{($client['province']['Name'] ?? '')}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>{{lang('dt_wards')}}: </span><span>{{($client['wards']['Name'] ?? '')}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>Mã giới thiệu : </span><span class="label label-default">{{$client['referral_code']}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>Người giới thiệu : </span>{!! $strReferral !!}</span>
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
                        <div class="col-md-7">
                            <div class="card-box">
                                <div class="title_driving_liscense">
                                    <h5 class="text-muted text-uppercase m-t-0 m-b-20" style="color: black"><b>Sơ đồ
                                            nhánh các cấp</b></h5>
                                </div>
                                <div>Tổng số thành viên: {{$countMember}}</div>
                                <div class="hide">Tổng số cấp: {{$level}}</div>
                                <div class="tree" style="overflow-x: auto;overflow-y: hidden; white-space: nowrap;">
                                    @php
                                        $html = get_parent_id_referral_level_html($dataReferralLevel)
                                    @endphp
                                    {!! $html !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="favourite">
                    <div class="row">
                        <input type="hidden" name="customer_id" class="customer_id" id="customer_id"
                               value="{{$client['id']}}">
                        <div class="row m-b-10">
                            <div class="col-md-2">
                                <label for="group_category_service_search_favourite">{{lang('Nhóm danh mục')}}</label>
                                <select class="group_category_service_search_favourite select2" id="group_category_service_search_favourite"
                                        data-placeholder="Chọn ..." name="group_category_service_search_favourite">
                                    <option></option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="category_service_search_favourite">{{lang('Danh mục')}}</label>
                                <select class="category_service_search_favourite select2" id="category_service_search_favourite"
                                        data-placeholder="Chọn ..." name="category_service_search_favourite">
                                    <option></option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="status_search_favourite">Trạng thái</label>
                                <select class="status_search_favourite select2" id="status_search_favourite"
                                        data-placeholder="Chọn ..." name="status_search_favourite">
                                    <option></option>
                                    <option value="-1">Tất cả</option>
                                    @foreach(getListStatusService() as $key => $value)
                                        <option value="{{$value['id']}}">{{$value['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="customer_search_favourite">Đối tác</label>
                                <select class="customer_search_favourite select2" id="customer_search_favourite"
                                        data-placeholder="Chọn ..." name="customer_search_favourite">
                                    <option></option>
                                </select>
                            </div>
                        </div>
                        {!! loadTableServiceFavourite() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            searchAjaxSelect2('#customer_search_favourite', 'admin/category/searchCustomer')
            searchAjaxSelect2('#group_category_service_search_favourite', 'admin/category/searchGroupCategoryService')
            searchAjaxSelect2('#category_service_search_favourite', 'admin/category/searchCategoryService')
        });
        var fnserverparamsNew = {
            'customer_favourite': '#customer_id',
            'group_category_service_search': '#group_category_service_search_favourite',
            'category_service_search': '#category_service_search_favourite',
            'status_search': '#status_search_favourite',
            'customer_search': '#customer_search_favourite',
        };
        var oTableFavourite;
        function loadTable() {
            oTableFavourite = InitDataTable('#table_service_favourite', 'admin/service/getList', {
                'order': [
                    [0, 'desc']
                ],
                'responsive': true,
                "ajax": {
                    "type": "POST",
                    "url": "admin/service/getList",
                    "data": function (d) {
                        for (var key in fnserverparamsNew) {
                            d[key] = $(fnserverparamsNew[key]).val();
                        }
                        d['favourite'] = 1;
                    },
                    "dataSrc": function (json) {
                        return json.data;
                    }
                },
                columnDefs: [
                    {   "render": function (data, type, row) {
                            return `<div class="text-center">${data}</data>`;
                        },
                        data: 'id', name: 'id',width: "50px"
                    },
                    {data: 'image', name: 'image',width: "120px" , orderable: false},
                    {data: 'name', name: 'name',width: "250px" },
                    {data: 'province_id', name: 'province_id',width: "150px"},
                    {data: 'customer_id', name: 'customer_id',width: "150px"},
                    {data: 'group_category_service_id', name: 'group_category_service_id',width: "100px"},
                    {data: 'category_service_id', name: 'category_service_id',width: "100px"},
                    {data: 'price', name: 'price',width: "100px"},
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'active', name: 'active',width: "100px"},
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'hot', name: 'hot',width: "100px"
                    },
                    {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },
                ]
            });
        }
        $.each(fnserverparamsNew, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTableFavourite.draw('page')
            });
        });
        $(document).on('shown.bs.tab', 'a[href="#favourite"]', function () {
            loadTable();
        });
    </script>
@endsection
