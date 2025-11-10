@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title">{{lang('c_title_client')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/clients/list">{{lang('c_title_client')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <span class="group_search">
                <input type="hidden" name="type_client_search" id="type_client_search" value="0">
            </span>
            <div class="card-box table-responsive">
                <div class="row m-b-10">
                    <div class="col-md-2">
                        <label for="package_search">Gói thành viên</label>
                        <select class="package_search select2" id="package_search"
                                data-placeholder="Chọn ..." name="package_search">
                            <option></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="active_search">{{lang('c_active_client')}}</label>
                        <select class="active_search select2" id="active_search"
                                data-placeholder="Chọn ..." name="active_search">
                            <option></option>
                            <option value="-1">Tất cả</option>
                            <option value="0">Khóa</option>
                            <option value="1">Hoạt động</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date_search">{{lang('dt_date_created_customer')}}</label>
                        <input class="form-control  date_search" type="text" id="date_search" name="date_search" value="">
                    </div>
                    <div class="col-md-3">
                        <label for="ares_search">{{lang('c_ares')}}</label>
                        <select class="ares_search select2" id="ares_search"
                                data-placeholder="Chọn ..." name="ares_search">
                            <option value="0">Tất cả</option>
                            @if(!empty($ares))
                                @foreach($ares as $key => $value)
                                    <option value="{{$value->id}}">{{$value->name}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <table id="table_client" class="table table-bordered table_client">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('c_avatar_client')}}</th>
                        <th class="text-center">{{lang('c_membership_level')}}</th>
                        <th class="text-center">Mã KH</th>
                        <th class="text-center">{{lang('c_fullname_client')}}</th>
                        <th class="text-center">{{lang('c_phone_client')}}</th>
                        <th class="text-center">{{lang('c_email_client')}}</th>
                        <th class="text-center">{{lang('c_point_membership')}}</th>
                        <th class="text-center">{{lang('Số dư tài khoản')}}</th>
                        <th class="text-center">{{lang('c_ranking_date')}}</th>
                        <th class="text-center">{{lang('dt_date_created_customer')}}</th>
                        <th class="text-center">{{lang('Ngày hết hạn sử dụng')}}</th>
                        <th class="text-center">{{lang('c_ares')}}</th>
                        <th class="text-center">{{lang('Nhân viên phụ trách')}}</th>
                        <th class="text-center">{{lang('Số thành viên')}}</th>
                        <th class="text-center">{{lang('Người giới thiệu')}}</th>
                        <th class="text-center">{{lang('Mã giới thiệu')}}</th>
                        <th class="text-center">{{lang('c_active_client')}}</th>
                        <th class="text-center">{{lang('dt_actions')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        var oTable;
        var fnserverparams = {
            'type_client_search' : 'input[name="type_client_search"]',
            'active_search' : '#active_search',
            'date_search' : '#date_search',
            'ares_search' : '#ares_search',
            'package_search' : '#package_search',
        };
        $('.H-search').click(function() {
            $('input[name="type_client_search"]').val($(this).find('a').attr('data-id')).trigger('change');
        })
        $(function() {
            searchAjaxSelect2('#package_search', 'admin/category/searchPackage',0,{type:1})
            search_daterangepicker('date_search');
            oTable = InitDataTable('#table_client', 'admin/clients/getListCustomer', {
                'order': [
                    [9, 'desc']
                ],
                'responsive': false,
                "ajax": {
                    "type": "POST",
                    "url": "admin/clients/getListCustomer",
                    "data": function (d) {
                        for (var key in fnserverparams) {
                            d[key] = $(fnserverparams[key]).val();
                        }
                    },
                    "dataSrc": function (json) {
                        if(json.result == false){
                            alert_float('error',json.message);
                        }
                        return json.data;
                    }
                },
                columnDefs: [
                    {data: 'avatar', name: 'avatar',width: "90px",},
                    {data: 'img_membership_level', name: 'img_membership_level',width: "110px", orderable: false},
                    {data: 'code', name: 'code',width: "110px",},
                    {data: 'fullname', name: 'fullname'},
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'phone', name: 'phone'
                    },
                    {data: 'email', name: 'email'},
                    {data: 'point_membership', name: 'point_membership', orderable: false},
                    {data: 'account_balance', name: 'account_balance',visible : false},
                    {data: 'ranking_date', name: 'ranking_date', orderable: false},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'date_active', name: 'date_active',width: "80px"},
                    {data: 'ares', name: 'ares', orderable: false, searchable: false},
                    {data: 'staff_id', name: 'staff_id', orderable: false, searchable: false,width: "110px" },
                    {data: 'count_number', name: 'count_number', orderable: false},
                    {
                        data: 'referral_code_customer', name: 'referral_code_customer',width: "120px",orderable: false,
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                    },
                    {data: 'referral_code', name: 'referral_code',width: "110px",},
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'active', name: 'active'
                    },
                    {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

                ]
            })
            $.each(fnserverparams, function(filterIndex, filterItem) {
                $('' + filterItem).on('change', function() {
                    oTable.draw('page')
                });
            });
            $('#table_client').on('draw.dt', function () {
                // countAll();
            });

            function countAll() {
                var data = {};
                $.each(fnserverparams, function(filterIndex, filterItem) {
                    data[filterIndex] = $(filterItem).val();
                });
                $.post('admin/clients/countAll', data, function(response) {
                    var total = 0;
                    if(response.arrType.length > 0){
                        $.each(response.arrType, function(index, value) {
                            $(`.count_type_${value.id}`).text(formatNumber(value.total));
                            total += parseFloat(value.total);
                        })
                    }
                    $(`.count_all`).text(formatNumber(total));
                })
            }
        })
    </script>
@endsection
