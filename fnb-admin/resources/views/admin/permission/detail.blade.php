<form action="admin/permission/submit/{{$id}}" method="post" id="FormPermission" data-parsley-validate
      novalidate>
    {{csrf_field()}}
    <div class="modal-dialog modal-lg" id="modal_permission">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">{{$title}}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="id" value="{{!empty($permission) ? $permission['id'] : 0}}" >
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name">{{lang('dt_name_permission')}}</label>
                                <select class="form-control name" name="name" id="name"
                                        style="width: 100%;height: 35px">
                                    <option value=""></option>
                                    @foreach(Config::get('permission')['permissions'] as $key => $value)
                                        <option
                                            {{!empty($permission) && $permission->name == $value['id'] ? 'selected': ''}} value="{{$value['id']}}">{{lang($value['name'])}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="group_permission_id">{{lang('dt_group_permission')}}</label>
                                <select class="group_permission_id form-control" name="group_permission_id" id="group_permission_id"
                                        style="width: 100%;height: 35px">
                                    <option value=""></option>
                                    @foreach($groupPermission as $key => $value)
                                        <option
                                            {{!empty($permission) && $permission->group_permission_id == $value->id ? 'selected': ''}} value="{{$value->id}}">{{$value->name}}</option>
                                    @endforeach
                                </select>
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
    $("#FormPermission").validate({
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
                        $(".show_error").html(data.message);
                        alert_float('error',data.message);
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

