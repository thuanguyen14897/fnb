@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{$title}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/transaction/list">{{$title}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <ul class="nav nav-tabs slick-responsive">
                <li class="H-search active-menu cursor"><a data-toggle="tab" data-id="-1">Tất cả (<b class="count_all">0</b>)</a></li>
                <li class="H-search cursor"><a style="color: red !important;" data-toggle="tab" data-id="-2">Đang theo dõi (<b class="count_follow">0</b>)</a></li>
                @foreach (getListStatusTransaction() as $key => $value)
                    <li class="H-search cursor"><a style="color: {{$value['color']}} !important;" data-toggle="tab" data-id="{{$value['id']}}">{{$value['name']}}  (<b class="count_{{$value['id']}}">0</b>)</a></li>
                @endforeach
            </ul>
            <span class="group_search">
                <input type="hidden" name="status_search" id="status_search" value="-1">
            </span>
            <div class="card-box table-responsive">
                <div class="row m-b-10">
                    <div class="col-md-2">
                        <label for="partner_search">Đối tác</label>
                        <select class="partner_search select2" id="partner_search"
                                data-placeholder="Chọn ..." name="partner_search">
                            <option></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="customer_search">Khách hàng</label>
                        <select class="customer_search select2" id="customer_search"
                                data-placeholder="Chọn ..." name="customer_search">
                            <option></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_search">Thời gian bắt đầu</label>
                        <input class="form-control date_search" type="text" id="date_search" name="date_search" value="" autocomplete="off">
                    </div>
                    <div class="col-md-2">
                        <label for="date_search_end">Thời gian kết thúc</label>
                        <input class="form-control date_search_end" type="text" id="date_search_end" name="date_search_end" value="" autocomplete="off">
                    </div>
                    <div class="col-md-2">
                        <label for="service_search">Gian hàng</label>
                        <select class="service_search select2" id="service_search"
                                data-placeholder="Chọn ..." name="service_search">
                            <option></option>
                        </select>
                    </div>
                </div>
                <table id="table_transaction" class="table table-bordered table_transaction">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('dt_reference_no')}}</th>
                        <th class="text-center">{{lang('dt_date')}}</th>
                        <th class="text-center">{{lang('Khách hàng')}}</th>
                        <th class="text-center">{{lang('dt_start')}}</th>
                        <th class="text-center">{{lang('dt_end')}}</th>
                        <th class="text-center">{{lang('dt_status')}}</th>
                        <th class="text-center">Nhân viên CSKH</th>
                        <th class="text-center">{{lang('dt_actions')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function (){
            searchAjaxSelect2('#customer_search','admin/category/searchCustomer')
            searchAjaxSelect2('#customer_renter_search','admin/category/searchCustomer',0,{type_client:-1})
            search_daterangetimepicker('date_search');
            search_daterangetimepicker('date_search_end');
        })
        var fnserverparams = {
            'partner_search': '#partner_search',
            'customer_search': '#customer_search',
            'service_search': '#service_search',
            'date_search': '#date_search',
            'date_search_end': '#date_search_end',
        };
        var oTable;
        oTable = InitDataTable('#table_transaction', 'admin/transaction/getList', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/transaction/getList",
                "data": function (d) {
                    for (var key in fnserverparams) {
                        d[key] = $(fnserverparams[key]).val();
                    }
                },
                "dataSrc": function (json) {
                    return json.data;
                }
            },
            columnDefs: [
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'id', name: 'id',width: "50px"
                },
                {data: 'reference_no', name: 'reference_no',width: "100px" },
                {data: 'date', name: 'date',width: "100px" },
                {data: 'customer', name: 'customer',width: "140px",orderable: false},
                {data: 'date_start', name: 'date_start'},
                {data: 'date_end', name: 'date_end'},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'status', name: 'status'},
                {data: 'user_id', name: 'user_id'},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ],
        });

        $('.H-search').click(function() {
            $('input[name="status_search"]').val($(this).find('a').attr('data-id')).trigger('change');
        })


        $.each(fnserverparams, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTable.draw('page')
            });
        });

        $('#table_transaction').on('draw.dt', function () {
            getCountAll();
        });

        function getCountAll() {
            return ;
            var data = {};
            $.each(fnserverparams, function(filterIndex, filterItem) {
                data[filterIndex] = $(filterItem).val();
            });
            $.ajax({
                url: 'admin/transaction/countAll',
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: data
            })
                .done(function (response) {
                    var total = 0;
                    if(response.arr.length > 0){
                        $.each(response.arr, function(index, value) {
                            $(`.count_${value.id}`).text(formatNumber(value.count));
                            total += parseFloat(value.count);
                        })
                    }
                    $(`.count_all`).text(formatNumber(total));
                    $(`.count_follow`).text(formatNumber(response.follow));
                })
                .fail(function () {

                });
            return false;
        }
    </script>
@endsection
