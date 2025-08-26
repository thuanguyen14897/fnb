@extends('admin.layouts.index')
@section('content')
    <style>
        .thumb_new > .bootstrap-filestyle {
            display: none;
        }

        .product-list-box {
            min-height: 250px !important;
        }
    </style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize m-b-10">{{$title}}</h4>
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
                        <div class="col-md-12">
                            <section>
                                @php
                                    $i = 1;
                                    $key_image = 'image_'.$i;
                                    $imagePath = !empty($homePage->section_2->$key_image->image) ? asset('storage/'.$homePage->section_2->$key_image->image) : 'admin/assets/images/1.jpg';
                                @endphp
                                <div class="col-sm-6 col-md-offset-3">
                                    <div class="product-list-box thumb thumb_new">
                                        <a href="javascript:void(0);" class="image-popup" onclick="clickImage(this)"
                                           data-id="{{$i}}" title="Screenshot-1">
                                            <img src="{{$imagePath}}" style="height: 120px"
                                                 class="thumb-img section_2_image_preview_{{$i}}" alt="work-thumbnail">
                                        </a>
                                        <input type="file" name="section_2_image_{{$i}}" class="section_2_image"
                                               data-id="{{$i}}" id="section_2_image_{{$i}}"/>
                                        <div class="detail">
                                            <div class="m-t-0">
                                                <label for="title_section_2_image_{{$i}}">Tiêu đề</label>
                                                <input type="text" name="title_section_2_image_{{$i}}"
                                                       class="title_section_2_image_{{$i}} form-control"
                                                       value="{{$homePage->section_2->$key_image->title ?? ''}}">
                                                <label for="detail_section_2_image_{{$i}}">Mô tả</label>
                                                <textarea class="form-control detail_section_2_image_{{$i}}"
                                                          name="detail_section_2_image_{{$i}}">{{$homePage->section_2->$key_image->detail ?? ''}}</textarea>
                                                <label for="content_section_2_image_{{$i}}">Nội dung</label>
                                                <textarea class="form-control content_section_2_image_{{$i}} editor"
                                                          name="content_section_2_image_{{$i}}">{{$homePage->section_2->$key_image->content ?? ''}}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-md-offset-3">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            @php
                                                                $i = 2;
                                                                $key_image = 'image_'.$i;
                                                                $imagePath = !empty($homePage->section_2->$key_image->image) ? asset('storage/'.$homePage->section_2->$key_image->image) : 'admin/assets/images/1.jpg';
                                                            @endphp
                                                            <div class="product-list-box thumb thumb_new">
                                                                <a href="javascript:void(0);" class="image-popup"
                                                                   onclick="clickImage(this)" data-id="{{$i}}"
                                                                   title="Screenshot-1">
                                                                    <img src="{{$imagePath}}" style="height: 120px"
                                                                         class="thumb-img section_2_image_preview_{{$i}}"
                                                                         alt="work-thumbnail">
                                                                </a>
                                                                <input type="file" name="section_2_image_{{$i}}"
                                                                       class="section_2_image" data-id="{{$i}}"
                                                                       id="section_2_image_{{$i}}"/>
                                                                <div class="detail">
                                                                    <div class="m-t-0">
                                                                        <label for="title_section_2_image_{{$i}}">Tiêu
                                                                            đề</label>
                                                                        <input type="text"
                                                                               name="title_section_2_image_{{$i}}"
                                                                               class="title_section_2_image_{{$i}} form-control"
                                                                               value="{{$homePage->section_2->$key_image->title ?? ''}}">
                                                                        <label for="detail_section_2_image_{{$i}}">Mô tả</label>
                                                                        <textarea
                                                                            class="form-control detail_section_2_image_{{$i}}"
                                                                            name="detail_section_2_image_{{$i}}">{{$homePage->section_2->$key_image->detail ?? ''}}</textarea>
                                                                        <label for="content_section_2_image_{{$i}}">Nội dung</label>
                                                                        <textarea class="form-control content_section_2_image_{{$i}} editor"
                                                                                  name="content_section_2_image_{{$i}}">{{$homePage->section_2->$key_image->content ?? ''}}</textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            @php
                                                                $i = 3;
                                                                $key_image = 'image_'.$i;
                                                                $imagePath = !empty($homePage->section_2->$key_image->image) ? asset('storage/'.$homePage->section_2->$key_image->image) : 'admin/assets/images/1.jpg';
                                                            @endphp
                                                            <div class="product-list-box thumb thumb_new">
                                                                <a href="javascript:void(0);" class="image-popup"
                                                                   onclick="clickImage(this)" data-id="{{$i}}"
                                                                   title="Screenshot-1">
                                                                    <img src="{{$imagePath}}" style="height: 120px"
                                                                         class="thumb-img section_2_image_preview_{{$i}}"
                                                                         alt="work-thumbnail">
                                                                </a>
                                                                <input type="file" name="section_2_image_{{$i}}"
                                                                       class="section_2_image" data-id="{{$i}}"
                                                                       id="section_2_image_{{$i}}"/>
                                                                <div class="detail">
                                                                    <div class="m-t-0">
                                                                        <label for="title_section_2_image_{{$i}}">Tiêu
                                                                            đề</label>
                                                                        <input type="text"
                                                                               name="title_section_2_image_{{$i}}"
                                                                               class="title_section_2_image_{{$i}} form-control"
                                                                               value="{{$homePage->section_2->$key_image->title ?? ''}}">
                                                                        <label for="detail_section_2_image_{{$i}}">Mô tả</label>
                                                                        <textarea
                                                                            class="form-control detail_section_2_image_{{$i}}"
                                                                            name="detail_section_2_image_{{$i}}">{{$homePage->section_2->$key_image->detail ?? ''}}</textarea>
                                                                        <label for="content_section_2_image_{{$i}}">Nội dung</label>
                                                                        <textarea class="form-control content_section_2_image_{{$i}} editor"
                                                                                  name="content_section_2_image_{{$i}}">{{$homePage->section_2->$key_image->content ?? ''}}</textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="col-md-12">
                                                        @php
                                                            $i = 4;
                                                            $key_image = 'image_'.$i;
                                                            $imagePath = !empty($homePage->section_2->$key_image->image) ? asset('storage/'.$homePage->section_2->$key_image->image) : 'admin/assets/images/1.jpg';
                                                        @endphp
                                                        <div class="product-list-box thumb thumb_new"
                                                             style="min-height: 900px !important;">
                                                            <a href="javascript:void(0);" class="image-popup"
                                                               onclick="clickImage(this)" data-id="{{$i}}"
                                                               title="Screenshot-1">
                                                                <img src="{{$imagePath}}" style="height: 500px"
                                                                     class="thumb-img section_2_image_preview_{{$i}}"
                                                                     alt="work-thumbnail">
                                                            </a>
                                                            <input type="file" name="section_2_image_{{$i}}"
                                                                   class="section_2_image" data-id="{{$i}}"
                                                                   id="section_2_image_{{$i}}"/>
                                                            <div class="detail">
                                                                <div class="m-t-0">
                                                                    <label for="title_section_2_image_{{$i}}">Tiêu
                                                                        đề</label>
                                                                    <input type="text"
                                                                           name="title_section_2_image_{{$i}}"
                                                                           class="title_section_2_image_{{$i}} form-control"
                                                                           value="{{$homePage->section_2->$key_image->title ?? ''}}">
                                                                    <label for="detail_section_2_image_{{$i}}">Mô tả</label>
                                                                    <textarea
                                                                        class="form-control detail_section_2_image_{{$i}}"
                                                                        name="detail_section_2_image_{{$i}}">{{$homePage->section_2->$key_image->detail ?? ''}}</textarea>
                                                                    <label for="content_section_2_image_{{$i}}">Nội dung</label>
                                                                    <textarea class="form-control content_section_2_image_{{$i}} editor"
                                                                              name="content_section_2_image_{{$i}}">{{$homePage->section_2->$key_image->content ?? ''}}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-md-offset-3">
                                        <div class="col-md-6">
                                            @php
                                                $i = 5;
                                                $key_image = 'image_'.$i;
                                                $imagePath = !empty($homePage->section_2->$key_image->image) ? asset('storage/'.$homePage->section_2->$key_image->image) : 'admin/assets/images/1.jpg';
                                            @endphp
                                            <div class="product-list-box thumb thumb_new">
                                                <a href="javascript:void(0);" class="image-popup"
                                                   onclick="clickImage(this)" data-id="{{$i}}" title="Screenshot-1">
                                                    <img src="{{$imagePath}}" style="height: 120px"
                                                         class="thumb-img section_2_image_preview_{{$i}}"
                                                         alt="work-thumbnail">
                                                </a>
                                                <input type="file" name="section_2_image_{{$i}}" class="section_2_image"
                                                       data-id="{{$i}}" id="section_2_image_{{$i}}"/>
                                                <div class="detail">
                                                    <div class="m-t-0">
                                                        <label for="title_section_2_image_{{$i}}">Tiêu đề</label>
                                                        <input type="text" name="title_section_2_image_{{$i}}"
                                                               class="title_section_2_image_{{$i}} form-control"
                                                               value="{{$homePage->section_2->$key_image->title ?? ''}}">
                                                        <label for="detail_section_2_image_{{$i}}">Mô tả</label>
                                                        <textarea
                                                            class="form-control detail_section_2_image_{{$i}}"
                                                            name="detail_section_2_image_{{$i}}">{{$homePage->section_2->$key_image->detail ?? ''}}</textarea>
                                                        <label for="content_section_2_image_{{$i}}">Nội dung</label>
                                                        <textarea class="form-control content_section_2_image_{{$i}} editor"
                                                                  name="content_section_2_image_{{$i}}">{{$homePage->section_2->$key_image->content ?? ''}}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            @php
                                                $i = 6;
                                                $key_image = 'image_'.$i;
                                                $imagePath = !empty($homePage->section_2->$key_image->image) ? asset('storage/'.$homePage->section_2->$key_image->image) : 'admin/assets/images/1.jpg';
                                            @endphp
                                            <div class="product-list-box thumb thumb_new">
                                                <a href="javascript:void(0);" class="image-popup"
                                                   onclick="clickImage(this)" data-id="{{$i}}" title="Screenshot-1">
                                                    <img src="{{$imagePath}}" style="height: 120px"
                                                         class="thumb-img section_2_image_preview_{{$i}}"
                                                         alt="work-thumbnail">
                                                </a>
                                                <input type="file" name="section_2_image_{{$i}}" class="section_2_image"
                                                       data-id="{{$i}}" id="section_2_image_{{$i}}"/>
                                                <div class="detail">
                                                    <div class="m-t-0">
                                                        <label for="title_section_2_image_{{$i}}">Tiêu đề</label>
                                                        <input type="text" name="title_section_2_image_{{$i}}"
                                                               class="title_section_2_image_{{$i}} form-control"
                                                               value="{{$homePage->section_2->$key_image->title ?? ''}}">
                                                        <label for="detail_section_2_image_{{$i}}">Mô tả</label>
                                                        <textarea
                                                            class="form-control detail_section_2_image_{{$i}}"
                                                            name="detail_section_2_image_{{$i}}">{{$homePage->section_2->$key_image->detail ?? ''}}</textarea>
                                                        <label for="content_section_2_image_{{$i}}">Nội dung</label>
                                                        <textarea class="form-control content_section_2_image_{{$i}} editor"
                                                                  name="content_section_2_image_{{$i}}">{{$homePage->section_2->$key_image->content ?? ''}}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
        })

        function clickImage(_this) {
            id = $(_this).attr('data-id');
            $(`#section_2_image_${id}`).click();
        }

        $(".section_2_image").change(function (event) {
            id = $(this).attr('data-id');
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();

                // Khi đọc tệp xong, hiển thị hình ảnh
                reader.onload = function (e) {
                    $(`.section_2_image_preview_${id}`).attr('src', e.target.result).show();
                };

                reader.readAsDataURL(file);
            }
        });

        function clickImage3(_this) {
            id = $(_this).attr('data-id');
            $(`#section_3_image_${id}`).click();
        }

        $(".section_3_image").change(function (event) {
            id = $(this).attr('data-id');
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();

                // Khi đọc tệp xong, hiển thị hình ảnh
                reader.onload = function (e) {
                    $(`.section_3_image_preview_${id}`).attr('src', e.target.result).show();
                };

                reader.readAsDataURL(file);
            }
        });
    </script>
@endsection
