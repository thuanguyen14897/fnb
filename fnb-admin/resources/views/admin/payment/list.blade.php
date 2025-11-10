@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{$title}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/payment/list">{{$title}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
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
                        <label for="date_search">Thời gian</label>
                        <input class="form-control date_search" type="text" id="date_search" name="date_search" value="" autocomplete="off">
                    </div>
                    <div class="col-md-2">
                        <label for="transaction_bill_search">Hóa đơn</label>
                        <select class="transaction_bill_search select2" id="transaction_bill_search"
                                data-placeholder="Chọn ..." name="transaction_bill_search">
                            <option></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status_search">Trạng thái</label>
                        <select class="status_search select2" id="status_search"
                                data-placeholder="Chọn ..." name="status_search">
                            <option value="-1">Tất cả</option>
                            <option value="1">Chưa thanh toán</option>
                            <option value="2">Đã thanh toán</option>
                        </select>
                    </div>
                </div>
                <table id="table_payment" class="table table-bordered table_payment">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('dt_reference_no')}}</th>
                        <th class="text-center">{{lang('dt_date')}}</th>
                        <th class="text-center">{{lang('Khách hàng')}}</th>
                        <th class="text-center">{{lang('Hóa đơn')}}</th>
                        <th class="text-center">{{lang('Đối tác')}}</th>
                        <th class="text-center">{{lang('Trạng thái')}}</th>
                        <th class="text-center">{{lang('Phương thức thanh toán')}}</th>
                        <th class="text-center">{{lang('Tổng tiền')}}</th>
                        <th class="text-center">{{lang('% đối tác')}}</th>
                        <th class="text-center">{{lang('HH đối tác')}}</th>
                        <th class="text-center">{{lang('% thành viên')}}</th>
                        <th class="text-center">{{lang('HH thành viên')}}</th>
                        <th class="text-center">{{lang('dt_actions')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" style="text-transform: uppercase;font-weight: bold">Tổng cộng</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="grand_total" style="font-weight: bold;text-align: right"></td>
                            <td></td>
                            <td class="revenue_partner" style="font-weight: bold;text-align: right"></td>
                            <td></td>
                            <td class="revenue_customer" style="font-weight: bold;text-align: right"></td>
                            <td></td>
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
            searchAjaxSelect2('#transaction_bill_search','admin/category/searchTransactionBill')
            searchAjaxSelect2('#customer_search','admin/category/searchCustomer')
            searchAjaxSelect2('#partner_search','admin/category/searchCustomer')
            search_daterangetimepicker('date_search');
        })
        var fnserverparams = {
            'status_search': '#status_search',
            'transaction_bill_search': '#transaction_bill_search',
            'customer_search': '#customer_search',
            'partner_search': '#partner_search',
            'date_search': '#date_search',
        };
        var oTablePayment;
        oTablePayment = InitDataTable('#table_payment', 'admin/payment/getList', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/payment/getList",
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
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'id', name: 'id',width: "50px"
                },
                {data: 'reference_no', name: 'reference_no',width: "120px" },
                {data: 'date', name: 'date',width: "120px" },
                {data: 'customer', name: 'customer',width: "140px",orderable: false},
                {data: 'transaction_bill', name: 'transaction_bill',width: "140px"},
                {data: 'partner', name: 'partner',width: "130px"},
                {data: 'status', name: 'status',width: "120px"},
                {data: 'payment_mode', name: 'payment_mode',width: "150px"},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'grand_total', name: 'grand_total',width: "120px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'percent_partner', name: 'percent_partner',width: "120px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'revenue_partner', name: 'revenue_partner',width: "120px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'percent_customer', name: 'percent_customer',width: "120px"
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                    data: 'revenue_customer', name: 'revenue_customer',width: "120px"
                },
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ],
        });


        $.each(fnserverparams, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTablePayment.draw('page')
            });
        });

        $('#table_payment').on('draw.dt', function () {
            var table = $(this).DataTable();
            var grand_total = table.column(8).data().sum();
            var revenue_partner = table.column(10).data().sum();
            var revenue_customer = table.column(12).data().sum();
            $("#table_payment").find('tfoot .grand_total').html(formatNumber(grand_total));
            $("#table_payment").find('tfoot .revenue_partner').html(formatNumber(revenue_partner));
            $("#table_payment").find('tfoot .revenue_customer').html(formatNumber(revenue_customer));
        });

        function changeStatus(transaction_id,status){
            $.ajax({
                url: 'admin/payment/changeStatus',
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: {
                    transaction_id: transaction_id,
                    status: status,
                },
            })
                .done(function (data) {
                    if (data.result) {
                        alert_float('success', data.message);
                    } else {
                        alert_float('error', data.message);
                    }
                    oTablePayment.draw('page');
                })
                .fail(function () {

                });
            return false;
        }
    </script>
@endsection
