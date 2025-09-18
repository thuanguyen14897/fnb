<form id="MemberShipLevelForm" action="admin/membership_level/submit_detail/{{$id}}" method="post" data-parsley-validate novalidate>
    {{csrf_field()}}
    <div class="modal-dialog modal-lg">
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
                            <input name="name" class="form-control name" value="{{!empty($dtData) ? $dtData->name : ''}}">
                        </div>
                        <div class="form-group hide">
                            <label for="color_header">Màu nền Header</label>
                            <input type="color" name="color_header" id="colorPicker" class="form-control color_header" value="{{!empty($dtData) ? $dtData->color_header : ''}}">
                        </div>
                        <div class="form-group">
                            <label for="color_background">Màu nền</label>
                            <input type="color" name="color_background" id="colorPicker" class="form-control color_background" value="{{!empty($dtData) ? $dtData->color_background : ''}}">
                        </div>
                        <div class="form-group">
                            <label for="color">Màu chữ</label>
                            <input type="color" name="color" id="colorPicker" class="form-control color" value="{{!empty($dtData) ? $dtData->color : ''}}">
                        </div>
                        <div class="form-group">
                            <label for="color_button">Màu nút</label>
                            <input type="color" name="color_button" id="colorPicker" class="form-control color_button" value="{{!empty($dtData) ? $dtData->color_button : ''}}">
                        </div>

                        <hr/>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-8">
                                    <label for="background_header">{{lang('c_background_header')}}</label>
                                    <input type="file" name="background_header" id="background_header" class="filestyle background_header"
                                           data-buttonbefore="true">
                                </div>
                                <div class="col-md-4">
                                    @if(!empty($dtData) && $dtData->background_header != null)
                                        @php
                                            $dtImage = !empty($dtData->background_header) ? asset('storage/'.$dtData->background_header) : null;
                                        @endphp
                                        {!! loadImageNew($dtImage,'150px','img-rounded','',false, 'auto') !!}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <hr/>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-8">
                                    <label for="icon">{{lang('Icon')}}</label>
                                    <input type="file" name="icon" id="icon" class="filestyle icon"
                                       data-buttonbefore="true">
                                </div>
                                <div class="col-md-4 m-t-20">
                                    @if(!empty($dtData) && $dtData->icon != null)
                                        @php
                                            $dtImage = !empty($dtData->icon) ? asset('storage/'.$dtData->icon) : null;
                                        @endphp
                                        {!! loadImageNew($dtImage,'40px','img-rounded','',false) !!}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <hr/>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-8">
                                    <label for="image">{{lang('Ảnh nền thẻ')}}</label>
                                    <input type="file" name="image" id="image" class="filestyle image"
                                           data-buttonbefore="true">
                                </div>
                                <div class="col-md-4">
                                    @if(!empty($dtData) && $dtData->image != null)
                                        @php

                                            $dtImage = !empty($dtData->image) ? asset('storage/'.$dtData->image) : null;
                                        @endphp
                                        {!! loadImageNew($dtImage,'150px','img-rounded','',false, 'auto') !!}
                                    @endif
                                </div>
                            </div>
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
    $("#MemberShipLevelForm").validate({
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
