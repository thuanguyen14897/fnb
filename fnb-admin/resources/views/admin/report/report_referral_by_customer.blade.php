@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{$title}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/report/report_referral_by_customer">{{$title}}</a></li>
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
                                data-placeholder="Chọn ..." name="partner_search">
                            <option></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_search">Thời gian</label>
                        <input class="form-control date_search" type="text" id="date_search" name="date_search" value="" autocomplete="off">
                    </div>
                    <div class="col-md-2">
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
                    <div class="col-md-12 m-t-10">
                        <button id="exportButton" class="btn btn-default">Xuất Excel</button>
                    </div>
                </div>
                <table id="table_report_referral_by_customer" class="table table-bordered table_report_referral_by_customer">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('Thành viên')}}</th>
                        <th class="text-center">{{lang('Thành viên được giới thiệu')}}</th>
                        <th class="text-center">{{lang('Người đại diện')}}</th>
                        <th class="text-center">{{lang('Cơ sở kinh doanh')}}</th>
                        <th class="text-center">{{lang('Ngày đăng ký')}}</th>
                        <th class="text-center">{{lang('STT thành viên')}}</th>
                        <th class="text-center">{{lang('Gói hội viên')}}</th>
                        <th class="text-center">{{lang('Khu vực kinh doanh')}}</th>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.2/xlsx.full.min.js"></script>
    <script>
        $(document).ready(function (){
            searchAjaxSelect2('#customer_search','admin/category/searchCustomer')
            search_daterangepicker('date_search');
        })
        var fnserverparams = {
            'customer_search': '#customer_search',
            'ares_search': '#ares_search',
            'date_search': '#date_search',
        };
        var oTable;
        oTable = InitDataTable('#table_report_referral_by_customer', 'admin/report/getReportReferralByCustomer', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/report/getReportReferralByCustomer",
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
                {data: 'customer', name: 'customer',orderable: false },
                {data: 'customer_referral', name: 'customer_referral',width: "200px", orderable: false },
                {data: 'partner', name: 'partner',width: "200px", orderable: false },
                {data: 'partner_representative', name: 'partner_representative',width: "200px", orderable: false },
                {data: 'created_at', name: 'created_at',width: "150px", orderable: false },
                {data: 'stt', name: 'stt',width: "80px", orderable: false},
                {data: 'customer_package', name: 'customer_package',width: "100px", orderable: false },
                {data: 'ares', name: 'ares',width: "200px", orderable: false},
            ],
        });
        $.each(fnserverparams, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTable.draw('page')
            });
        });

        var table = $('#table_report_referral_by_customer').DataTable();

        // Sự kiện click để xuất Excel
        $('#exportButton').click(function() {
            // Lấy tiêu đề cột từ DataTable
            var tableData = [];
            var header = [];

            // Lấy tên các cột từ bảng tiêu đề
            $('#table_report_referral_by_customer thead tr th').each(function() {
                header.push($(this).text());
            });

            tableData.push(header); // Thêm tiêu đề vào mảng dữ liệu

            // Lấy dữ liệu từ từng dòng
            table.rows().every(function(rowIdx, tableLoop, rowLoop) {
                var rowData = [];
                // Lấy dữ liệu từ mỗi cột hiển thị
                $('#table_report_referral_by_customer thead tr th').each(function(index) {
                    var cellData = table.cell(rowIdx, index).node();
                    var cellText = $(cellData).find('a').text();
                    var cellTextDiv = $(cellData).find('div').first().find('div').text();
                    if (cellText) {
                        rowData.push(cellText); // Nếu có thẻ <a>, lấy văn bản từ đó
                    } else if($(cellData).find('div').text()){
                        if(cellTextDiv){
                            if($(cellData).find('div.text-right').first().find('div').text()){
                                rowData.push(intVal($(cellData).find('div.text-right').first().find('div').text()));
                            } else if($(cellData).find('div.text-right').first().find('div').text()){
                                rowData.push(intVal($(cellData).find('div.text-right').first().find('div').text()));
                            } else {
                                rowData.push(cellTextDiv);
                            }
                        } else {
                            rowData.push($(cellData).find('div').text());
                        }
                    } else {
                        rowData.push($(cellData).find('div').text()); // Nếu không có thẻ <a> và <div>, lấy dữ liệu gốc
                    }
                });

                tableData.push(rowData);
            });
            var wb = XLSX.utils.book_new();
            var ws = XLSX.utils.aoa_to_sheet(tableData);

            var colWidths = [];
            tableData.forEach(function(row) {
                row.forEach(function(cell, colIdx) {
                    const cellValue = String(cell);
                    if (!colWidths[colIdx]) {
                        colWidths[colIdx] = 0;
                    }
                    // Tính chiều rộng của cột dựa trên độ dài của nội dung
                    colWidths[colIdx] = Math.max(colWidths[colIdx], cellValue.length);
                });
            });

            // Đặt chiều rộng cột cho mỗi cột trong worksheet
            ws['!cols'] = colWidths.map(function(width) {
                return { wch: width }; // wch: width in characters
            });

            XLSX.utils.book_append_sheet(wb, ws, "Sheet1");

            XLSX.writeFile(wb, "bao_cao_thanh_vien_duoc_gioi_thieu_boi_thanh_vien_f1.xlsx");
        });
    </script>
@endsection
