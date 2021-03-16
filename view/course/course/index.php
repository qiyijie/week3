<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://cdn.staticfile.org/jquery/2.1.1/jquery.min.js"></script>
    <script src="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <!--第一步：引入Javascript / CSS （CDN）-->
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="http://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">

    <!-- DataTables -->
    <script type="text/javascript" charset="utf8" src="http://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script>
</head>
<body>

<a class="layui-btn layui-btn-sm" href="{:url('create')}">添加课程</a>
<a href="{:url('exports')}" class="layui-btn layui-btn-sm">导出分类</a>
<!--第二步：添加如下 HTML 代码-->
<table id="table_id_example" class="display">
    <thead>
    <tr>
        <th>ID</th>
        <th>排序</th>
        <th>课程名称</th>
        <th>价格</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody>
    {volist name='data' id= 'vo'}
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>{$vo.id}</td>
    </tr>
    {/volist}
    </tbody>
</table>

</body>
</html>
<script>
    // <!--第三步：初始化Datatables-->
    $(document).ready( function () {
        $('#table_id_example').DataTable({
            //下拉的分页数量
            lengthMenu:[5,10,15,20,15,30,50,100],
            serverSide: true,
            ajax: {
                url: "{:url('getList')}",
                type: 'GET'
            },
            "columns": [
                {"data": "id"},
                {"data": "sort"},
                {"data": "course_name"},
                {"data": "course_price"},
                {"data": 'btm','defaultContent':`<a href="{:url('delete',['id'=>$vo.id])}" class="click">删除</a>`},
            ]
        });
    } );
    //生成token
    var _token = "{{csrf_token()}}"
    //框架加载完成后触发
    datatable.on('draw',function () {
        //给删除按钮绑定点击事件
        $('.click').click(function () {
            let url = $(this).attr('href')
            layer.confirm('请确认此次操作？', {
                btn: ['确认','取消'] //按钮
            },()=>{
                $.ajax({
                    url,
                    data:{_token},
                    type:"DELETE",
                    dataType:'json',
                }).then(({code,msg})=>{
                    if (code == 200){
                        layer.msg(msg,{time:1000,icon:1},()=>{
                            location.replace(location.href)
                        })
                    }
                })
            })
            return false
        })
    })
</script>
