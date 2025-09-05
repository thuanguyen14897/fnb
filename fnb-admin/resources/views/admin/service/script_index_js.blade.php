<script>
    $(document).ready(function () {
        searchAjaxSelect2('#customer_search', 'admin/category/searchCustomer')
        searchAjaxSelect2('#group_category_service_search', 'admin/category/searchGroupCategoryService')
        searchAjaxSelect2('#category_service_search', 'admin/category/searchCategoryService')
        searchAjaxSelect2('#province_search', 'api/category/getListProvince',0,{
            'select2':true
        })
        searchAjaxSelect2('#ward_search', 'api/category/getListWard',0,{
            'select2':true
        })

        searchAjaxSelect2('#customer_search_favourite', 'admin/category/searchCustomer')
        searchAjaxSelect2('#group_category_service_search_favourite', 'admin/category/searchGroupCategoryService')
        searchAjaxSelect2('#category_service_search_favourite', 'admin/category/searchCategoryService')
    })
    function changeProvince(_this){
        var province_id = $(_this).val();
        searchAjaxSelect2('#ward_search', 'api/category/getListWard',0,{
            'select2':true,
            'province_id':province_id
        })
    }
    arrSearch = [];
    arrSearchObject = [];
    var fnserverparams = {
        'group_category_service_search': '#group_category_service_search',
        'category_service_search': '#category_service_search',
        'status_search': '#status_search',
        'customer_search': '#customer_search',
        'customer_id': '#customer_id',
        'province_search': '#province_search',
        'ward_search': '#ward_search',
    };
    var oTable;
    oTable = InitDataTable('#table_service', 'admin/service/getList', {
        'order': [
            [0, 'desc']
        ],
        'responsive': true,
        "ajax": {
            "type": "POST",
            "url": "admin/service/getList",
            "data": function (d) {
                for (var key in fnserverparams) {
                    d[key] = $(fnserverparams[key]).val();
                }
                if (Object.keys(arrSearchObject).length > 0){
                    for (var key in arrSearchObject) {
                        d[key] = arrSearchObject[key];
                    }
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
                data: 'id', name: 'id',width: "50px"
            },
            {data: 'image', name: 'image',width: "120px" , orderable: false},
            {data: 'name', name: 'name',width: "250px" },
            {data: 'province_id', name: 'province_id',width: "150px"},
            {data: 'customer_id', name: 'customer_id',width: "150px"},
            {data: 'group_category_service_id', name: 'group_category_service_id',width: "100px"},
            {data: 'category_service_id', name: 'category_service_id',width: "100px"},
            {data: 'price', name: 'price',width: "100px"},
            {
                "render": function (data, type, row) {
                    return `<div class="text-center">${data}</div>`;
                },
                data: 'active', name: 'active',width: "100px"},
            {
                "render": function (data, type, row) {
                    return `<div class="text-center">${data}</div>`;
                },
                data: 'hot', name: 'hot',width: "100px"
            },
            {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

        ]
    });
    $.each(fnserverparams, function(filterIndex, filterItem) {
        $('' + filterItem).on('change', function() {
            oTable.draw('page')
        });
    });

    $('#table_car').on('draw.dt', function () {

    });

    function changeStatus(service_id,status){
        $.ajax({
            url: 'admin/service/active',
            type: 'POST',
            dataType: 'JSON',
            cache: false,
            data: {
                service_id: service_id,
                status: status,
            },
        })
            .done(function (data) {
                if(data.result){
                    alert_float('success',data.message);
                } else {
                    alert_float('error',data.message);
                }
                oTable.draw('page');
            })
            .fail(function () {

            });
        return false;
    }
</script>
