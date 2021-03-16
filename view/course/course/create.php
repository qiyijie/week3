{extend name="public/container"}
{block name='head_top'}
<style>
    .layui-form-item .special-label{
        width: 50px;
        float: left;
        height: 30px;
        line-height: 38px;
        margin-left: 10px;
        margin-top: 5px;
        border-radius: 5px;
        background-color: #0092DC;
        text-align: center;
    }
    .layui-form-item .special-label i{
        display: inline-block;
        width: 18px;
        height: 18px;
        font-size: 18px;
        color: #fff;
    }
    .layui-form-item .label-box{
        border: 1px solid;
        border-radius: 10px;
        position: relative;
        padding: 10px;
        height: 30px;
        color: #fff;
        background-color: #393D49;
        text-align: center;
        cursor: pointer;
        display: inline-block;
        line-height: 10px;
    }
    .layui-form-item .label-box p{
        line-height: inherit;
    }
    .layui-form-mid{
        margin-left: 18px;
    }
    .m-t-5{
        margin-top:5px;
    }
    .edui-default .edui-for-image .edui-icon{
        background-position: -380px 0px;
    }
</style>
<script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/ueditor/third-party/zeroclipboard/Zeroclipboard.js"></script>
<script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/ueditor/ueditor.config.js"></script>
<script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/ueditor/ueditor.all.min.js"></script>
<script type="text/javascript" src="{__ADMIN_PATH}plug/ueditor/lang/zh-cn/zh-cn.js"></script>
<script type="text/javascript" src="{__ADMIN_PATH}js/aliyun-oss-sdk-4.4.4.min.js"></script>
<script type="text/javascript" src="{__ADMIN_PATH}js/request.js"></script>
<script type="text/javascript" src="{__MODULE_PATH}widget/lib/plupload-2.1.2/js/plupload.full.min.js"></script>
<script type="text/javascript" src="{__MODULE_PATH}widget/OssUpload.js"></script>
<!--引入CSS-->
<link rel="stylesheet" type="text/css" href="/webuploader/webuploader.css">
<script src="https://cdn.staticfile.org/jquery/3.2.1/jquery.min.js"></script>
<!--引入JS-->
<script type="text/javascript" src="/webuploader/webuploader.js"></script>
{/block}
{block name="content"}
    <div class="layui-row layui-col-space15"  id="app">
        <form action="{:url('save')}" class="layui-form" method="post" enctype="multipart/form-data">
            <div class="layui-col-md12">
                <div class="layui-card" v-cloak="">
                    <div class="layui-card-body" style="padding: 10px 150px;">
                        <div class="layui-form-item">
                            <label class="layui-form-label">课程名称</label>
                            <div class="layui-input-block">
                                <input type="text" name="course_name" required  autocomplete="off" placeholder="请输入课程名称" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">课程简介</label>
                            <div class="layui-input-block">
                                <textarea placeholder="请输入新闻简介"   name="course_desn" class="layui-textarea"></textarea>
                            </div>
                        </div>
                        <div class="layui-form-item m-t-5">
                            <label class="layui-form-label">排序</label>
                            <div class="layui-input-block">
                                <input type="number" style="width: 20%" required name="sort"  autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item m-t-5" v-cloak="">
                            <label class="layui-form-label">课程封面</label>
                            <div class="layui-input-block">
                                <div id="uploadfile">
                                    <!--用来存放文件信息-->
                                    <div id="the_2655" class="uploader-list"></div>
                                    <div class="form-group form-inline">
                                        <input type="hidden" id="pic" value="" name="course_pic">
                                        <div id="pick_2655" style="float:left">选择文件</div>
                                        <img src="" id="img" alt="" style="width: 100px;height: 100px">
                                        <!--                <button id="Btn_2655" class="btn btn-default" style="padding: 5px 10px;border-radius: 3px;">开始上传</button>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="layui-form-item m-t-5">
                            <label class="layui-form-label">价格</label>
                            <div class="layui-input-block">
                                <input type="number" style="width: 20%" required name="course_price"  autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item submit" style="margin-bottom: 10px">
                            <div class="layui-input-block">
                                <button class="layui-btn layui-btn-normal" type="submit">立即提交</button>
                                <button class="layui-btn layui-btn-primary clone">取消</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript" src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name='script'}
<script>
    uploadfiles(2655,"files");

    function uploadfiles(ids,folder) {
        $(function(){
                var $list = $("#the_"+ids);
                $btn = $("#Btn_"+ids);
                var uploader = WebUploader.create({

                    auto:true,
                    resize: false, // 不压缩image
                    swf: '/webuploader/uploader.swf', // swf文件路径
                    server: '{:url("uploadFile")}', // 文件接收服务端。
                    pick: "#pick_"+ids, // 选择文件的按钮。可选
                    chunked: true, //是否要分片处理大文件上传
                    chunkSize:5*1024*1024, //分片上传，每片2M，默认是5M
                    //fileSizeLimit: 6*1024* 1024 * 1024,    // 所有文件总大小限制 6G
                    fileSingleSizeLimit: 10*1024* 1024 * 1024,    // 单个文件大小限制 5 G
                    formData: {
                        folder:folder  //自定义参数
                    },
                    // 压缩图片上传
                    compress: {
                        width: 450,
                        height: 450,
                    },
                    });
                uploader.on( 'uploadSuccess', function( file ,res) {
                    console.log(res)
                    let src = res.filePath;
                    $("#pic").val(src)
                    $("#img").attr('src','http://www.bwvr.com/'+src)
                });
            }
        )}

</script>
{/block}