@include('admin.sample_message.list')
@section('script')
    <script>
        var fnserverparams = {};
        var oTable;
        oTable = InitDataTable('#table_sample_message', 'admin/sample_message/getSampleMessage', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/sample_message/getSampleMessage",
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
                {data: 'message', name: 'message'},
                {data: 'type', name: 'type',width: "100px"},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });
    </script>
@endsection
