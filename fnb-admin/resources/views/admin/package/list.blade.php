@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default dropdown-toggle waves-effect waves-light dt-modal" data-toggle="dropdown"
                   aria-expanded="false" href="admin/package/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title">{{lang('Gói thành viên')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/package/list">{{lang('Gói thành viên')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <div class="row m-b-10">
                    <div class="col-md-2">
                        <label for="type_search">Loại</label>
                        <select class="type_search select2" id="type_search"
                                data-placeholder="Chọn ..." name="type_search">
                            <option value="0">Tất cả</option>
                            @foreach(getListTypePackage() as $key => $val)
                                <option value="{{$val['id']}}">{{$val['name']}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <table id="table_package" class="table table-bordered table_package">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('STT')}}</th>
                        <th class="text-center">{{lang('Tên')}}</th>
                        <th class="text-center">{{lang('Số ngày')}}</th>
                        <th class="text-center">{{lang('Số tiền')}}</th>
                        <th class="text-center">{{lang('Phần trăm giảm giá')}}</th>
                        <th class="text-center">{{lang('Ghi chú')}}</th>
                        <th class="text-center">{{lang('Loại')}}</th>
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
            'type_search': '#type_search'
        };

        $(function() {
            oTable = InitDataTable('#table_package', 'admin/package/getListPackage', {
                'order': [
                    [0, 'desc']
                ],
                'responsive': false,
                "ajax": {
                    "type": "POST",
                    "url": "admin/package/getListPackage",
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
                    {data: 'name', name: 'name'},
                    {data: 'number_day', name: 'number_day',width: "120px"},
                    {data: 'total', name: 'total',width: "150px"},
                    {data: 'percent', name: 'percent',width: "130px"},
                    {data: 'note', name: 'note',width: "150px"},
                    {data: 'type', name: 'type',width: "120px"},
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
