<script>
    $('.date').datetimepicker({
        format: 'YYYY-MM-DD HH:mm',
        locale: "zh-CN",
        toolbarPlacement: 'bottom',
        showClear: true,
    });

    /* 新增 */
    function create() {
        window.location.href = '{{ $path }}' + '/create/';
    }

    /* 查询 */
    $('#btn_query').click(function () {
        $('#table').bootstrapTable('selectPage', 1);
    });

    /* 删除 */
    var remove_open = false;
    $("#modal_remove").click(function () {
        if (remove_open == true) {
            return false;
        }

        var state = 0;
        var rows = $('#table').bootstrapTable('getSelections');

        var ids = [];
        for (var i = 0; i < rows.length; i++) {
            ids[ids.length] = rows[i].id;
        }

        $.ajax({
            url: '{{ $path }}' + '/state',
            type: 'POST',
            data: {'_token': '{{ csrf_token() }}', 'ids': ids, 'state': state},
            success: function () {
                $('#modal').modal('hide');
                $('#table').bootstrapTable('refresh');
            }
        });
    });

    function remove() {
        remove_open = false;
        var rows = $('#table').bootstrapTable('getSelections');
        if (rows.length > 0) {
            $('#msg').html('您确认删除这<strong><span class="text-danger">' + rows.length + '</span></strong>条信息吗？');
            $('#modal_remove').show();
        } else {
            $('#msg').html('请选择要删除的数据！');
            $('#modal_remove').hide();
        }
    }

    /* 修改状态 */
    $('.state').click(function () {
        var state = $(this).val();
        var rows = $('#table').bootstrapTable('getSelections');

        var ids = [];
        for (var i = 0; i < rows.length; i++) {
            ids[ids.length] = rows[i].id;
        }

        if (ids.length > 0) {
            $.ajax({
                url: '{{ $path }}' + '/state',
                type: 'POST',
                data: {'_token': '{{ csrf_token() }}', 'ids': ids, 'state': state},
                success: function () {
                    $('#table').bootstrapTable('refresh');
                }
            });
        }
    });

    /* 筛选 */
    $('.filter').click(function () {
        var value = $(this).val();
        $('#state').val(value);
        $('#table').bootstrapTable('selectPage', 1);

        //改变按钮样式
        $('.filter').removeClass('btn-primary btn-info btn-success btn-danger btn-warning');
        $('.filter').addClass('btn-default');
        $(this).removeClass('btn-default');
        $(this).addClass($(this).data('active'));
    });

    /* 表格 */
    var place_index;
    var select_index;
    var original_y;
    var move_down = 1;

    $('#table').bootstrapTable({
        method: 'get',
        url: '{{ $path }}' + '/table',
        pagination: true,
        pageSize: 25,
        pageList: [25, 50, 100, 200],
        sidePagination: 'server',
        clickToSelect: true,
        striped: true,

        onLoadSuccess: function (data) {
            $('#modal_query').modal('hide');
            $('#table tbody').sortable({
                cursor: 'move',
                axis: 'y',
                revert: true,
                start: function (e, ui) {
                    select_index = ui.item.attr('data-index');
                    original_y = e.pageY;
                },
                sort: function (e, ui) {
                    if (e.pageY > original_y) {
                        place_index = $(this).find('tr').filter('.ui-sortable-placeholder').prev('tr').attr('data-index');
                        move_down = 1;
                    }
                    else {
                        place_index = $(this).find('tr').filter('.ui-sortable-placeholder').next('tr').attr('data-index');
                        move_down = 0;
                    }
                },
                update: function (e, ui) {
                    var select_id = data.rows[select_index].id;
                    var place_id = data.rows[place_index].id;

                    if (select_id == place_id) {
                        return;
                    }

                    $.ajax({
                        url: '{{ $path }}' + '/sort',
                        type: 'get',
                        async: true,
                        data: {select_id: select_id, place_id: place_id, move_down: move_down},
                        success: function (data) {
                            if (data.status_code != 200) {
                                $('#table tbody').sortable('cancel');
                                $('#table').bootstrapTable('refresh');
                            }
                        },
                    });
                }
            });
            $('#table tbody').sortable('disable');
        },
        onEditableSave: function (field, row, old, $el) {
            $.ajax({
                url: "/admin/contents/" + row.id + '/save',
                data: {'_token': '{{ csrf_token() }}', 'clicks': row.clicks, 'views': row.views},
                success: function (data, status) {
                },
                error: function (data) {
                    alert('Error');
                },
            });
        },
        queryParams: function (params) {
            var object = $('#form_query input,#form_query select').serializeObject();
            object['state'] = $('#state').val();
            object['offset'] = params.offset;
            object['limit'] = params.limit;
            return object;
        },
    });
</script>