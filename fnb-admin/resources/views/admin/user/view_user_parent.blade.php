<div class="modal-dialog" style="width: 60%">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">{{$title}}</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div style="border: 1px solid #eee;border-radius: 5px;padding: 5px;margin-bottom: 5px; font-style: italic;font-weight: 500;">
                        <div>Nhân viên : <span>{{$user->name}}</span></div>
                        <div>Chức vụ : <span>{{$user->str_role}}</span></div>
                        <div>Phòng ban : <span>{{$user->str_department}}</span></div>
                    </div>
                </div>
               <div class="col-md-12" style="font-size: 17px;font-style: italic;color: red;">Nhân viên cấp trên</div>
                <div class="col-md-12">
                    <div class="card-box table-responsive">
                        <table id="table_user_parent" class="table table-bordered table_user_parent">
                            <thead>
                            <tr>
                                <th class="text-center">{{lang('dt_stt')}}</th>
                                <th class="text-center">{{lang('Hình ảnh')}}</th>
                                <th class="text-center">{{lang('Mã NV')}}</th>
                                <th class="text-center">{{lang('Họ và Tên')}}</th>
                                <th class="text-center">{{lang('Chứ Vụ')}}</th>
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-12" style="font-size: 17px;font-style: italic;color: green;">Nhân viên cấp dưới</div>
                <div class="col-md-12">
                    <div class="card-box table-responsive">
                        <table id="table_user_child" class="table table-bordered table_user_child">
                            <thead>
                            <tr>
                                <th class="text-center">{{lang('dt_stt')}}</th>
                                <th class="text-center">{{lang('Hình ảnh')}}</th>
                                <th class="text-center">{{lang('Mã NV')}}</th>
                                <th class="text-center">{{lang('Họ và Tên')}}</th>
                                <th class="text-center">{{lang('Chứ Vụ')}}</th>
                                <th class="text-center">{{lang('Phòng ban')}}</th>
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default"
                    data-dismiss="modal">{{lang('dt_close')}}</button>
        </div>
    </div>
</div>
<script type=text/javascript>
    var fnserverparams = {};
    var oTableUser;
    var oTableUserChild;
    oTableUser = InitDataTable('#table_user_parent', 'admin/user/getUserParent', {
        'order': [
            [0, 'desc']
        ],
        'responsive': true,
        "ajax": {
            "type": "POST",
            "url": "admin/user/getUserParent/{{$id}}",
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
            {   "render": function (data, type, row) {
                    return `<div class="text-center">${data}</data>`;
                },
                data: 'DT_RowIndex', name: 'DT_RowIndex',width: "80px" },
            {data: 'image', name: 'image',width: "80px" },
            {data: 'code', name: 'code'},
            {data: 'name', name: 'name'},
            {data: 'role', name: 'role'},
            {data: 'department', name: 'department'},

        ]
    });

    oTableUserChild = InitDataTable('#table_user_child', 'admin/user/getUserChild', {
        'order': [
            [0, 'desc']
        ],
        'responsive': true,
        "ajax": {
            "type": "POST",
            "url": "admin/user/getUserChild/{{$id}}",
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
            {   "render": function (data, type, row) {
                    return `<div class="text-center">${data}</data>`;
                },
                data: 'DT_RowIndex', name: 'DT_RowIndex',width: "80px" },
            {data: 'image', name: 'image',width: "80px" },
            {data: 'code', name: 'code'},
            {data: 'name', name: 'name'},
            {data: 'role', name: 'role'},
            {data: 'department', name: 'department'},

        ]
    });
</script>
