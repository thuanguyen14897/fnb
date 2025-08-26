<form id="groupCategoryServiceForm" action="admin/group_category_service/detail/{{$id}}" method="post" data-parsley-validate novalidate>
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
                            <label for="index">Index</label>
                            <input type="number" name="index" class="form-control index" onkeyup="formatNumBerKeyChange(this)" min="1" max="6" value="{{!empty($dtData) ? $dtData['index'] : ''}}">
                        </div>
                        <div class="form-group">
                            <label for="color">Màu sắc</label>
                            <input type="color" name="color" id="colorPicker" class="form-control color" value="{{!empty($dtData) ? $dtData['color'] : ''}}">
                        </div>
                        <div class="form-group">
                            <label for="color_border">Màu sắc viền</label>
                            <input type="color" name="color_border" id="colorPicker" class="form-control color_border" value="{{!empty($dtData) ? $dtData['color_border'] : ''}}">
                        </div>
                        <div class="form-group">
                            <label for="icon">{{lang('Icon')}}</label>
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
                            <label for="image">{{lang('dt_image')}}</label>
                            <input type="file" name="image" id="image" class="filestyle image"
                                   data-buttonbefore="true">
                            @if(!empty($dtData) && $dtData['image'] != null)
                                @php
                                    $dtImage = !empty($dtData['image']) ? $dtData['image'] : null;
                                @endphp
                                {!! loadImageNew($dtImage,'75px','img-rounded','',false) !!}
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="background">{{lang('Hình nền')}}</label>
                            <input type="file" name="background" id="background" class="filestyle background"
                                   data-buttonbefore="true">
                            @if(!empty($dtData) && $dtData['background'] != null)
                                @php
                                    $dtImage = !empty($dtData['background']) ? $dtData['background'] : null;
                                @endphp
                                {!! loadImageNew($dtImage,'100px','img-rounded','',false) !!}
                            @endif
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
    initDatepicker();
    $("#groupCategoryServiceForm").validate({
        rules: {
            name: {
                required: true,
            },
            color: {
                required: true,
            },
        },
        messages: {
            name: {
                required: "{{lang('dt_required')}}",
            },
            color: {
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
</script>
