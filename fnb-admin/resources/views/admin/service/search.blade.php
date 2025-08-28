<div class="row m-b-10">
    <div class="col-md-2">
        <label for="group_category_service_search">{{lang('Nhóm danh mục')}}</label>
        <select class="group_category_service_search select2" id="group_category_service_search"
                data-placeholder="Chọn ..." name="group_category_service_search">
            <option></option>
        </select>
    </div>
    <div class="col-md-2">
        <label for="category_service_search">{{lang('Danh mục')}}</label>
        <select class="category_service_search select2" id="category_service_search"
                data-placeholder="Chọn ..." name="category_service_search">
            <option></option>
        </select>
    </div>
    <div class="col-md-2">
        <label for="status_search">Trạng thái</label>
        <select class="status_search select2" id="status_search"
                data-placeholder="Chọn ..." name="status_search">
            <option></option>
            <option value="-1">Tất cả</option>
            @foreach(getListStatusService() as $key => $value)
                <option value="{{$value['id']}}">{{$value['name']}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label for="customer_search">Đối tác</label>
        <select class="customer_search select2" id="customer_search"
                data-placeholder="Chọn ..." name="customer_search">
            <option></option>
        </select>
    </div>
</div>
