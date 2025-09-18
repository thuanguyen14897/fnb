<form id="packageForm" action="admin/package/detail/{{$id}}" method="post" data-parsley-validate novalidate>
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
                            <label for="name">Tên gói</label>
                            <input name="name" class="form-control name" value="{{!empty($dtData) ? $dtData['name'] : ''}}">
                        </div>
                        <div class="form-group">
                            <label for="number_day">Số ngày</label>
                            <input type="text" name="number_day" class="form-control number_day" onkeyup="formatNumBerKeyChange(this)" value="{{!empty($dtData) ? $dtData['number_day'] : 0}}">
                        </div>
                        <div class="form-group">
                            <label for="total">Số tiền</label>
                            <input type="text" name="total" id="total" class="form-control total" onkeyup="formatNumBerKeyChange(this)" value="{{!empty($dtData) ? formatMoney($dtData['total']) : 0}}">
                        </div>
                        <div class="form-group">
                            <label for="percent">% giảm giá</label>
                            <input type="number" name="percent" id="percent" class="form-control" min="0" max="100" onkeyup="formatNumBerKeyChange(this)" value="{{!empty($dtData) ? $dtData['percent'] : 0}}">
                        </div>
                        <div class="form-group hide">
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
                            <label for="note">Ghi chú</label>
                            <textarea class="form-control note" name="note" cols="2" rows="2">{{!empty($dtData) ? $dtData['note'] : ''}}</textarea>
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
    $("#packageForm").validate({
        rules: {
            name: {
                required: true,
            },
            number_day: {
                required: true,
            },
            total: {
                required: true,
            },
        },
        messages: {
            name: {
                required: "{{lang('dt_required')}}",
            },
            number_day: {
                required: "{{lang('dt_required')}}",
            },
            total: {
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
