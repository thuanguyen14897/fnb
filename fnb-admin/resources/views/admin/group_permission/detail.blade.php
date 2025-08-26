<form action="admin/group_permission/submit/{{$id}}" method="post" id="FormGroupPermission" data-parsley-validate novalidate>
    {{csrf_field()}}
    <div class="modal-dialog modal-lg" id="modal_group_permission">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">{{$title}}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="id" value="{{!empty($groupPermission) ? $groupPermission['id'] : 0}}" >
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name">{{lang('dt_name_group_permission')}}</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{!empty($groupPermission) ? $groupPermission['name'] : ''}}">
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
    </div>
</form>
<script>
    $("#FormGroupPermission").validate({
        rules: {
        },
        messages: {
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
                        if (data.message.length > 0){
                            $.each(data.message,function (k,v){
                                htmlError += `<div>${v}</div>`;
                            })
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

