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
        <form id="HomepageForm" action="admin/admin_website/submit_page_not_found" method="post" data-parsley-validate
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
                                        <div class="col-md-12" style="margin-top: 20px">
                                            <label for="document_paper">Nội dung trang</label>
                                            <textarea name="document_paper" class="document_paper form-control editor">
                                                {{$page_not_found->document_paper ?? ''}}
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="tab-english">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="col-md-12" style="margin-top: 20px">
                                            <label for="document_paper_en">Nội dung trang</label>
                                            <textarea name="document_paper_en" class="document_paper_en form-control editor">
                                                {{$page_not_found->document_paper_en ?? ''}}
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="tab-chinese">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="col-md-12" style="margin-top: 20px">
                                            <label for="document_paper_zh">Nội dung trang</label>
                                            <textarea name="document_paper_zh" class="document_paper_zh form-control editor">
                                                {{$page_not_found->document_paper_zh ?? ''}}
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="tab-korea">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="col-md-12" style="margin-top: 20px">
                                            <label for="document_paper_ko">Nội dung trang</label>
                                            <textarea name="document_paper_ko" class="document_paper_ko form-control editor">
                                                {{$page_not_found->document_paper_ko ?? ''}}
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="tab-japan">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="col-md-12" style="margin-top: 20px">
                                            <label for="document_paper_ja">Nội dung trang</label>
                                            <textarea name="document_paper_ja" class="document_paper_ja form-control editor">
                                                {{$page_not_found->document_paper_ja ?? ''}}
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
