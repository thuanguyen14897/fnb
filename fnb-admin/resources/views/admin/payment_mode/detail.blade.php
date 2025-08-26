<form id="PaymentModeForm" action="admin/payment_mode/submit/{{$id}}" method="post" data-parsley-validate novalidate>
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
                        <input type="hidden" name="id" value="{{!empty($paymentMode) ? $paymentMode['id'] : 0}}" >
                        <div class="show_error" style="color: red"></div>
                        <div class="form-group">
                            <label for="image">{{lang('dt_image')}}</label>
                            <input type="file" name="image[]" multiple id="image" class="filestyle image"
                                   data-buttonbefore="true">
                            @if(!empty($paymentMode) && $paymentMode->image != null)
                                <input type="hidden" name="image_old" id="image_old"
                                       class="image_old"
                                       data-buttonbefore="true" value="{{!empty($paymentMode) ? $paymentMode->image : ''}}">
                                {!! loadImage(asset('storage/'.$paymentMode->image)) !!}
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="name">{{lang('dt_name_payment_mode')}}</label>
                            <input type="text" name="name" class="form-control name" value="{{!empty($paymentMode) ? $paymentMode['name'] : ''}}">
                        </div>
                        <div class="form-group">
                            <label for="balance">Số dư đầu kỳ</label>
                            <input type="text" name="balance" class="form-control balance" onchange="formatNumBerKeyChange(this)" value="{{!empty($paymentMode) ? formatMoney($paymentMode['balance']) : ''}}">
                        </div>
                        <div class="form-group">
                            <label for="name">{{lang('dt_type')}}</label>
                            <select class="type select2 form-control" name="type" id="type">
                                <option value="1" {{!empty($paymentMode) && $paymentMode['type'] == 1 ? 'selected' : ''}}>Tiền mặt</option>
                                <option value="2" {{!empty($paymentMode) && $paymentMode['type'] == 2 ? 'selected' : ''}}>Ngân hàng</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="name">{{lang('dt_note')}}</label>
                            <textarea name="note" class="form-control note">{{!empty($paymentMode) ? $paymentMode['note'] : ''}}</textarea>
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
    $("#type").select2();
    $("#PaymentModeForm").validate({
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
