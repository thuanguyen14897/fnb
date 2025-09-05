@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title">{{lang('c_title_ares')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/ares/list">{{lang('c_title_ares')}}</a></li>
                <li class="active">{{$title}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <form action="admin/ares/updateSetup" method="post" id="formSetupAres" data-parsley-validate
              novalidate
              enctype="multipart/form-data">
            {{csrf_field()}}

            <div class="col-lg-12">
                <input type="hidden" name="id" value="{{$id ?? 0}}">
                <h4>Cài đặt khu vực: {{$ares->name ?? ''}}</h4>
                <div class="card-box">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table" id="table-setup-ares">
                                <thead>
                                    <tr>
                                        <th><a onclick="btnPlus()" class="btn btn-icon btn-primary"><i class="fa fa-plus"></i></a></th>
                                        <th>{{lang('dt_province')}}</th>
                                        <th>{{lang('dt_province_old')}}</th>
                                        <th>{{lang('dt_wards')}}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sttKey = 0;
                                    @endphp
                                    @if(!empty($ares->detail))
                                        @foreach($ares->detail as $key => $value)
                                            <tr class="item" data-class="{{$value->id_province}}" data-class-old="{{$value->id_province_old ?? 0}}">
                                                <td class="SttITem" style="width: 5%">{{($sttKey + 1)}}</td>
                                                <td style="width: 20%">
                                                    <div class="form-group">
                                                        <select class="select_province_id province_id-{{$sttKey}} select2" id="province_id-{{$sttKey}}" data-key="{{$sttKey}}"
                                                                data-placeholder="Chọn ..." name="item[{{$sttKey}}][province_id]"
                                                                onchange="changeProvince(this)">
                                                            <option value="{{$value->id_province}}">{{$value->name_province ?? ''}}</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td style="width: 20%">
                                                    <div class="form-group">
                                                        <select class="select_province_old_id province_old_id-{{$sttKey}} select2" id="province_old_id-{{$sttKey}}" data-key="{{$sttKey}}"
                                                                data-placeholder="Chọn ..." name="item[{{$sttKey}}][province_old_id]"
                                                                onchange="changeProvinceOld(this)">
                                                            <option value="0">Tất cả</option>
                                                            @foreach($value->province_sixty_four as $k => $v)
                                                                <option value="{{$v->provinceid}}" {{(!empty($value->id_province_old) && $value->id_province_old == $v->provinceid) ? 'selected' : ''}}>{{$v->name ?? ''}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <select class="ward_id-{{$sttKey}} select2" id="ward_id-{{$sttKey}}" multiple
                                                                data-placeholder="Chọn ..." name="item[{{$sttKey}}][ward_id][]">
                                                            @if(!empty($value->items))
                                                                @foreach($value->items as $k => $v)
                                                                    <option value="{{$v->Id}}" {{is_numeric(array_search($v->Id, $value->list_id)) ? 'selected' : ''}}>{{$v->Name}}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <a class="btn btn-danger btn-icon" onclick="removeTr(this)"><i class="fa fa-remove"></i></a>
                                                </td>
                                            </tr>
                                            @php
                                                $sttKey++;
                                            @endphp
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr/>
                    <div class="form-group text-right m-b-0">
                        <button class="btn btn-primary waves-effect waves-light" type="submit">
                            {{lang('dt_save')}}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- end row -->
@endsection
@section('script')
    <script>
        $(document).ready(function(){
            $('select.select2').select2({'clear' : true});
            var select_province_id = $('.select_province_id');

            $.each(select_province_id, function(index, value) {
                searchAjaxSelect2(value,'api/category/getListProvince', 0,{
                    'select2':true
                })
            })
        })
        var countKey = {{$sttKey ?? 0}};
        function changeProvince(_this) {
            var key = $(_this).attr('data-key');
            var tr = $(_this).parents('tr');
            var province_id = $(_this).val();

            if($(`#table-setup-ares`).find(`tr[data-class="${province_id}"]`).length > 0) {
                alert_float('error', 'Tỉnh/Thành phố đã có vui lòng chọn tỉnh/thành phố khác');
                $(_this).val(0);
                searchAjaxSelect2($(_this),'api/category/getListProvince', 0,{
                    'select2':true
                })
                return false;
            }

            if($(tr).attr('data-class') != province_id) {
                $(`#ward_id-${key}`).val([]);
            }

            $(tr).attr('data-class', province_id);
            var province_id_old = $(`#province_old_id-${key}`).val(0);
            province_id_old  = province_id_old ?? 0;
            searchAjaxSelect2(`#province_old_id-${key}`,'api/category/getListProvinceSixtyFour',0,{
                'select2':true,
                id_province :province_id,
            })
            searchAjaxSelect2Mutil(`#ward_id-${key}`,'api/category/getListWard',0,{
                'select2':true,
                province_id :province_id,
                province_id_old :province_id_old
            })
        }


        function changeProvinceOld(_this) {
            var key = $(_this).attr('data-key');
            var tr = $(_this).parents('tr');
            var province_id_old = $(_this).val();
            var province_id = $(tr).find('.select_province_id').val();

            if($(tr).attr('data-class-old') != province_id_old) {
                $(`#ward_id-${key}`).val([]);
            }
            $(tr).attr('data-class-old', province_id_old);
            searchAjaxSelect2Mutil(`#ward_id-${key}`,'api/category/getListWard',0,{
                'select2':true,
                province_id :province_id,
                province_id_old :province_id_old
            })
        }

        function btnPlus() {
            $(`#table-setup-ares`).find('tbody').append(`<tr class="item">
                                            <td class="SttITem" style="width: 5%"></td>
                                            <td style="width: 20%">
                                                <div class="form-group">
                                                    <select class="select_province_id province_id-${countKey} select2" id="province_id-${countKey}" data-key="${countKey}"
                                                            data-placeholder="Chọn ..." name="item[${countKey}][province_id]"
                                                            onchange="changeProvince(this)">
                                                        <option></option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td style="width: 20%">
                                                <div class="form-group">
                                                    <select class="select_province_old_id province_old_id-${countKey} select2" id="province_old_id-${countKey}" data-key="${countKey}"
                                                            data-placeholder="Chọn ..." name="item[${countKey}][province_old_id]"
                                                            onchange="changeProvinceOld(this)">
                                                        <option></option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <select class="ward_id-${countKey} select2" id="ward_id-${countKey}" multiple
                                                            data-placeholder="Chọn ..." name="item[${countKey}][ward_id][]">
                                                        <option></option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <a class="btn btn-danger btn-icon" onclick="removeTr(this)""><i class="fa fa-remove"></i></a>
                                            </td>
                                        </tr>`);
            searchAjaxSelect2(`.province_id-${countKey}`,'api/category/getListProvince',0,{
                'select2':true
            })
            $(`select.province_old_id-${countKey}`).select2();
            $(`select.ward_id-${countKey}`).select2();
            countKey++;
            ChangeStt();
        }

        function removeTr(_this) {
            if(confirm("Bạn có chắc muốn xóa Thành phố/Tỉnh?")) {
                $(_this).parents('tr').remove();
                ChangeStt();
            }
        }

        function ChangeStt() {
            var listSTT = $('.SttITem');
            $.each(listSTT, function(index, value) {
                $(value).text(index + 1);
            })
        }

        $("#formSetupAres").validate({
            rules: {
                phone: {
                    required: true,
                },
                fullname: {
                    required: true,
                }
            },
            messages: {
                phone: {
                    required: "{{lang('dt_required')}}",
                },
                fullname: {
                    required: "{{lang('dt_required')}}",
                }
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
                            alert_float('success',data.message);
                            window.location.href = 'admin/ares/list';
                        } else {
                            alert_float('error',data.message);
                        }
                    })
                    .fail(function (err) {
                        htmlError = '';
                        for (var [ el, message ] of Object.entries(err.responseJSON.errors)) {
                            htmlError += `<div>${message}</div>`;
                        }
                        alert_float('error',htmlError);
                    });
                return false;
            }
        });
    </script>
@endsection
