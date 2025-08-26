<style>
    .wrap_other_amenities{
        border: 1px solid #eee;
        border-radius: 5px;
        padding: 5px;
        flex-wrap: wrap;
    }
    .item_other_amenities{
        border: 1px solid #675a5a;
        padding: 5px;
        border-radius: 5px;
        margin-right: 5px;
    }
</style>
<form id="CategoryServiceForm" action="admin/category_service/detail/{{$id}}" method="post" data-parsley-validate novalidate>
    {{csrf_field()}}
    <div class="modal-dialog" style="width: 30%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{$title}}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="name">Tên</label>
                            <input name="name" class="form-control name" value="{{!empty($dtData) ? $dtData['name'] : ''}}">
                        </div>
                        <div class="form-group">
                            <label for="image">{{lang('dt_image')}}</label>
                            <input type="file" name="icon" id="icon" class="filestyle icon"
                                   data-buttonbefore="true">
                            @if(!empty($dtData) && $dtData['icon'] != null)
                                @php
                                    $dtImage = !empty($dtData['icon']) ? $dtData['icon'] : null;
                                @endphp
                                {!! loadImageNew($dtImage,'40px','img-rounded','',false) !!}
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="group_category_service_id">{{lang('Nhóm danh mục')}}</label>
                            <select class="group_category_service_id select2" id="group_category_service_id"
                                    data-placeholder="Chọn ..." name="group_category_service_id">
                                <option></option>
                                @if(!empty($dtData) && !empty($dtData['group_category_service_id']))
                                    <option value="{{$dtData['group_category_service_id']}}"
                                            selected>{{$dtData['group_category_service']['name']}}</option>
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="other_amenities">{{lang('Tiện ích')}}</label>
                            <select class="other_amenities select2" id="other_amenities"
                                    data-placeholder="Chọn ..." name="other_amenities">
                                <option></option>
                            </select>
                        </div>
                        <div class="wrap_other_amenities">
                            @forelse($arrIdOtherAmenitiesText as $key => $value)
                                <div class="item_other_amenities" data-id="{{$value['id']}}">{{$value['text']}} <span onclick="removeItem(this)" style="cursor: pointer"><i class="fa fa-remove"></i></span></div>
                            @empty
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary waves-effect waves-light"
                        type="submit">{{lang('dt_save')}}</button>
                <button type="button" class="btn btn-default"
                        data-dismiss="modal">{{lang('dt_close')}}</button>
            </div>
        </div>
    </div>
</form>
<script>
    $(".delete_image").click(function () {
        $(this).closest("div.show_image").remove();
    });
    searchAjaxSelect2('#group_category_service_id','admin/category/searchGroupCategoryService')
    searchAjaxSelect2('#other_amenities','admin/category/searchOtherAmenities')
    initDatepicker();
    var arrIdOtherAmenities = {{!empty($arrIdOtherAmenities) ? json_encode($arrIdOtherAmenities) : '[]'}};
    $("#CategoryServiceForm").validate({
        rules: {
            name: {
                required: true,
            },
        },
        messages: {
            name: {
                required: "{{lang('dt_required')}}",
            },
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

            formData.append('other_amenities', arrIdOtherAmenities);

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
                        oTable.draw();
                        $('.modal-dialog .close').trigger('click');
                        alert_float('success',data.message);
                    } else {
                        htmlError = '';
                        if( typeof (data.message) == 'object' && data.message.length > 0) {
                            $.each(data.message,function (k,v){
                                htmlError += `<div>${v}</div>`;
                            })
                        } else {
                            htmlError = data.message;
                        }
                        $(".show_error").html(htmlError);
                        alert_float('error',htmlError);
                    }
                })
                .fail(function (err) {
                    htmlError = '';
                    for (var [ el, message ] of Object.entries(err.responseJSON.errors)) {
                        htmlError += `<div>${message}</div>`;
                    }
                    $(".show_error").html(htmlError);
                    alert_float('error',htmlError);
                });
            return false;
        }
    });
    $("select#other_amenities").on('change', function () {
        dataSelect = $(this).select2('data');
        text = dataSelect[0].text;
        value = dataSelect[0].id;
        $(this).val(null).trigger('change.select2')
        if(arrIdOtherAmenities.includes(value)) {
            alert_float('error','Tiện ích đã được tồn tại!');
            return false;
        }
        if (value != null && value !== '') {
            var html = `<div class="item_other_amenities" data-id="${value}">${text} <span onclick="removeItem(this)" style="cursor: pointer"><i class="fa fa-remove"></i></span></div>`;
            $(".wrap_other_amenities").append(html);
        }
        getOtherAmenities();
    });

    function getOtherAmenities() {
        arrIdOtherAmenities = [];
        $(".wrap_other_amenities .item_other_amenities").each(function () {
            var value =  $(this).closest('div').attr('data-id')
            arrIdOtherAmenities.push(value);
        });
    }
    getOtherAmenities();

    function removeItem(_this){
        var value = $(_this).closest('div').attr('data-id');
        $(_this).closest('div').remove();
        getOtherAmenities();
    }
</script>
