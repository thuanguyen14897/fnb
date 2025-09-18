@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default dropdown-toggle waves-effect waves-light dt-modal" data-toggle="dropdown"
                   aria-expanded="false" href="admin/group_category_service/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title">{{lang('Nhóm danh mục dịch vụ')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/group_category_serive/list">{{lang('Nhóm danh mục dịch vụ')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <div class="row m-b-10">
                </div>
                <table id="table_group_category_service" class="table table-bordered table_group_category_service">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('STT')}}</th>
                        <th class="text-center">Icon</th>
                        <th class="text-center">Hình ảnh</th>
                        <th class="text-center">Hình nền</th>
                        <th class="text-center">{{lang('Tên')}}</th>
                        <th class="text-center">{{lang('Màu sắc')}}</th>
                        <th class="text-center">{{lang('Màu sắc viền')}}</th>
                        <th class="text-center">{{lang('Trạng thái')}}</th>
                        <th class="text-center">{{lang('Sắp xếp')}}</th>
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
        };

        $(function() {
            oTable = InitDataTable('#table_group_category_service', 'admin/group_category_service/getListGroupCategoryService', {
                'order': [
                    [0, 'desc']
                ],
                'responsive': false,
                "ajax": {
                    "type": "POST",
                    "url": "admin/group_category_service/getListGroupCategoryService",
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
                    {data: 'icon', name: 'icon',width: "90px",},
                    {data: 'image', name: 'image',width: "120px",},
                    {data: 'background', name: 'background',width: "120px",},
                    {data: 'name', name: 'name'},
                    {data: 'color', name: 'color',width: "120px"},
                    {data: 'color_border', name: 'color_border',width: "120px"},
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'active', name: 'active',width: "110px",
                    },
                    {data: 'index', name: 'index',width: "90px",},
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
