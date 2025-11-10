<style>
</style>
<div class="modal-dialog transaction-modal" style="width: 60%;">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">{{$title}}</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="card-box table-responsive">
                    <div class="row m-b-10">
                        <input type="hidden" id="month_search" value="{{$month}}">
                        <input type="hidden" id="year_search" value="{{$year}}">
                        <input type="hidden" id="partner_search_child" value="{{$parent_id}}">
                        <input type="hidden" id="customer_search" value="{{$customer_id}}">
                    </div>
                    <table id="table_report_synthetic_payment_detail" class="table table-bordered table_report_synthetic_payment_detail">
                        <thead>
                        <tr>
                            <th class="text-center">{{lang('dt_stt')}}</th>
                            <th class="text-center">{{lang('Mã phiếu')}}</th>
                            <th class="text-center">{{lang('Ngày tạo')}}</th>
                            <th class="text-center">{{lang('Khách hàng')}}</th>
                            <th class="text-center">{{lang('Tổng tiền')}}</th>
                            <th class="text-center">{{lang('% đối tác')}}</th>
                            <th class="text-center">{{lang('HH đối tác')}}</th>
                            <th class="text-center">{{lang('% thành viên')}}</th>
                            <th class="text-center">{{lang('HH thành viên')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="2" style="text-transform: uppercase;font-weight: bold">Tổng cộng</td>
                            <td></td>
                            <td></td>
                            <td class="grand_total_child" style="font-weight: bold;text-align: right"></td>
                            <td></td>
                            <td class="revenue_partner_child" style="font-weight: bold;text-align: right"></td>
                            <td></td>
                            <td class="revenue_customer_child" style="font-weight: bold;text-align: right"></td>
                        </tr>
                        </tfoot>
                    </table>
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
    var fnserverparamsDetail = {
        'month_search': '#month_search',
        'year_search': '#year_search',
        'partner_search': '#partner_search_child',
        'customer_search': '#customer_search',
    };
    var oTableDetail;
    oTableDetail = InitDataTable('#table_report_synthetic_payment_detail', 'admin/report/getDetailReportSyntheticPayment', {
        'order': [
            [0, 'desc']
        ],
        'responsive': true,
        "ajax": {
            "type": "POST",
            "url": "admin/report/getDetailReportSyntheticPayment",
            "data": function (d) {
                for (var key in fnserverparamsDetail) {
                    d[key] = $(fnserverparamsDetail[key]).val();
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
        ],
    });
    $.each(fnserverparamsDetail, function(filterIndex, filterItem) {
        $('' + filterItem).on('change', function() {
            oTableDetail.draw('page')
        });
    });
    $('#table_report_synthetic_payment_detail').on('draw.dt', function () {
        var table = $(this).DataTable();
        var grand_total_child = table.column(4).data().sum();
        var revenue_partner_child = table.column(6).data().sum();
        var revenue_customer_child = table.column(8).data().sum();
        $("#table_report_synthetic_payment_detail").find('tfoot .grand_total_child').html(formatNumber(grand_total_child));
        $("#table_report_synthetic_payment_detail").find('tfoot .revenue_partner_child').html(formatNumber(revenue_partner_child));
        $("#table_report_synthetic_payment_detail").find('tfoot .revenue_customer_child').html(formatNumber(revenue_customer_child));
    });
</script>
