@extends('admin.layouts.index')
@section('content')
    <div class="row" style="height: calc(100vh - 260px);display: flex;justify-content: center;align-items: center;">
        <div class="col-md-12 text-center">
            <img src="admin/assets/images/logo_login.png" style="width: 150px">
            <div style="font-size: 18px;color: black;">Ẩm Thực, Dịch Vụ, Trọn Vẹn</div>
        </div>
    </div>

    <!-- end row -->
@endsection
@section('script')
    <script src="admin/assets/pages/jquery.dashboard.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/data.js"></script>
    <script src="https://code.highcharts.com/modules/drilldown.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script src="https://code.highcharts.com/modules/funnel.js"></script>
@endsection
