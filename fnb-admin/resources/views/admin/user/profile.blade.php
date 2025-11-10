<form id="profileForm" action="admin/user/profile/{{$id}}" method="post" data-parsley-validate novalidate>
    {{csrf_field()}}
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{$title}}</h4>
            </div>
            <div class="modal-body ">
                <div class="form-group">
                    <label for="userName">Email</label>
                    <input type="email" name="email" parsley-trigger="change" required placeholder="Nhập email" disabled
                           value="{{$dtData->email}}" class="form-control">
                </div>
                <div class="row">
                    <div class="col-md-12" style="display:flex;">
                        <div style="color: red">Click vào đây để có nhu cầu đổi password</div> <div style="margin-left: 5px"><input type="checkbox" class="form-control-sm" id="changepass" name="checkpassword"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="userName">Mật khẩu</label>
                    <input type="password" name="password" disabled="true" parsley-trigger="change"
                           placeholder="Nhập password" class="form-control password">
                </div>
                <div class="form-group">
                    <label for="name">Họ và tên</label>
                    <input type="text" name="name" parsley-trigger="change" required placeholder="Nhập họ tên"
                           value="{{$dtData->name}}" class="form-control">
                </div>
                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="text" name="phone" parsley-trigger="change" required placeholder="Nhập số điện thoại"
                           value="{{$dtData->phone}}" class="form-control">
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
    $("#changepass").on('change',function () {
        if($(this).is(":checked")){
            $(".password").removeAttr('disabled')
        }else{
            $(".password").attr('disabled','')
        }
    });
    $("#profileForm").validate({
        rules: {},
        messages: {},
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
                        alert_float('success', data.message);
                    } else {
                        htmlError = '';
                        if (data.message.length > 0) {
                            $.each(data.message, function (k, v) {
                                htmlError += `<div>${v}</div>`;
                            })
                        }
                        $(".show_error").html(htmlError);
                        alert_float('error', htmlError);
                    }
                })
                .fail(function (err) {
                    htmlError = '';
                    for (var [el, message] of Object.entries(err.responseJSON.errors)) {
                        htmlError += `<div>${message}</div>`;
                    }
                    $(".show_error").html(htmlError);
                    alert_float('error', htmlError);
                });
            return false;
        }
    });
</script>
