<form id="OtherForm" action="admin/other_amenities_service/detail/{{$id}}" method="post" data-parsley-validate novalidate>
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
                        <input type="hidden" name="id" value="{{!empty($dtData) ? $dtData['id'] : 0}}" >
                        <div class="show_error" style="color: red"></div>
                        <div class="form-group">
                            <label for="image">{{lang('dt_image')}}</label>
                            <input type="file" name="image" id="image" class="filestyle image"
                                   data-buttonbefore="true">
                            @if(!empty($dtData) && $dtData['image'] != null)
                                @php
                                    $dtImage = !empty($dtData['image']) ? $dtData['image'] : null;
                                @endphp
                                {!! loadImageNew($dtImage,'50px','img-rounded','',false) !!}
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="name">{{lang('dt_name_other_amenities_car')}}</label>
                            <input type="text" name="name" class="form-control name" value="{{!empty($dtData) ? $dtData['name'] : ''}}">
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
    $("#OtherForm").validate({
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
