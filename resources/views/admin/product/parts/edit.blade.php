<link href="{{asset('assets/admin')}}/assets/plugins/select2/select2.min.css" rel="stylesheet"/>
<form id="updateForm" method="POST" enctype="multipart/form-data" action="{{route('products.update',$product->id)}}">
    @csrf
    @method('PUT')
    <input name="id" value="{{$product->id}}" type="hidden">
    <div class="form-group">
        <label for="name" class="form-control-label">الصورة</label>
        <input type="file" id="testDrop" class="dropify" name="main_image" data-default-file="{{$product->main_image}}"/>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="title_ar" class="form-control-label">العنوان (ar)</label>
                <input type="text" class="form-control" name="title_ar" value="{{$product->title_ar}}">
            </div>
        </div>
        <div class="col-lg-6">
            <div class="form-group">
                <label for="title_en" class="form-control-label">العنوان (en)</label>
                <input type="text" class="form-control" name="title_en" value="{{$product->title_en}}">
            </div>
        </div>

        <div class="col-lg-6">
            <div class="form-group">
                <label class="form-label">التصنيف الرئيسي</label>
                <select name="category_id" id="category_id" class="form-control select2"
                        data-placeholder="--- اختيار التصنيف ---">
                    <option disabled>--- اختيار التصنيف ---</option>
                    @foreach($mainCategories as $category)
                        <option
                            value="{{$category->id}} {{($product->category_id == $category->id) ?? 'selected'}}">{{$category->title_ar}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="form-group">
                <label class="form-label">التصنيف الفرعي</label>
                <select name="sub_category_id" id="sub_category_id" class="form-control select2"
                        data-placeholder="--- اختيار التصنيف الفرعي ---">
                    <option disabled>--- اختيار التصنيف ---</option>
                    @foreach($categories as $sub)
                        <option
                            value="{{$sub->id}} {{($product->sub_category_id == $sub->id) ?? 'selected'}}">
                            {{$sub->title_ar}}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="form-group">
                <label class="form-label">وصف المنتج (ar)</label>
                <textarea rows="3" name="details_at" id="details_at"
                          class="form-control">{{$product->details_at}}</textarea>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="form-group">
                <label class="form-label">وصف المنتج (en)</label>
                <textarea rows="3" name="details_en" id="details_en"
                          class="form-control">{{$product->details_en}}</textarea>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">اغلاق</button>
        <button type="submit" class="btn btn-success" id="updateButton">تعديل</button>
    </div>
</form>
<script>
    $('.dropify').dropify()
    // Show Categories
    var categories = JSON.parse('<?php echo json_encode($mainCategories) ?>');

    $(document).on('change', '#category_id', function () {
        var id = $(this).val();
        var category = categories.filter(oneObject => oneObject.id == id)
        if (category.length > 0) {
            var subCategories = category[0].sub_category

            $('#sub_category_id').html('<option value="">--- اختيار التصنيف الفرعي ---</option>')

            $.each(subCategories, function (index) {
                console.log(subCategories[index].sub_category.title_ar)
                $('#sub_category_id').append('<option value="' + subCategories[index].sub_category.id + '">' + subCategories[index].sub_category.title_ar + '</option>')
            })
        }
    })
</script>
<script src="{{asset('assets/admin')}}/assets/js/select2.js"></script>
<script src="{{asset('assets/admin')}}/assets/plugins/select2/select2.full.min.js"></script>

