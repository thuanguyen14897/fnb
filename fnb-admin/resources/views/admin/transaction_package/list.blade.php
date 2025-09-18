@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default dropdown-toggle waves-effect waves-light dt-modal" data-toggle="dropdown"
                   aria-expanded="false" href="admin/transaction_package/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title">{{lang('Danh sách mua gói')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/transaction_package/list">{{lang('Danh sách mua gói')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <div class="row m-b-10">
                    <div class="col-md-2">
                        <label for="customer_search">Thành viên</label>
                        <select class="customer_search select2" id="customer_search"
                                data-placeholder="Chọn ..." name="customer_search">
                            <option></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="package_search">Gói thành viên</label>
                        <select class="package_search select2" id="package_search"
                                data-placeholder="Chọn ..." name="package_search">
                            <option></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status_search">Trạng thái</label>
                        <select class="status_search select2" id="status_search"
                                data-placeholder="Chọn ..." name="status_search">
                            <option value="0">Tất cả</option>
                            <option value="1">Chờ thanh toán</option>
                            <option value="2">Đã thanh toán</option>
                        </select>
                    </div>
                </div>
                <table id="table_transaction_package" class="table table-bordered table_transaction_package">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('STT')}}</th>
                        <th class="text-center">{{lang('dt_reference_no')}}</th>
                        <th class="text-center">{{lang('Ngày tạo')}}</th>
                        <th class="text-center">{{lang('Thành viên')}}</th>
                        <th class="text-center">{{lang('Gói thành viên')}}</th>
                        <th class="text-center">{{lang('Số ngày')}}</th>
                        <th class="text-center">{{lang('Tổng tiền')}}</th>
                        <th class="text-center">{{lang('Trạng thái')}}</th>
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
        $(document).ready(function () {
            searchAjaxSelect2('#customer_search', 'admin/category/searchCustomer')
            searchAjaxSelect2('#package_search', 'admin/category/searchPackage')
        });
        var oTable;
        var fnserverparams = {
            'customer_search': '#customer_search',
            'package_search': '#package_search',
            'status_search': '#status_search',
        };

        $(function() {
            oTable = InitDataTable('#table_transaction_package', 'admin/transaction_package/getListTransactionPackage', {
                'order': [
                    [0, 'desc']
                ],
                'responsive': false,
                "ajax": {
                    "type": "POST",
                    "url": "admin/transaction_package/getListTransactionPackage",
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
                    {   "render": function (data, type, row) {
                            return `<div class="text-center">${data}</data>`;
                        },
                        data: 'id', name: 'id',width: "80px"
                    },
                    {data: 'reference_no', name: 'reference_no',width: "120px"},
                    {data: 'date', name: 'date',width: "120px"},
                    {data: 'customer', name: 'customer'},
                    {data: 'package', name: 'package',width: "120px"},
                    {data: 'number_day', name: 'number_day',width: "150px"},
                    {data: 'grand_total', name: 'grand_total',width: "150px"},
                    {data: 'status', name: 'status',width: "150px"},
                    {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

                ]
            })
            $.each(fnserverparams, function(filterIndex, filterItem) {
                $('' + filterItem).on('change', function() {
                    oTable.draw('page')
                });
            });
        })
    </script>
@endsection
