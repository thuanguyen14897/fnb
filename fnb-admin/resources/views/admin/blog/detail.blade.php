@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title">{{$title}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/blog/list">{{lang('dt_blog')}}</a></li>
                <li class="active">{{!empty($blog) ? lang('dt_edit') : lang('dt_create')}}</li>
            </ol>
        </div>
    </div>
    <form action="admin/blog/submit/{{$id}}" method="post" id="blogForm" data-parsley-validate
              novalidate
              enctype="multipart/form-data">
            {{csrf_field()}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card-box">
                    <div role="tabpanel">
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#tab-info_general" aria-controls="tab" role="tab" data-toggle="tab"><?= lang('info_general') ?></a>
                            </li>
                            <li role="presentation">
                                <a href="#tab-vietnamese" aria-controls="tab" role="tab" data-toggle="tab"><?= lang('vietnamese') ?></a>
                            </li>
                            <li role="presentation">
                                <a href="#tab-english" aria-controls="tab" role="tab" data-toggle="tab"><?= lang('english') ?></a>
                            </li>
                            <li role="presentation">
                                <a href="#tab-chinese" aria-controls="tab" role="tab" data-toggle="tab"><?= lang('chinese') ?></a>
                            </li>
                            <li role="presentation">
                                <a href="#tab-korea" aria-controls="tab" role="tab" data-toggle="tab"><?= lang('korea') ?></a>
                            </li>
                            <li role="presentation">
                                <a href="#tab-japan" aria-controls="tab" role="tab" data-toggle="tab"><?= lang('japan') ?></a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="tab-info_general">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="image">{{lang('dt_image')}}<span class="text-danger">*</span></label>
                                            <input type="file" name="image" id="image" class="filestyle image" data-buttonbefore="true">
                                            @if(!empty($blog) && $blog->image != null)
                                                @php
                                                    $dtImage =asset('storage/' . $blog->image);
                                                @endphp
                                                {!! loadImage($dtImage, '200px', 'img-rounded',$blog->image,false,'150px'); !!}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group {{$type == 1 ? '' : 'hide'}}">
                                            <label for="type_blog">Loại bài viết</label>
                                            <select class="form-control type_blog select2" name="type_blog">
                                                @foreach($dtTypeBlog as $key => $value)
                                                    <option {{!empty($blog) && ($blog->type_blog) == $value['id'] ? 'selected' : ''}} value="{{$value['id']}}">{{$value['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group hide">
                                            <label for="type">Loại</label>
                                            <select class="form-control type select2" name="type">
                                                @foreach($dtType as $key => $value)
                                                    <option {{!empty($blog) && ($blog->type) == $value['id'] ? 'selected' : ($type == $value['id'] ? 'selected' : '')}} value="{{$value['id']}}">{{$value['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="tab-vietnamese">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="title">{{lang('dt_title')}}<span class="text-danger">*</span></label>
                                            <input type="text" name="title" id="title" placeholder="{{ lang('dt_title') }}" autocomplete="off" value="{{!empty($blog) ? $blog->title : ''}}" class="form-control title">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="descption">{{lang('dt_descption')}}<span class="text-danger">*</span></label>
                                            <textarea type="text" name="descption" id="descption" autocomplete="off"
                                                      cols="2" rows="3" class="form-control descption editor">{{!empty($blog) ? $blog->descption : ''}}
                                            </textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="note">{{lang('dt_content')}}</label>
                                            <textarea type="text" name="content" autocomplete="off"
                                                    cols="2" rows="5" class="form-control content editor">{{!empty($blog) ? $blog->content : ''}}
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="tab-english">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="title_en">{{lang('dt_title')}}<span class="text-danger">*</span></label>
                                            <input type="text" name="title_en" placeholder="{{ lang('dt_title') }}" id="title_en" autocomplete="off" value="{{!empty($blog) ? $blog->title_en : ''}}" class="form-control title_en">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="descption_en">{{lang('dt_descption')}}<span class="text-danger">*</span></label>
                                            <textarea type="text" name="descption_en" id="descption_en" autocomplete="off"
                                                      cols="2" rows="3" class="form-control descption_en editor">{{!empty($blog) ? $blog->descption_en : ''}}
                                            </textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="content_en">{{lang('dt_content')}}</label>
                                            <textarea type="text" name="content_en" autocomplete="off"
                                                    cols="2" rows="5" class="form-control content_en editor">{{!empty($blog) ? $blog->content_en : ''}}
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="tab-chinese">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="title_zh">{{lang('dt_title')}}<span class="text-danger">*</span></label>
                                            <input type="text" name="title_zh" placeholder="{{ lang('dt_title') }}" id="title_zh" autocomplete="off" value="{{!empty($blog) ? $blog->title_zh : ''}}" class="form-control title_zh">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="descption_zh">{{lang('dt_descption')}}<span class="text-danger">*</span></label>
                                            <textarea type="text" name="descption_zh" id="descption_zh" autocomplete="off"
                                                      cols="2" rows="3" class="form-control descption_zh editor">{{!empty($blog) ? $blog->descption_zh : ''}}
                                            </textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="content_zh">{{lang('dt_content')}}</label>
                                            <textarea type="text" name="content_zh" autocomplete="off"
                                                    cols="2" rows="5" class="form-control content_zh editor">{{!empty($blog) ? $blog->content_zh : ''}}
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="tab-korea">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="title_ko">{{lang('dt_title')}}<span class="text-danger">*</span></label>
                                            <input type="text" name="title_ko" placeholder="{{ lang('dt_title') }}" id="title_ko" autocomplete="off" value="{{!empty($blog) ? $blog->title_ko : ''}}" class="form-control title_ko">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="descption_ko">{{lang('dt_descption')}}<span class="text-danger">*</span></label>
                                            <textarea type="text" name="descption_ko" id="descption_ko" autocomplete="off"
                                                      cols="2" rows="3" class="form-control descption_ko editor">{{!empty($blog) ? $blog->descption_ko : ''}}
                                            </textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="content_ko">{{lang('dt_content')}}</label>
                                            <textarea name="content_ko" autocomplete="off"
                                                      cols="2" rows="5" class="form-control content_ko editor">{{!empty($blog) ? $blog->content_ko : ''}}
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="tab-japan">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="title_ja">{{lang('dt_title')}}<span class="text-danger">*</span></label>
                                            <input type="text" name="title_ja" placeholder="{{ lang('dt_title') }}" id="title_ja" autocomplete="off" value="{{!empty($blog) ? $blog->title_ja : ''}}" class="form-control title_ja">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="descption_ja">{{lang('dt_descption')}}<span class="text-danger">*</span></label>
                                            <textarea type="text" name="descption_ja" id="descption_ja" autocomplete="off"
                                                      cols="2" rows="3" class="form-control descption_ja editor">{{!empty($blog) ? $blog->descption_ja : ''}}
                                            </textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="content_ja">{{lang('dt_content')}}</label>
                                            <textarea type="text" name="content_ja" autocomplete="off"
                                                      cols="2" rows="5" class="form-control content_ja editor">{{!empty($blog) ? $blog->content_ja : ''}}
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-6">
                            </div>
                            <div class="col-md-6">
                            </div>
                        </div>
                    </div>
                    <div class="form-group text-right m-b-0">
                        <button class="btn btn-primary waves-effect waves-light" type="submit">
                            {{lang('dt_save')}}
                        </button>
                        <button type="reset" class="btn btn-default waves-effect waves-light m-l-5">
                            {{lang('dt_cancel')}}
                        </button>
                    </div>
                </div>
            </div>
    </div>
</form>

    <!-- end row -->
@endsection
@section('script')
    <script>

        $("#blogForm").validate({
            ignore: "",
            rules: {
                title: {
                    required: true,
                },
                descption: {
                    required: true,
                },
                title_en: {
                    required: true,
                },
                descption_en: {
                    required: true,
                },
                title_zh: {
                    required: true,
                },
                descption_zh: {
                    required: true,
                },
                title_ko: {
                    required: true,
                },
                descption_ko: {
                    required: true,
                },
                title_ja: {
                    required: true,
                },
                descption_ja: {
                    required: true,
                },
                {{empty($blog) ? 'image: {
                    required: true,
                }' : '' }}
            },
            messages: {
                title: {
                    required: "{{lang('dt_required')}}",
                },
                descption: {
                    required: "{{lang('dt_required')}}",
                },
                image: {
                    required: "{{lang('dt_required')}}",
                },
                title_en: {
                    required: "{{lang('dt_required')}}",
                },
                descption_en: {
                    required: "{{lang('dt_required')}}",
                },
                title_zh: {
                    required: "{{lang('dt_required')}}",
                },
                descption_zh: {
                    required: "{{lang('dt_required')}}",
                },
                title_ko: {
                    required: "{{lang('dt_required')}}",
                },
                descption_ko: {
                    required: "{{lang('dt_required')}}",
                },
                title_ja: {
                    required: "{{lang('dt_required')}}",
                },
                descption_ja: {
                    required: "{{lang('dt_required')}}",
                },
            },
            // errorPlacement: function(error, element) {
            //     console.log(element.closest('.tab-pane'));
            //     console.log(element);
            //     if (element.closest('.tab-pane').is(':hidden')) {
            //         // Delay the error placement until the tab becomes visible
            //         element.on('shown.bs.tab', function() {
            //             $(this).valid();
            //         });
            //     } else {
            //         error.insertAfter(element);
            //     }
            // },
            invalidHandler: function(event, validator) {
                let errors = validator.numberOfInvalids();
                if (errors) {
                    let message = "";
                    validator.errorList.forEach(function(error) {
                        let fieldName = $(error.element).attr("id");
                        var fieldNameLang = '';
                        if (fieldName.endsWith("_en")) {
                            fieldNameLang = 'tiếng anh';
                        } else if (fieldName.endsWith("_zh")) {
                            fieldNameLang = 'tiếng trung';
                        }

                        let label = $($("label[for='" + fieldName + "']")[0]).text();
                        if (!label) {
                            fieldName = $(error.element).attr("name");
                            label = $($("label[for='" + fieldName + "']")[0]).text();
                        }

                        message += `<div>${label} ${fieldNameLang} ${error.message}</div>`;
                    });

                    if (!message) {
                        message = 'Bạn chưa nhập các trường';
                    }
                    alert_float('error', message, 5000);
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
                            window.location.href='admin/blog/list?type='+data.type;
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
        setTimeout(function (){
            $(".content").closest('div').find('.tox-tinymce').css({
                height:"450px"
            })
        },300);
    </script>
@endsection
