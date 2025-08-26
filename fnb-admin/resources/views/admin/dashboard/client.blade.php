<style>
</style>
<div class="modal-dialog transaction-modal" style="width: 80%;">
    <div class="modal-content" style="background: #fff">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">{{$title}}</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <input type="hidden" name="object_id" id="object_id" class="object_id" value="{{$object_id}}">
                <input type="hidden" name="type" id="type" class="type" value="{{$type}}">
                <table id="table_client" class="table table-bordered table_client">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('c_avatar_client')}}</th>
                        <th class="text-center">Mã KH</th>
                        <th class="text-center">{{lang('c_fullname_client')}}</th>
                        <th class="text-center">{{lang('c_phone_client')}}</th>
                        <th class="text-center">{{lang('c_email_client')}}</th>
                        <th class="text-center">Mã giới thiệu</th>
                        <th class="text-center">{{lang('dt_date_created_customer')}}</th>
                        <th class="text-center">Số dư ví ({{get_option('money_unit')}})</th>
                        <th class="text-center">Gói thành viên</th>
                        <th class="text-center">Hạng thành viên</th>
                        <th class="text-center">Username</th>
                        <th class="text-center">{{lang('c_active_client')}}</th>
                        <th class="text-center">{{lang('dt_actions')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default"
                    data-dismiss="modal">{{lang('dt_close')}}</button>
        </div>
    </div>
</div>
<script>
    var oTableClient;
    var fnserverparamsClient = {
        'province_search' : '#province_search',
        'active_search' : '#active_search',
        'date_search' : '#date_search',
        'object_id' : '#object_id',
        'type' : '#type',
    };

    $(function() {
        search_daterangepicker('date_search');
        oTableClient = InitDataTable('#table_client', 'admin/clients/getClients', {
            'order': [
                [6, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/clients/getClients",
                "data": function (d) {
                    for (var key in fnserverparamsClient) {
                        d[key] = $(fnserverparamsClient[key]).val();
                    }
                },
                "dataSrc": function (json) {
                    return json.data;
                }
            },
            columnDefs: [
                {data: 'avatar', name: 'avatar',width: "90px",},
                {data: 'code', name: 'code',width: "110px",},
                {data: 'fullname', name: 'fullname'},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'phone', name: 'phone'
                },
                {data: 'email', name: 'email'},
                {
                    data: 'referral_code_customer', name: 'referral_code_customer',width: "120px",
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                },
                {data: 'created_at', name: 'created_at'},
                {
                    data: 'account_balance', name: 'account_balance',
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</div>`;
                    },
                },
                {
                    data: 'customer_class', name: 'customer_class',
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                },
                {
                    data: 'class_customer', name: 'class_customer',
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                },
                {
                    data: 'referral_code', name: 'referral_code',width: "150px",
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'active', name: 'active'
                },
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px",visible:false },

            ]
        })

        $.each(fnserverparamsClient, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTable.draw('page')
            });
        });

        $('#table_client').on('draw.dt', function () {
            getCountClients();
        });

        function getCountClients() {
            var data = {};
            $.each(fnserverparamsClient, function(filterIndex, filterItem) {
                data[filterIndex] = $(filterItem).val();
            });
            $.post('admin/clients/getCountClients', data, function(response) {
                var total = 0;
                if(response.arrType.length > 0){
                    $.each(response.arrType, function(index, value) {
                        $(`.count_type_${value.id}`).text(formatNumber(value.count));
                        total += parseFloat(value.count);
                    })
                }
                $(`.count_all`).text(formatNumber(total));
            })
        }
    })
</script>
