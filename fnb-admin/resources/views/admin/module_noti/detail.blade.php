<form id="moduleNotiForm" action="admin/module_noti/submit/{{$id}}" method="post" data-parsley-validate novalidate>
    {{csrf_field()}}
    <div class="modal-dialog" style="width: 60%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{$title}}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Tên</label>
                                <input name="name" class="form-control name" value="{{!empty($moduleNoti) ? $moduleNoti->name : ''}}">
                            </div>
                            <div class="form-group">
                                <label for="type_user">Khách hàng</label>
                                <select class="type_user select2" onchange="changeTypeUser(this)" id="type_user"
                                        data-placeholder="Chọn ..." name="type_user">
                                    <option></option>
                                    @foreach($dtTypeUser as $typeUser)
                                        <option
                                            @if(!empty($moduleNoti))
                                                @if($moduleNoti->type_user == $typeUser['id'])
                                                    {{'selected'}}
                                                @endif
                                            @endif
                                            value="{{$typeUser['id']}}">{{$typeUser['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group wrap_customer {{!empty($moduleNoti) ? ($moduleNoti->type_user == 5 ? '' : 'hide') : 'hide'}}">
                                <label for="customer_id">Chọn 1 khách để test</label>
                                <select class="customer_id" id="customer_id"
                                        data-placeholder="Chọn ..." name="customer_id">
                                    <option></option>
                                    @if(!empty($moduleNoti) && !empty($moduleNoti->customer_id))
                                        <option value="{{$moduleNoti->customer_id}}"
                                                selected>{{$moduleNoti->customer->fullname}} ({{$moduleNoti->customer->referral_code}})</option>
                                    @endif
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="note">{{lang('dt_note')}}</label>
                                <textarea name="note" class="form-control note editor">{{!empty($moduleNoti) ? $moduleNoti->content : ''}}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="image">Banner</label>
                                <input type="file" name="banner" multiple id="banner" class="filestyle banner"
                                       data-buttonbefore="true">
                                @if(!empty($moduleNoti) && $moduleNoti->banner != null)
                                    {!! loadImageNew(asset('storage/'.$moduleNoti->banner),'300px','img-rounded',$moduleNoti->banner,true,'250px','banner_old') !!}
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="image">{{lang('dt_image')}}</label>
                                <input type="file" name="image" multiple id="image" class="filestyle image"
                                       data-buttonbefore="true">
                                @if(!empty($moduleNoti) && $moduleNoti->image != null)
                                    {!! loadImageNew(asset('storage/'.$moduleNoti->image),'80px','img-rounded',$moduleNoti->image,true,'80px','image_old') !!}
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="detail">Mô tả ngắn</label>
                                <textarea name="detail" class="form-control detail">{{!empty($moduleNoti) ? $moduleNoti->detail : ''}}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="type">Loại</label>
                                <select class="type select2" onchange="changeType(this)" id="type"
                                        data-placeholder="Chọn ..." name="type">
                                    <option></option>
                                    @foreach($dtType as $type)
                                        <option
                                            @if(!empty($moduleNoti))
                                                @if($moduleNoti->type == $type['id'])
                                                    {{'selected'}}
                                                @endif
                                            @endif
                                            value="{{$type['id']}}">{{$type['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group show_date_send {{!empty($moduleNoti) ? ($moduleNoti->type == 2 ? '' : 'hide') : 'hide'}}">
                                <label for="date_send">Ngày gửi</label>
                                <input type="text" autocomplete="off" name="date_send" class="date_send form-control datetimepicker" value="{{!empty($moduleNoti) ? _dt($moduleNoti->date_send) : ''}}">
                            </div>
                            <div class="form-group show_day {{!empty($moduleNoti) ? ($moduleNoti->type == 1 ? '' : 'hide') : 'hide'}}">
                                <label for="day">Lặp lại hàng tuần</label>
                                <div style="display: flex;align-items: center;flex-wrap: wrap">
                                    @foreach (getListDay() as $key => $value)
                                        <div class="checkbox checkbox-info" style="margin-right: 15px;margin-top: 10px !important;">
                                            <input type="checkbox" name="day[]" {{!empty($arrDate) && in_array($value['id'],
                                                $arrDate) ? 'checked' : ''}}
                                            id="day_{{$value['id']}}"
                                                   value="{{ $value['id']}}">
                                            <label for="day_{{$value['id']}}">{{$value['name']}}</label>
                                        </div>
                                    @endforeach
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
    searchAjaxSelect2('#customer_id','admin/category/searchCustomer',0,{type_client:-1})
    $(".delete_image").click(function () {
        $(this).closest("div.show_image").remove();
    });
    initDatepicker();
    $("#screen_noti").select2({
        allowClear:true
    });
    $("#type").select2();
    $("#type_user").select2({
        dropdownParent: $(".modal-body")
    });

    $("#moduleNotiForm").validate({
        rules: {
            type: {
                required: true,
            },
            type_user: {
                required: true,
            },
            name: {
                required: true,
            },
        },
        messages: {
            type: {
                required: "{{lang('dt_required')}}",
            },
            type_user: {
                required: "{{lang('dt_required')}}",
            },
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

    function changeType(_this){
        value = $(_this).val();
        if(value == 2){
            $('.show_date_send').removeClass('hide')
            $('.show_day').addClass('hide')
            $("input.date_send").attr('required',true);
        } else {
            $('.show_date_send').addClass('hide')
            $('.show_day').removeClass('hide')
            $("input.date_send").attr('required',false);
        }
    }

    function changeTypeUser(_this){
        value = $(_this).val();
        if(value == 5){
            $('.wrap_customer').removeClass('hide')
        } else {
            $('.wrap_customer').addClass('hide')
        }
    }

    function changeTypeApp(_this) {
        type_app = $(_this).val();
        if(type_app == 1){
            searchAjaxSelect2('#customer_id','admin/category/searchCustomer',0,{type_client:-1})
        } else{
            searchAjaxSelect2('#customer_id','admin/category/searchDriver',0)
        }
        $("select.customer_id").select2("val"," ");
        $.ajax({
            url: 'admin/module_noti/getListScreenApp',
            type: 'GET',
            dataType: 'JSON',
            cache: false,
            data: {
                type_app: type_app,
            },
        })
            .done(function (data) {
                dataHtml = '<option></option>';
                $.each(data.dataScreen, function (k, v) {
                    dataHtml += `<option value="${k}">${v}</option>`;
                })
                $("select.screen_noti").html(dataHtml);
            })
            .fail(function () {

            });
        return false;
    }
</script>
