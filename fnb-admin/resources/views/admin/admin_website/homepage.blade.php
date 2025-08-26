@extends('admin.layouts.index')
@section('content')
    <style>
        .thumb_new > .bootstrap-filestyle{
            display: none;
        }
        .product-list-box{
            min-height: 590px !important;
        }
    </style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{$title}}</h4>
        </div>
    </div>
    <div class="row">
        <form id="HomepageForm" action="admin/admin_website/submit_homepage" method="post" data-parsley-validate
              novalidate
              enctype="multipart/form-data">
            {{csrf_field()}}
            <div class="col-lg-12">
                <div class="card-box">
                    <div class="row">
                        <div class="">
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active" onclick="tabData(this, 'vn')">
                                    <a href="#tab-vietnamese" aria-controls="tab" role="tab" data-toggle="tab"><?= lang('vietnamese') ?></a>
                                </li>
                                <li role="presentation" onclick="tabData(this, 'en')">
                                    <a href="#tab-english" aria-controls="tab" role="tab" data-toggle="tab"><?= lang('english') ?></a>
                                </li>
                                <li role="presentation" onclick="tabData(this, 'zh')">
                                    <a href="#tab-chinese" aria-controls="tab" role="tab" data-toggle="tab"><?= lang('chinese') ?></a>
                                </li>
                                <li role="presentation" onclick="tabData(this, 'ko')">
                                    <a href="#tab-korea" aria-controls="tab" role="tab" data-toggle="tab"><?= lang('korea') ?></a>
                                </li>
                                <li role="presentation" onclick="tabData(this, 'ja')">
                                    <a href="#tab-japan" aria-controls="tab" role="tab" data-toggle="tab"><?= lang('japan') ?></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <section>
                                <div class="col-md-12 title_section">Section 1</div>
                                <div class="col-md-4 div-vn">
                                    <label for="title_section1">Tiêu đề</label>
                                    <input type="text" name="title_section1" class="title_section1 form-control" value="{{$homePage->section_1->title ?? ''}}">
                                </div>
                                <div class="col-md-8 div-vn">
                                    <label for="content_section1">Nội dung</label>
                                    <input type="text" name="content_section1" class="content_section1 form-control" value="{{$homePage->section_1->content ?? ''}}">
                                </div>

                                <div class="col-md-4 div-en">
                                    <label for="title_section1_en">Tiêu đề</label>
                                    <input type="text" name="title_section1_en" class="title_section1_en form-control" value="{{$homePage->section_1->title_en ?? ''}}">
                                </div>
                                <div class="col-md-8 div-en">
                                    <label for="content_section1_en">Nội dung</label>
                                    <input type="text" name="content_section1_en" class="content_section1_en form-control" value="{{$homePage->section_1->content_en ?? ''}}">
                                </div>

                                <div class="col-md-4 div-zh">
                                    <label for="title_section1_zh">Tiêu đề</label>
                                    <input type="text" name="title_section1_zh" class="title_section1_zh form-control" value="{{$homePage->section_1->title_zh ?? ''}}">
                                </div>
                                <div class="col-md-8 div-zh">
                                    <label for="content_section1_zh">Nội dung</label>
                                    <input type="text" name="content_section1_zh" class="content_section1_zh form-control" value="{{$homePage->section_1->content_zh ?? ''}}">
                                </div>

                                <div class="col-md-4 div-ko">
                                    <label for="title_section1_ko">Tiêu đề</label>
                                    <input type="text" name="title_section1_ko" class="title_section1_ko form-control" value="{{$homePage->section_1->title_ko ?? ''}}">
                                </div>
                                <div class="col-md-8 div-ko">
                                    <label for="content_section1_ko">Nội dung</label>
                                    <input type="text" name="content_section1_ko" class="content_section1_ko form-control" value="{{$homePage->section_1->content_ko ?? ''}}">
                                </div>

                                <div class="col-md-4 div-ja">
                                    <label for="title_section1_ja">Tiêu đề</label>
                                    <input type="text" name="title_section1_ja" class="title_section1_ja form-control" value="{{$homePage->section_1->title_ja ?? ''}}">
                                </div>
                                <div class="col-md-8 div-ja">
                                    <label for="content_section1_ja">Nội dung</label>
                                    <input type="text" name="content_section1_ja" class="content_section1_ja form-control" value="{{$homePage->section_1->content_ja ?? ''}}">
                                </div>

                                <div class="col-md-12">
                                    <label for="country_currency">Ngoại tệ</label>
                                    <select class="country_currency select2"
                                            data-placeholder="Chọn ..." name="country_currency[]" multiple>
                                        <option></option>
                                        @if(!empty($dtCountryCurrent))
                                            @foreach($dtCountryCurrent as $key => $value)
                                                <option
                                                    @if(!empty($dtCountryCurrentHomepage))
                                                        @foreach($dtCountryCurrentHomepage as $k => $v)
                                                            @if($v->country_currency_id == $value->id)
                                                                selected
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                    value="{{$value->id}}">{{$value->name}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </section>
                            <section>
                                <div class="col-md-12 title_section" style="margin-top: 10px">Section 2</div>
                                <div class="col-md-4 div-vn">
                                    <label for="title_section2">Tiêu đề</label>
                                    <input type="text" name="title_section2" class="title_section2 form-control" value="{{$homePage->section_2->title ?? ''}}">
                                </div>
                                <div class="col-md-8 div-vn">
                                    <label for="content_section2">Nội dung</label>
                                    <input type="text" name="content_section2" class="content_section2 form-control" value="{{$homePage->section_2->content ?? ''}}">
                                </div>

                                <div class="col-md-4 div-en">
                                    <label for="title_section2_en">Tiêu đề</label>
                                    <input type="text" name="title_section2_en" class="title_section2_en form-control" value="{{$homePage->section_2->title_en ?? ''}}">
                                </div>
                                <div class="col-md-8 div-en">
                                    <label for="content_section2_en">Nội dung</label>
                                    <input type="text" name="content_section2_en" class="content_section2_en form-control" value="{{$homePage->section_2->content_en ?? ''}}">
                                </div>

                                <div class="col-md-4 div-zh">
                                    <label for="title_section2_zh">Tiêu đề</label>
                                    <input type="text" name="title_section2_zh" class="title_section2_zh form-control" value="{{$homePage->section_2->title_zh ?? ''}}">
                                </div>
                                <div class="col-md-8 div-zh">
                                    <label for="content_section2_zh">Nội dung</label>
                                    <input type="text" name="content_section2_zh" class="content_section2_zh form-control" value="{{$homePage->section_2->content_zh ?? ''}}">
                                </div>

                                <div class="col-md-4 div-ko">
                                    <label for="title_section2_ko">Tiêu đề</label>
                                    <input type="text" name="title_section2_ko" class="title_section2_ko form-control" value="{{$homePage->section_2->title_ko ?? ''}}">
                                </div>
                                <div class="col-md-8 div-ko">
                                    <label for="content_section2_ko">Nội dung</label>
                                    <input type="text" name="content_section2_ko" class="content_section2_ko form-control" value="{{$homePage->section_2->content_ko ?? ''}}">
                                </div>

                                <div class="col-md-4 div-ja">
                                    <label for="title_section2_ja">Tiêu đề</label>
                                    <input type="text" name="title_section2_ja" class="title_section2_ja form-control" value="{{$homePage->section_2->title_ja ?? ''}}">
                                </div>
                                <div class="col-md-8 div-ja">
                                    <label for="content_section2_ja">Nội dung</label>
                                    <input type="text" name="content_section2_ja" class="content_section2_ja form-control" value="{{$homePage->section_2->content_ja ?? ''}}">
                                </div>

                                @for($i = 1;$i <= $limit;$i++)
                                    @php
                                    $key_image = 'image_'.$i;
                                    $imagePath = !empty($homePage->section_2->$key_image->image) ? asset('storage/'.$homePage->section_2->$key_image->image) : 'admin/assets/images/1.jpg';
                                    $type_section_2 = !empty($homePage->section_2->$key_image->type) ? ($homePage->section_2->$key_image->type == 0 ? 'checked' : '') : 'checked';
                                    $type_section_2_new = !empty($homePage->section_2->$key_image->type) ? ($homePage->section_2->$key_image->type == 1 ? 'checked' : '') : '';
                                    $type_wrap_section_2 = !empty($homePage->section_2->$key_image->type) ? ($homePage->section_2->$key_image->type == 0 ? '' : 'hide') : '';
                                    $type_wrap_section_2_new = !empty($homePage->section_2->$key_image->type) ? ($homePage->section_2->$key_image->type == 1 ? '' : 'hide') : 'hide';
                                    $type_required_section_2 = !empty($homePage->section_2->$key_image->type) ? ($homePage->section_2->$key_image->type == 0 ? 'required' : '') : 'required';
                                    $type_required_section_2_new = !empty($homePage->section_2->$key_image->type) ? ($homePage->section_2->$key_image->type == 1 ? 'required' : '') : '';
                                    $blog_id_section_2 = !empty($homePage->section_2->$key_image->blog_id) ? $homePage->section_2->$key_image->blog_id : 0;
                                    $dtBlog = \App\Models\Blog::find($blog_id_section_2);
                                    $name_blog_section_2 = !empty($dtBlog) ? $dtBlog->title : '';
                                    $href_id_section_2 = !empty($homePage->section_2->$key_image->href_id) ? $homePage->section_2->$key_image->href_id : '';
                                    @endphp
                                    <div class="col-sm-6 col-lg-3 col-md-4 mobiles">
                                        <div class="product-list-box thumb thumb_new">
                                            <a href="javascript:void(0);" class="image-popup" onclick="clickImage(this)" data-id="{{$i}}" title="Screenshot-1">
                                                <img src="{{$imagePath}}" style="max-height: 250px;min-height: 200px" class="thumb-img section_2_image_preview_{{$i}}" alt="work-thumbnail">
                                            </a>
                                            <input type="file" name="section_2_image_{{$i}}" class="section_2_image" data-id="{{$i}}" id="section_2_image_{{$i}}" />
                                            <div class="detail">
                                                <div class="m-t-0 div-vn">
                                                    <label for="title_section_2_image_{{$i}}">Tiêu đề</label>
                                                    <input type="text" name="title_section_2_image_{{$i}}" class="title_section_2_image_{{$i}} form-control" value="{{$homePage->section_2->$key_image->title ?? ''}}">
                                                    <label for="content_section_2_image_{{$i}}">Nội dung</label>
                                                    <textarea class="form-control content_section_2_image_{{$i}}" name="content_section_2_image_{{$i}}">{{$homePage->section_2->$key_image->content ?? ''}}</textarea>
                                                </div>

                                                <div class="m-t-0 div-en">
                                                    <label for="title_section_2_image_en_{{$i}}">Tiêu đề</label>
                                                    <input type="text" name="title_section_2_image_en_{{$i}}" class="title_section_2_image_en_{{$i}} form-control" value="{{$homePage->section_2->$key_image->title_en ?? ''}}">
                                                    <label for="content_section_2_image_en_{{$i}}">Nội dung</label>
                                                    <textarea class="form-control content_section_2_image_en_{{$i}}" name="content_section_2_image_en_{{$i}}">{{$homePage->section_2->$key_image->content_en ?? ''}}</textarea>
                                                </div>

                                                <div class="m-t-0 div-zh">
                                                    <label for="title_section_2_image_zh_{{$i}}">Tiêu đề</label>
                                                    <input type="text" name="title_section_2_image_zh_{{$i}}" class="title_section_2_image_zh_{{$i}} form-control" value="{{$homePage->section_2->$key_image->title_zh ?? ''}}">
                                                    <label for="content_section_2_image_zh_{{$i}}">Nội dung</label>
                                                    <textarea class="form-control content_section_2_image_zh_{{$i}}" name="content_section_2_image_zh_{{$i}}">{{$homePage->section_2->$key_image->content_zh ?? ''}}</textarea>
                                                </div>

                                                <div class="m-t-0 div-ko">
                                                    <label for="title_section_2_image_ko_{{$i}}">Tiêu đề</label>
                                                    <input type="text" name="title_section_2_image_ko_{{$i}}" class="title_section_2_image_ko_{{$i}} form-control" value="{{$homePage->section_2->$key_image->title_ko ?? ''}}">
                                                    <label for="content_section_2_image_ko_{{$i}}">Nội dung</label>
                                                    <textarea class="form-control content_section_2_image_ko_{{$i}}" name="content_section_2_image_ko_{{$i}}">{{$homePage->section_2->$key_image->content_ko ?? ''}}</textarea>
                                                </div>

                                                <div class="m-t-0 div-ja">
                                                    <label for="title_section_2_image_ja_{{$i}}">Tiêu đề</label>
                                                    <input type="text" name="title_section_2_image_ja_{{$i}}" class="title_section_2_image_ja_{{$i}} form-control" value="{{$homePage->section_2->$key_image->title_ja ?? ''}}">
                                                    <label for="content_section_2_image_ja_{{$i}}">Nội dung</label>
                                                    <textarea class="form-control content_section_2_image_ja_{{$i}}" name="content_section_2_image_ja_{{$i}}">{{$homePage->section_2->$key_image->content_ja ?? ''}}</textarea>
                                                </div>

                                                <div class="form-group" style="margin-top: 10px">
                                                    <div class="radio radio-info radio-inline">
                                                        <input type="radio" id="type_link1_{{$i}}" value="0" {{$type_section_2}} onchange="ChangeType(this,1)" name="type_link_{{$i}}">
                                                        <label for="type_link1_{{$i}}">Link trong website</label>
                                                    </div>
                                                    <div class="radio radio-info radio-inline">
                                                        <input type="radio" id="type_link2_{{$i}}" value="1" {{$type_section_2_new}} onchange="ChangeType(this,2)" name="type_link_{{$i}}">
                                                        <label for="type_link2_{{$i}}">Link ngoài website</label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12 wrap-blog {{$type_wrap_section_2}}">
                                                        <label for="blog_id">Chọn bài viết</label>
                                                        <select class="blog_id select2"
                                                                data-placeholder="Chọn ..." {{$type_required_section_2}} name="blog_id_{{$i}}">
                                                            <option></option>
                                                            @if (!empty($homePage->section_2->$key_image->blog_id))
                                                                <option selected value="{{$homePage->section_2->$key_image->blog_id}}">
                                                                    {{$name_blog_section_2}}</option>
                                                            @endif
                                                        </select>
                                                    </div>
                                                    <div class="col-md-12 wrap-link {{$type_wrap_section_2_new}}">
                                                        <label for="href_id">Nhập link bài viết</label>
                                                        <input type="text" name="href_id_{{$i}}" {{$type_required_section_2_new}} class="href_id form-control" value="{{$href_id_section_2}}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </section>
                            <section>
                                <div class="col-md-12 title_section" style="margin-top: 10px">Section 3</div>
                                <div class="col-md-4 div-vn">
                                    <label for="title_section3">Tiêu đề</label>
                                    <input type="text" name="title_section3" class="title_section3 form-control" value="{{$homePage->section_3->title ?? ''}}">
                                </div>
                                <div class="col-md-8 div-vn">
                                    <label for="content_section3">Nội dung</label>
                                    <input type="text" name="content_section3" class="content_section3 form-control" value="{{$homePage->section_3->content ?? ''}}">
                                </div>

                                <div class="col-md-4 div-en">
                                    <label for="title_section3_en">Tiêu đề</label>
                                    <input type="text" name="title_section3_en" class="title_section3_en form-control" value="{{$homePage->section_3->title_en ?? ''}}">
                                </div>
                                <div class="col-md-8 div-en">
                                    <label for="content_section3_en">Nội dung</label>
                                    <input type="text" name="content_section3_en" class="content_section3_en form-control" value="{{$homePage->section_3->content_en ?? ''}}">
                                </div>

                                <div class="col-md-4 div-zh">
                                    <label for="title_section3_zh">Tiêu đề</label>
                                    <input type="text" name="title_section3_zh" class="title_section3_zh form-control" value="{{$homePage->section_3->title_zh ?? ''}}">
                                </div>
                                <div class="col-md-8 div-zh">
                                    <label for="content_section3_zh">Nội dung</label>
                                    <input type="text" name="content_section3_zh" class="content_section3_zh form-control" value="{{$homePage->section_3->content_zh ?? ''}}">
                                </div>

                                <div class="col-md-4 div-ko">
                                    <label for="title_section3_ko">Tiêu đề</label>
                                    <input type="text" name="title_section3_ko" class="title_section3_ko form-control" value="{{$homePage->section_3->title_ko ?? ''}}">
                                </div>
                                <div class="col-md-8 div-ko">
                                    <label for="content_section3_ko">Nội dung</label>
                                    <input type="text" name="content_section3_ko" class="content_section3_ko form-control" value="{{$homePage->section_3->content_ko ?? ''}}">
                                </div>

                                <div class="col-md-4 div-ja">
                                    <label for="title_section3_ja">Tiêu đề</label>
                                    <input type="text" name="title_section3_ja" class="title_section3_ja form-control" value="{{$homePage->section_3->title_ja ?? ''}}">
                                </div>
                                <div class="col-md-8 div-ja">
                                    <label for="content_section3_ja">Nội dung</label>
                                    <input type="text" name="content_section3_ja" class="content_section3_ja form-control" value="{{$homePage->section_3->content_ja ?? ''}}">
                                </div>
                                @for($i = 1;$i <= $limit_section;$i++)
                                    @php
                                        $key_image = 'image_'.$i;
                                        $imagePath = !empty($homePage->section_3->$key_image->image) ? asset('storage/'.$homePage->section_3->$key_image->image) : 'admin/assets/images/1.jpg';
                                        $type_section_3 = !empty($homePage->section_3->$key_image->type) ? ($homePage->section_3->$key_image->type == 0 ? 'checked' : '') : 'checked';
                                        $type_section_3_new = !empty($homePage->section_3->$key_image->type) ? ($homePage->section_3->$key_image->type == 1 ? 'checked' : '') : '';
                                        $type_wrap_section_3 = !empty($homePage->section_3->$key_image->type) ? ($homePage->section_3->$key_image->type == 0 ? '' : 'hide') : '';
                                        $type_wrap_section_3_new = !empty($homePage->section_3->$key_image->type) ? ($homePage->section_3->$key_image->type == 1 ? '' : 'hide') : 'hide';
                                        $type_required_section_3 = !empty($homePage->section_3->$key_image->type) ? ($homePage->section_3->$key_image->type == 0 ? 'required' : '') : 'required';
                                        $type_required_section_3_new = !empty($homePage->section_3->$key_image->type) ? ($homePage->section_3->$key_image->type == 1 ? 'required' : '') : '';
                                        $blog_id_section_3 = !empty($homePage->section_3->$key_image->blog_id) ? $homePage->section_3->$key_image->blog_id : 0;
                                        $dtBlog = \App\Models\Blog::find($blog_id_section_3);
                                        $name_blog_section_3 = !empty($dtBlog) ? $dtBlog->title : '';
                                        $href_id_section_3 = !empty($homePage->section_3->$key_image->href_id) ? $homePage->section_3->$key_image->href_id : '';
                                    @endphp
                                <div class="col-sm-6 col-lg-3 col-md-4 mobiles">
                                    <div class="product-list-box thumb thumb_new">
                                        <a href="javascript:void(0);" class="image-popup" onclick="clickImage3(this)" data-id="{{$i}}"  title="Screenshot-1">
                                            <img src="{{$imagePath}}" style="max-height: 250px;min-height: 200px" class="thumb-img section_3_image_preview_{{$i}}" alt="work-thumbnail">
                                        </a>
                                        <input type="file" name="section_3_image_{{$i}}" class="section_3_image" data-id="{{$i}}" id="section_3_image_{{$i}}" />
                                        <div class="detail">
                                            <div class="m-t-0 div-vn">
                                                <label for="title_section_3_image_{{$i}}">Tiêu đề</label>
                                                <input type="text" name="title_section_3_image_{{$i}}" class="title_section_3_image_{{$i}} form-control" value="{{$homePage->section_3->$key_image->title ?? ''}}">
                                                <label for="content_section_3_image_{{$i}}">Nội dung</label>
                                                <textarea class="form-control content_section_3_image_{{$i}}" name="content_section_3_image_{{$i}}">{{$homePage->section_3->$key_image->content ?? ''}}</textarea>
                                            </div>

                                            <div class="m-t-0 div-en">
                                                <label for="title_section_3_image_en_{{$i}}">Tiêu đề</label>
                                                <input type="text" name="title_section_3_image_en_{{$i}}" class="title_section_3_image_en_{{$i}} form-control" value="{{$homePage->section_3->$key_image->title_en ?? ''}}">
                                                <label for="content_section_3_image_en_{{$i}}">Nội dung</label>
                                                <textarea class="form-control content_section_3_image_en_{{$i}}" name="content_section_3_image_en_{{$i}}">{{$homePage->section_3->$key_image->content_en ?? ''}}</textarea>
                                            </div>

                                            <div class="m-t-0 div-zh">
                                                <label for="title_section_3_image_zh_{{$i}}">Tiêu đề</label>
                                                <input type="text" name="title_section_3_image_zh_{{$i}}" class="title_section_3_image_zh_{{$i}} form-control" value="{{$homePage->section_3->$key_image->title_zh ?? ''}}">
                                                <label for="content_section_3_image_zh_{{$i}}">Nội dung</label>
                                                <textarea class="form-control content_section_3_image_zh_{{$i}}" name="content_section_3_image_zh_{{$i}}">{{$homePage->section_3->$key_image->content_zh ?? ''}}</textarea>
                                            </div>

                                            <div class="m-t-0 div-ko">
                                                <label for="title_section_3_image_ko_{{$i}}">Tiêu đề</label>
                                                <input type="text" name="title_section_3_image_ko_{{$i}}" class="title_section_3_image_ko_{{$i}} form-control" value="{{$homePage->section_3->$key_image->title_ko ?? ''}}">
                                                <label for="content_section_3_image_ko_{{$i}}">Nội dung</label>
                                                <textarea class="form-control content_section_3_image_ko_{{$i}}" name="content_section_3_image_ko_{{$i}}">{{$homePage->section_3->$key_image->content_ko ?? ''}}</textarea>
                                            </div>

                                            <div class="m-t-0 div-ja">
                                                <label for="title_section_3_image_ja_{{$i}}">Tiêu đề</label>
                                                <input type="text" name="title_section_3_image_ja_{{$i}}" class="title_section_3_image_ja_{{$i}} form-control" value="{{$homePage->section_3->$key_image->title_ja ?? ''}}">
                                                <label for="content_section_3_image_ja_{{$i}}">Nội dung</label>
                                                <textarea class="form-control content_section_3_image_ja_{{$i}}" name="content_section_3_image_ja_{{$i}}">{{$homePage->section_3->$key_image->content_ja ?? ''}}</textarea>
                                            </div>

                                            <div class="form-group" style="margin-top: 10px">
                                                <div class="radio radio-info radio-inline">
                                                    <input type="radio" id="type_section_3_link1_{{$i}}" value="0" {{$type_section_3}} onchange="ChangeTypeSection3(this,1)" name="type_link_section_3_{{$i}}">
                                                    <label for="type_section_3_link1_{{$i}}">Link trong website</label>
                                                </div>
                                                <div class="radio radio-info radio-inline">
                                                    <input type="radio" id="type_section_3_link2_{{$i}}" value="1" {{$type_section_3_new}} onchange="ChangeTypeSection3(this,2)" name="type_link_section_3_{{$i}}">
                                                    <label for="type_section_3_link2_{{$i}}">Link ngoài website</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 wrap-blog-section-3 {{$type_wrap_section_3}}">
                                                    <label for="blog_id_section_3">Chọn bài viết</label>
                                                    <select class="blog_id_section_3 select2"
                                                            data-placeholder="Chọn ..." {{$type_required_section_3}} name="blog_id_section_3_{{$i}}">
                                                        <option></option>
                                                        @if (!empty($homePage->section_3->$key_image->blog_id))
                                                            <option selected value="{{$homePage->section_3->$key_image->blog_id}}">
                                                                {{$name_blog_section_3}}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="col-md-12 wrap-link-section-3 {{$type_wrap_section_3_new}}">
                                                    <label for="href_id_section_3">Nhập link bài viết</label>
                                                    <input type="text" name="href_id_section_3_{{$i}}" {{$type_required_section_3_new}} class="href_id_section_3 form-control" value="{{$href_id_section_3}}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endfor
                            </section>
                            <section >
                                <div class="col-md-12 title_section" style="margin-top: 10px">Section 4</div>
                                <div class="col-md-4 div-vn">
                                    <label for="title_section4">Tiêu đề</label>
                                    <input type="text" name="title_section4" class="title_section4 form-control" value="{{$homePage->section_4->title ?? ''}}">
                                </div>
                                <div class="col-md-8 div-vn">
                                    <label for="content_section4">Nội dung</label>
                                    <input type="text" name="content_section4" class="content_section4 form-control" value="{{$homePage->section_4->content ?? ''}}">
                                </div>

                                <div class="col-md-4 div-en">
                                    <label for="title_section4_en">Tiêu đề</label>
                                    <input type="text" name="title_section4_en" class="title_section4_en form-control" value="{{$homePage->section_4->title_en ?? ''}}">
                                </div>
                                <div class="col-md-8 div-en">
                                    <label for="content_section4_en">Nội dung</label>
                                    <input type="text" name="content_section4_en" class="content_section4_en form-control" value="{{$homePage->section_4->content_en ?? ''}}">
                                </div>

                                <div class="col-md-4 div-zh">
                                    <label for="title_section4_zh">Tiêu đề</label>
                                    <input type="text" name="title_section4_zh" class="title_section4_zh form-control" value="{{$homePage->section_4->title_zh ?? ''}}">
                                </div>
                                <div class="col-md-8 div-zh">
                                    <label for="content_section4_zh">Nội dung</label>
                                    <input type="text" name="content_section4_zh" class="content_section4_zh form-control" value="{{$homePage->section_4->content_zh ?? ''}}">
                                </div>

                                <div class="col-md-4 div-ko">
                                    <label for="title_section4_ko">Tiêu đề</label>
                                    <input type="text" name="title_section4_ko" class="title_section4_ko form-control" value="{{$homePage->section_4->title_ko ?? ''}}">
                                </div>
                                <div class="col-md-8 div-ko">
                                    <label for="content_section4_ko">Nội dung</label>
                                    <input type="text" name="content_section4_ko" class="content_section4_ko form-control" value="{{$homePage->section_4->content_ko ?? ''}}">
                                </div>

                                <div class="col-md-4 div-ja">
                                    <label for="title_section4_ja">Tiêu đề</label>
                                    <input type="text" name="title_section4_ja" class="title_section4_ja form-control" value="{{$homePage->section_4->title_ja ?? ''}}">
                                </div>
                                <div class="col-md-8 div-ja">
                                    <label for="content_section4_ja">Nội dung</label>
                                    <input type="text" name="content_section4_ja" class="content_section4_ja form-control" value="{{$homePage->section_4->content_ja ?? ''}}">
                                </div>
                            </section>

                        </div>
                    </div>
                    <div class="form-group text-right m-b-0 m-t-10">
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
        $(document).ready(function () {
            searchAjaxSelect2('.blog_id', 'admin/category/searchBlog', 0, {type: 2})
            searchAjaxSelect2('.blog_id_section_3', 'admin/category/searchBlog', 0, {type: 2})
        })
        function clickImage(_this){
            id = $(_this).attr('data-id');
            $(`#section_2_image_${id}`).click();
        }
        $(".section_2_image").change(function (event){
            id = $(this).attr('data-id');
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();

                // Khi đọc tệp xong, hiển thị hình ảnh
                reader.onload = function(e) {
                    $(`.section_2_image_preview_${id}`).attr('src', e.target.result).show();
                };

                reader.readAsDataURL(file);
            }
        });

        function clickImage3(_this){
            id = $(_this).attr('data-id');
            $(`#section_3_image_${id}`).click();
        }
        $(".section_3_image").change(function (event){
            id = $(this).attr('data-id');
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();

                // Khi đọc tệp xong, hiển thị hình ảnh
                reader.onload = function(e) {
                    $(`.section_3_image_preview_${id}`).attr('src', e.target.result).show();
                };

                reader.readAsDataURL(file);
            }
        });

        function ChangeType(_this,type){
            if(type == 1){
                $(_this).closest('div.detail').find(".wrap-blog").removeClass('hide');
                $(_this).closest('div.detail').find(".wrap-link").addClass('hide');
                $(_this).closest('div.detail').find("select.blog_id").attr('required',true);
                $(_this).closest('div.detail').find("input.href_id").attr('required',false);
            } else if(type == 2){
                $(_this).closest('div.detail').find(".wrap-blog").addClass('hide');
                $(_this).closest('div.detail').find(".wrap-link").removeClass('hide');
                $(_this).closest('div.detail').find("select.blog_id").attr('required',false);
                $(_this).closest('div.detail').find("input.href_id").attr('required',true);
            }
        }

        function ChangeTypeSection3(_this,type){
            if(type == 1){
                $(_this).closest('div.detail').find(".wrap-blog-section-3").removeClass('hide');
                $(_this).closest('div.detail').find(".wrap-link-section-3").addClass('hide');
                $(_this).closest('div.detail').find("select.blog_id_section_3").attr('required',true);
                $(_this).closest('div.detail').find("input.href_id_section_3").attr('required',false);
            } else if(type == 2){
                $(_this).closest('div.detail').find(".wrap-blog-section-3").addClass('hide');
                $(_this).closest('div.detail').find(".wrap-link-section-3").removeClass('hide');
                $(_this).closest('div.detail').find("select.blog_id_section_3").attr('required',false);
                $(_this).closest('div.detail').find("input.href_id_section_3").attr('required',true);
            }
        }

        function tabData(_this, _lang) {
            if (_lang == 'vn') {
                $('.div-vn').removeClass('hide');
                $('.div-en').addClass('hide');
                $('.div-zh').addClass('hide');
                $('.div-ko').addClass('hide');
                $('.div-ja').addClass('hide');
            } else if (_lang == 'en') {
                $('.div-vn').addClass('hide');
                $('.div-en').removeClass('hide');
                $('.div-zh').addClass('hide');
                $('.div-ko').addClass('hide');
                $('.div-ja').addClass('hide');
            } else if (_lang == 'zh') {
                $('.div-vn').addClass('hide');
                $('.div-en').addClass('hide');
                $('.div-zh').removeClass('hide');
                $('.div-ko').addClass('hide');
                $('.div-ja').addClass('hide');
            } else if (_lang == 'ko') {
                $('.div-vn').addClass('hide');
                $('.div-en').addClass('hide');
                $('.div-zh').addClass('hide');
                $('.div-ko').removeClass('hide');
                $('.div-ja').addClass('hide');
            } else if (_lang == 'ja') {
                $('.div-vn').addClass('hide');
                $('.div-en').addClass('hide');
                $('.div-zh').addClass('hide');
                $('.div-ko').addClass('hide');
                $('.div-ja').removeClass('hide');
            }
        }

        $(document).ready(function () {
            tabData(this, 'vn');
        });
    </script>
@endsection
