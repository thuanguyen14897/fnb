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
        <form id="HomepageForm" action="admin/admin_website/submit_privilege" method="post" data-parsley-validate
              novalidate
              enctype="multipart/form-data">
            {{csrf_field()}}
            <div class="col-lg-12">
                <div class="card-box">
                    <div role="tabpanel">
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active">
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
                            <div role="tabpanel" class="tab-pane active" id="tab-vietnamese">
                                <div class="row">
                                    <div class="col-md-12">
                                        <section>
                                            <div class="col-md-12 title_section">Section 1</div>
                                            <div class="col-md-4">
                                                <label for="title_section1">Tiêu đề</label>
                                                <input type="text" name="title_section1" class="title_section1 form-control" value="{{$privilege->section_1->title ?? ''}}">
                                            </div>
                                            <div class="col-md-8">
                                                <label for="content_section1">Nội dung</label>
                                                <input type="text" name="content_section1" class="content_section1 form-control" value="{{$privilege->section_1->content ?? ''}}">
                                            </div>
                                        </section>
                                        <section>
                                            <div class="col-md-12 title_section" style="margin-top: 10px">Section 2</div>
                                            <div class="col-md-4">
                                                <label for="title_section2">Tiêu đề</label>
                                                <input type="text" name="title_section2" class="title_section2 form-control" value="{{$privilege->section_2->title ?? ''}}">
                                            </div>
                                            <div class="col-md-8">
                                                <label for="content_section2">Nội dung</label>
                                                <input type="text" name="content_section2" class="content_section2 form-control" value="{{$privilege->section_2->content ?? ''}}">
                                            </div>
                                        </section>
                                        <div class="col-md-12" style="margin-top: 20px">
                                            <label for="document_use_card">Cách sử dụng thẻ thành viên</label>
                                            <textarea name="document_use_card" class="document_use_card form-control editor">
                                                {{$privilege->document_use_card ?? ''}}
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="tab-english">
                                <div class="row">
                                    <div class="col-md-12">
                                        <section>
                                            <div class="col-md-12 title_section">Section 1</div>
                                            <div class="col-md-4">
                                                <label for="title_section1_en">Tiêu đề</label>
                                                <input type="text" name="title_section1_en" class="title_section1_en form-control" value="{{$privilege->section_1->title_en ?? ''}}">
                                            </div>
                                            <div class="col-md-8">
                                                <label for="content_section1_en">Nội dung</label>
                                                <input type="text" name="content_section1_en" class="content_section1_en form-control" value="{{$privilege->section_1->content_en ?? ''}}">
                                            </div>
                                        </section>
                                        <section>
                                            <div class="col-md-12 title_section" style="margin-top: 10px">Section 2</div>
                                            <div class="col-md-4">
                                                <label for="title_section2_en">Tiêu đề</label>
                                                <input type="text" name="title_section2_en" class="title_section2_en form-control" value="{{$privilege->section_2->title_en ?? ''}}">
                                            </div>
                                            <div class="col-md-8">
                                                <label for="content_section2_en">Nội dung</label>
                                                <input type="text" name="content_section2_en" class="content_section2_en form-control" value="{{$privilege->section_2->content_en ?? ''}}">
                                            </div>
                                        </section>
                                        <div class="col-md-12" style="margin-top: 20px">
                                            <label for="document_use_card_en">Cách sử dụng thẻ thành viên</label>
                                            <textarea name="document_use_card_en" class="document_use_card_en form-control editor">
                                                {{$privilege->document_use_card_en ?? ''}}
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="tab-chinese">
                                <div class="row">
                                    <div class="col-md-12">
                                        <section>
                                            <div class="col-md-12 title_section">Section 1</div>
                                            <div class="col-md-4">
                                                <label for="title_section1_zh">Tiêu đề</label>
                                                <input type="text" name="title_section1_zh" class="title_section1_zh form-control" value="{{$privilege->section_1->title_zh ?? ''}}">
                                            </div>
                                            <div class="col-md-8">
                                                <label for="content_section1_zh">Nội dung</label>
                                                <input type="text" name="content_section1_zh" class="content_section1_zh form-control" value="{{$privilege->section_1->content_zh ?? ''}}">
                                            </div>
                                        </section>
                                        <section>
                                            <div class="col-md-12 title_section" style="margin-top: 10px">Section 2</div>
                                            <div class="col-md-4">
                                                <label for="title_section2_zh">Tiêu đề</label>
                                                <input type="text" name="title_section2_zh" class="title_section2_zh form-control" value="{{$privilege->section_2->title_zh ?? ''}}">
                                            </div>
                                            <div class="col-md-8">
                                                <label for="content_section2_zh">Nội dung</label>
                                                <input type="text" name="content_section2_zh" class="content_section2_zh form-control" value="{{$privilege->section_2->content_zh ?? ''}}">
                                            </div>
                                        </section>
                                        <div class="col-md-12" style="margin-top: 20px">
                                            <label for="document_use_card_zh">Cách sử dụng thẻ thành viên</label>
                                            <textarea name="document_use_card_zh" class="document_use_card_zh form-control editor">
                                                {{$privilege->document_use_card_zh ?? ''}}
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="tab-korea">
                                <div class="row">
                                    <div class="col-md-12">
                                        <section>
                                            <div class="col-md-12 title_section">Section 1</div>
                                            <div class="col-md-4">
                                                <label for="title_section1_ko">Tiêu đề</label>
                                                <input type="text" name="title_section1_ko" class="title_section1_ko form-control" value="{{$privilege->section_1->title_ko ?? ''}}">
                                            </div>
                                            <div class="col-md-8">
                                                <label for="content_section1_ko">Nội dung</label>
                                                <input type="text" name="content_section1_ko" class="content_section1_ko form-control" value="{{$privilege->section_1->content_ko ?? ''}}">
                                            </div>
                                        </section>
                                        <section>
                                            <div class="col-md-12 title_section" style="margin-top: 10px">Section 2</div>
                                            <div class="col-md-4">
                                                <label for="title_section2_ko">Tiêu đề</label>
                                                <input type="text" name="title_section2_ko" class="title_section2_ko form-control" value="{{$privilege->section_2->title_ko ?? ''}}">
                                            </div>
                                            <div class="col-md-8">
                                                <label for="content_section2_ko">Nội dung</label>
                                                <input type="text" name="content_section2_ko" class="content_section2_ko form-control" value="{{$privilege->section_2->content_ko ?? ''}}">
                                            </div>
                                        </section>
                                        <div class="col-md-12" style="margin-top: 20px">
                                            <label for="document_use_card_ko">Cách sử dụng thẻ thành viên</label>
                                            <textarea name="document_use_card_ko" class="document_use_card_ko form-control editor">
                                                {{$privilege->document_use_card_ko ?? ''}}
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="tab-japan">
                                <div class="row">
                                    <div class="col-md-12">
                                        <section>
                                            <div class="col-md-12 title_section">Section 1</div>
                                            <div class="col-md-4">
                                                <label for="title_section1_ja">Tiêu đề</label>
                                                <input type="text" name="title_section1_ja" class="title_section1_ja form-control" value="{{$privilege->section_1->title_ja ?? ''}}">
                                            </div>
                                            <div class="col-md-8">
                                                <label for="content_section1_ja">Nội dung</label>
                                                <input type="text" name="content_section1_ja" class="content_section1_ja form-control" value="{{$privilege->section_1->content_ja ?? ''}}">
                                            </div>
                                        </section>
                                        <section>
                                            <div class="col-md-12 title_section" style="margin-top: 10px">Section 2</div>
                                            <div class="col-md-4">
                                                <label for="title_section2_ja">Tiêu đề</label>
                                                <input type="text" name="title_section2_ja" class="title_section2_ja form-control" value="{{$privilege->section_2->title_ja ?? ''}}">
                                            </div>
                                            <div class="col-md-8">
                                                <label for="content_section2_ja">Nội dung</label>
                                                <input type="text" name="content_section2_ja" class="content_section2_ja form-control" value="{{$privilege->section_2->content_ja ?? ''}}">
                                            </div>
                                        </section>
                                        <div class="col-md-12" style="margin-top: 20px">
                                            <label for="document_use_card_ja">Cách sử dụng thẻ thành viên</label>
                                            <textarea name="document_use_card_ja" class="document_use_card_ja form-control editor">
                                                {{$privilege->document_use_card_ja ?? ''}}
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
@endsection
