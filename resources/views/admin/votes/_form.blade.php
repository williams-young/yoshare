<ul id="tabs" class="nav nav-tabs">
    <li class="active">
        <a href="#tabHome" data-toggle="tab">基本信息</a>
    </li>
    <li>
        <a href="#tabContent" data-toggle="tab">正文</a>
    </li>
</ul>
<div id="tabContents" class="tab-content">
    <div id="tabHome" class="tab-pane fade in active padding-t-15">
        <div class="form-group">
            {!! Form::label('title', '标题:', ['class' => 'control-label col-sm-1']) !!}
            <div class="input-group col-sm-10">
                {!! Form::text('title', null, ['class' => 'form-control']) !!}
                <span class="input-group-btn">
                      <button class="btn btn-info btn-flat add_option" type="button">添加选项</button>
                </span>
            </div>
        </div>

        <div class="form-group">
            @if(isset($vote))
                <label class="col-sm-1 control-label">类型:</label>
                <div class="col-sm-5">
                    <input type="radio" name="multiple" @if($vote->multiple==\App\Models\Vote::MULTIPLE_FALSE) value="{{$vote->multiple}}" checked @endif>单选
                    <input type="radio" name="multiple" @if($vote->multiple==\App\Models\Vote::MULTIPLE_TRUE) value="{{$vote->multiple}}" checked @endif >多选
                </div>
            @else
                <label class="col-sm-1 control-label">类型:</label>
                <div class="col-sm-5">
                    <input type="radio" name="multiple" value="{{\App\Models\Vote::MULTIPLE_FALSE}}" checked> 单选
                    <input type="radio" name="multiple" value="{{\App\Models\Vote::MULTIPLE_TRUE}}">多选
                </div>
            @endif
            {!! Form::label('link', '外链:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                {!! Form::text('link', null, ['class' => 'form-control']) !!}
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('begin_date', '开始日期:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                <div class='input-group date' id='begin_date'>
                    {!! Form::text('begin_date', null, ['class' => 'form-control']) !!}
                    <span class="input-group-addon"> <span class="glyphicon glyphicon-calendar"></span> </span>
                </div>
            </div>
            {!! Form::label('end_date', '截止日期:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                <div class='input-group date' id='end_date'>
                    {!! Form::text('end_date', null, ['class' => 'form-control']) !!}
                    <span class="input-group-addon"> <span class="glyphicon glyphicon-calendar"></span> </span>
                </div>
            </div>
        </div>

        @if(isset($vote))
            @foreach($vote->items as $k=>$item)
                <div class="form-group file" data-id="{{$item->id}}">
                    <label class="control-label col-sm-1">选项({{$k+1}}):</label>
                    <div class="col-sm-11">
                        <div class="input-group">
                            <input type="hidden" name="item_id[]" value="{{$item->id}}">
                            <input type="text" id="item_title{{$k+1}}" class="form-control " value="{{$item->title}}" name="item_title[]">
                            <span class="input-group-addon file_del1">
                        <span class="glyphicon glyphicon-remove"></span>
                    </span>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="form-group file">
                <label class="control-label col-sm-1">选项(1):</label>
                <div class="col-sm-11">
                    <div class="input-group">
                        <input type="text" id="item_title" class="form-control" value="" name="item_title[]">
                        <span class="input-group-addon file_del2">
                    <span class="glyphicon glyphicon-remove"></span>
                </span>
                    </div>
                </div>
            </div>
        @endif

        <div class="form-group">
            {!! Form::label('image_url', '图片地址:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-11">
                {!! Form::text('image_url', null, ['class' => 'form-control']) !!}
            </div>
        </div>

        <div class="form-group">
            <label for="image_file" class="control-label col-sm-1">上传图片:</label>
            <div class=" col-sm-11">
                <input id="image_file" name="image_file" type="file" data-preview-file-type="text" data-upload-url="/files/upload">
            </div>
        </div>
    </div>

    <div id="tabContent" class="tab-pane fade padding-t-15">
        <div class="form-group">
            <div class="col-sm-12">
                {!! Form::textarea('description', null, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>
</div>

<div class="box-footer">
    <button type="button" class="btn btn-default" onclick="location.href='/votes';">取　消</button>
    <button type="submit" class="btn btn-info pull-right" id="submit">保　存</button>
</div>

<script>
    $(function () {
        $('#begin_date').datetimepicker({
            format: 'YYYY/MM/DD HH:mm',
            locale: 'zh-cn'
        });
        $('#end_date').datetimepicker({
            format: 'YYYY/MM/DD HH:mm',
            locale: 'zh-cn'
        });
    });

    //上传图片
    var image_url = $('#image_url').val();
    var images = [];

    if (image_url == null || image_url.length > 0) {
        images = ['<img height="240" src="' + $('#image_url').val() + '">'];
    }

    $('#image_file').fileinput({
        language: 'zh',
        uploadExtraData: {_token: '{{ csrf_token() }}'},
        allowedFileExtensions: ['jpg', 'gif', 'png'],
        initialPreview: images,
        maxFileSize: 10240,
        resizeImage: true,
        maxImageWidth: 640,
        maxImageHeight: 960,
    });

    $('#image_file').on('fileuploaded', function (event, data) {
        $('#image_url').val(data.response.data);
    });

    //提示上传图片
    function toastrs(message) {
        toastr.options = {
            'closeButton': true,
            'positionClass': 'toast-bottom-right',
        };
        toastr['warning'](message);
        return false;
    }

    $('.submit').click(function () {
        var image_file = $('#image_file').fileinput('getFileStack');
        if (image_file.length > 0) {
            return toastrs('请先上传图片!');
        }
    })

    $(document).ready(function () {
        CKEDITOR.replace('description', {
            extraPlugins: 'uploadimage,image2',
            height: 900,
            filebrowserUploadUrl: '{{ url('files/upload') }}?_token={{csrf_token()}}',
            contentsCss: [CKEDITOR.basePath + 'contents.css', '/css/app.css'],
            image2_alignClasses: ['image-align-left', 'image-align-center', 'image-align-right'],
            image2_disableResizer: true
        });
    });

    //增加选项
    $(".add_option").click(function () {
        var i = $(".file").size();
        var n = i + 1;
        var html =
            '<div class="form-group file " data-id=file>' +
            '<label class="col-sm-1 control-label">选项(' + n + '):</label>' +
            '<div class="col-sm-11">' +
            '<div class="input-group">' +
            '<input type="text" id="item_title" name="item_title[]" class="form-control">' +
            '<span class="input-group-addon file_del">' +
            '<span class="glyphicon glyphicon-remove"></span>' +
            '</span>' +
            '</div>' +
            '</div>' +
            '</div>';

        $(".file:last").after(html);

        //删除file的个数
        $(".file_del").click(function () {
            $(this).parent().parent().parent().remove();
        })
        return false;
    })

    //清空第一个file的内容//新增页面
    $(".file_del2").click(function () {
        $("#item_title").val('');
    })

    //点击删除 //编辑页面原本存在的通过这个删除
    $('.file_del1').click(function () {
        $file = $(this).parent().children('.form-control').attr('id');

        if ($file == 'item_title1') {
            $(this).parent().children('.form-control').val('');
        } else {
            $(this).parent().parent().parent().remove();
        }
    })
</script>

