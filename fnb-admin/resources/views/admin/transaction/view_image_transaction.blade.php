<div class="modal-dialog" style="width: 50%;">
    <div class="modal-content" style="background: #eee">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">{{$title}}</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <ul class="nav nav-tabs navtab-bg nav-justified">
                    <li class="active">
                        <a href="#exterior" data-toggle="tab" aria-expanded="false">
                            <span class="visible-xs">Ngoại thất</span>
                            <span class="hidden-xs">Ngoại thất</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="#interior" data-toggle="tab" aria-expanded="false">
                            <span class="visible-xs">Nội thất</span>
                            <span class="hidden-xs">Nội thất</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="#paper" data-toggle="tab" aria-expanded="false">
                            <span class="visible-xs">Giấy tờ</span>
                            <span class="hidden-xs">Giấy tờ</span>
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="exterior">
                        <div class="row">
                            <div style="display: flex;flex-wrap: wrap">
                                @if(count($transaction->image_transaction) > 0)
                                    @foreach($transaction->image_transaction as $key => $value)
                                        @php
                                            $image = '<a href="'.$myService->getImageServiceS3($value->image_s3).'"
                                               data-lightbox="customer-profile" class="display-block mbot5">
                                                <img
                                                    src="'.$myService->getImageServiceS3($value->image_s3).'"
                                                    alt="image" class="img-responsive img-rounded">
                                            </a>';
                                        @endphp
                                        <div
                                            style="display: flex;justify-content:center;margin-top: 5px;margin-right: 5px;width: 32%"
                                            class="show_image">
                                            {!! $image !!}
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="interior">
                        <div class="row">
                            <div style="display: flex;flex-wrap: wrap">
                            @if(count($transaction->image_transaction_interior) > 0)
                                @foreach($transaction->image_transaction_interior as $key => $value)
                                    @php
                                        $image_interior = '<a href="'.$myService->getImageServiceS3($value->image_s3).'"
                                           data-lightbox="customer-profile" class="display-block mbot5">
                                            <img
                                                src="'.$myService->getImageServiceS3($value->image_s3).'"
                                                alt="image" class="img-responsive img-rounded">
                                        </a>';
                                    @endphp
                                    <div
                                        style="display: flex;justify-content:center;margin-top: 5px;margin-right: 5px;width: 32%"
                                        class="show_image">
                                        {!! $image_interior !!}

                                    </div>
                                @endforeach
                            @endif
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="paper">
                        <div class="row">
                            <div style="display: flex;flex-wrap: wrap">
                            @if(count($transaction->image_transaction_paper) > 0)
                                @foreach($transaction->image_transaction_paper as $key => $value)
                                    @php
                                        $image_paper = '<a href="'.$myService->getImageServiceS3($value->image_s3).'"
                                           data-lightbox="customer-profile" class="display-block mbot5">
                                            <img
                                                src="'.$myService->getImageServiceS3($value->image_s3).'"
                                                alt="image" class="img-responsive img-rounded">
                                        </a>';
                                    @endphp
                                    <div
                                        style="display: flex;justify-content:center;margin-top: 5px;margin-right: 5px;width: 32%"
                                        class="show_image">
                                        {!! $image_paper !!}

                                    </div>
                                @endforeach
                            @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default"
                    data-dismiss="modal">{{lang('dt_close')}}</button>
        </div>
    </div>
</div>
