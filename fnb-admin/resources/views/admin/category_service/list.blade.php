@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default dropdown-toggle waves-effect waves-light dt-modal" data-toggle="dropdown"
                   aria-expanded="false" href="admin/category_service/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title">{{lang('Danh mục dịch vụ')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/category_service/list">{{lang('Danh mục dịch vụ')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <div class="row m-b-10">
                    <div class="col-md-2">
                        <label for="group_category_search_service">{{lang('Nhóm danh mục')}}</label>
                        <select class="group_category_search_service select2" id="group_category_search_service"
                                data-placeholder="Chọn ..." name="group_category_search_service">
                            <option></option>
                        </select>
                    </div>
                </div>
                <table id="table_category_service" class="table table-bordered table_category_service">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('STT')}}</th>
                        <th class="text-center">Icon</th>
                        <th class="text-center">{{lang('Tên')}}</th>
                        <th class="text-center">{{lang('Tiện ích')}}</th>
                        <th class="text-center">{{lang('Nhóm danh mục')}}</th>
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
            'group_category_search_service': '#group_category_search_service'
        };

        $(function() {
            searchAjaxSelect2('#group_category_search_service','admin/category/searchGroupCategoryService')
            oTable = InitDataTable('#table_category_service', 'admin/category_service/getListCategoryService', {
                'order': [
                    [0, 'desc']
                ],
                'responsive': false,
                "ajax": {
                    "type": "POST",
                    "url": "admin/category_service/getListCategoryService",
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
                    {data: 'name', name: 'name'},
                    {data: 'other_amenities', name: 'other_amenities',width: "400px"},
                    {data: 'group_category_service', name: 'group_category_service',width: "150px"},
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
