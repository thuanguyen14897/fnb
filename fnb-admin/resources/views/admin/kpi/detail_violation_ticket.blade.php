<form id="violationTicketForm" action="admin/kpi/detail_violation_ticket/{{$id}}" method="post" data-parsley-validate novalidate>
    {{csrf_field()}}
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{$title}}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="date_new">{{lang('dt_date_new')}}</label>
                                <input type="text" name="date_new" class="form-control date_new datetimepicker" value="{{($dtData) ? _dt($dtData->date) : date('d/m/Y H:i')}}">
                            </div>
                            <div class="form-group">
                                <label for="reference_no">{{lang('dt_reference_no')}}</label>
                                <input type="text" name="reference_no" class="form-control reference_no" readonly value="{{$reference_no}}">
                            </div>
                            <div class="form-group">
                                <label for="staff_id">{{lang('Nhân viên')}}</label>
                                <select name="staff_id" class="staff_id select2" id="staff_id" data-placeholder="Chọn ..." >
                                    <option></option>
                                    @if(!empty($dtData) && !empty($dtData->user))
                                        <option value="{{$dtData->staff_id}}" selected="selected">{{$dtData->user->name}} ({{$dtData->user->code}})</option>
                                    @endif
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="note">{{lang('Nội dung vi phạm')}}</label>
                                <textarea name="note" class="form-control note">{{!empty($dtData) ? $dtData['note'] : ''}}</textarea>
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
    $(function() {
        searchAjaxSelect2('#staff_id', 'api/category/getListStaff', 0, {select2: true})
    });
    $("#violationTicketForm").validate({
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
