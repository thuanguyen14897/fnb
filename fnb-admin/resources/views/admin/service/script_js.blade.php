<script>
    var arrIdOtherAmenitiesCar = {{!empty($arrIdOtherAmenitiesCar) ? json_encode($arrIdOtherAmenitiesCar) : '[]'}};
    $(document).ready(function () {
        searchAjaxSelect2('#group_category_service_id','admin/category/searchGroupCategoryService')
        searchAjaxSelect2('#customer_id','admin/category/searchCustomer')
        searchAjaxSelect2('#province_id','api/category/getListProvince',0,{
            'select2':true
        })
        searchAjaxSelect2('#wards_id','api/category/getListWard',0,{
            'select2':true,
            province_id: {{!empty($dtData['province_id']) ? $dtData['province_id'] : -1}},
        })
        searchAjaxSelect2('#category_service_id','admin/category/searchCategoryService',0,
            {
                group_category_service_id: {{!empty($dtData['group_category_service_id']) ? $dtData['group_category_service_id'] : -1}},
            }
        );
    })

    function changeProvince(_this){
        var province_id = $(_this).val();
        searchAjaxSelect2('#wards_id','api/category/getListWard',0,{
            'select2':true,
            province_id: province_id,
        })
        $('select#wards_id').val(null).trigger('change.select2')
    }

    function changeGroupCategory(_this){
        var group_category_service_id = $(_this).val();
        searchAjaxSelect2('#category_service_id','admin/category/searchCategoryService',0,
            {
                group_category_service_id: group_category_service_id,
            }
        );
        $('select#category_service_id').val(null).trigger('change.select2')
    }

    $(document).on('click', '.chosen_other_amenities_car', function (e) {
        id = $(this).closest('div.other_amenities_car').find('input.value_other_amenities_car').val();
        if ($(this).hasClass('active_new')) {
            $(this).removeClass('active_new');

        } else {
            $(this).addClass('active_new');
        }
        id = intVal(id);
        index = jQuery.inArray(id, arrIdOtherAmenitiesCar);
        if (index != -1) {
            arrIdOtherAmenitiesCar.splice(index, 1);
        } else {
            arrIdOtherAmenitiesCar.push(id);
        }
    })

    $(document).on('change', '.type_lunch_break', function (e) {
        checked = $(this).is(':checked');
        if (checked) {
            $(".wrap_hour").removeClass('hide');
        } else {
            $(".wrap_hour").addClass('hide');
        }
    })

    $("#serviceForm").validate({
        rules: {
            name: {
                required: true,
            },
            number_car: {
                required: true,
            },
            year_manu: {
                required: true,
            },
            company_car_id: {
                required: true,
            },
            model_car_id: {
                required: true,
            },
            type_car_id: {
                required: true,
            },
            // customer_id: {
            //     required: true,
            // },
        },
        messages: {
            name: {
                required: "{{lang('dt_required')}}",
            },
            number_car: {
                required: "{{lang('dt_required')}}",
            },
            year_manu: {
                required: "{{lang('dt_required')}}",
            },
            company_car_id: {
                required: "{{lang('dt_required')}}",
            },
            model_car_id: {
                required: "{{lang('dt_required')}}",
            },
            type_car_id: {
                required: "{{lang('dt_required')}}",
            },
            {{--customer_id: {--}}
            {{--    required: "{{lang('dt_required')}}",--}}
            {{--},--}}

        },
        submitHandler: function (form) {
            var url = form.action;
            var form = $(form),
                formData = new FormData(),
                formParams = form.serializeArray();

            $.each(form.find('input[type="file"]'), function (i, tag) {
                $.each($(tag)[0].files, function (i, file) {
                    formData.append(tag.name, file);
                });
            });
            $.each(formParams, function (i, val) {
                formData.append(val.name, val.value);
            });
            formData.append('other_amenities', arrIdOtherAmenitiesCar);

            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: formData,
            })
                .done(function (data) {
                    if (data.result) {
                        alert_float('success',data.message);
                        window.location.href='admin/service/list';
                    } else {
                        alert_float('error',data.message);
                    }
                })
                .fail(function (err) {
                    htmlError = '';
                    for (var [ el, message ] of Object.entries(err.responseJSON.errors)) {
                        htmlError += `<div>${message}</div>`;
                    }
                    alert_float('error',htmlError);
                });
            return false;
        }
    });
</script>
