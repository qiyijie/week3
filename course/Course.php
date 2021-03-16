<?php

namespace app\admin\controller\course;

use Api\AliyunOss;
use app\admin\model\system\SystemAttachment;
use service\JsonService;
use service\SystemConfigService;
use think\Cache;
use think\Controller;
use think\Db;
use think\Request;

class Course extends Controller
{

    /**
     * 初始化
     */
    protected function init()
    {
        return AliyunOss::instance([
            'AccessKey' => SystemConfigService::get('accessKeyId'),
            'AccessKeySecret' => SystemConfigService::get('accessKeySecret'),
            'OssEndpoint' => SystemConfigService::get('end_point'),
            'OssBucket' => SystemConfigService::get('OssBucket'),
            'uploadUrl' => SystemConfigService::get('uploadUrl'),
        ]);
    }


    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $data = \app\admin\model\course\Course::all();
        return view('index',compact('data'));
    }

    public function getList(Request $request)
    {
        //        搜索的条件
        $search = $request->get('search.value');
        //        分页开始的位置
        $start = $request->get('start');
        //        分页结束的位置
        $length = $request->get('length');
        //        条件存入缓存
        Cache::store('redis')->set('search',$search);
        //        取出条件
        $where = Cache::store('redis')->get('search');
        //        分页查询
        $data = Db::table('eb_course')->where('course_name','like',"%$where%")->limit($start,$length)->select();
        //        高亮显示
        foreach ($data as &$val){
            $val['course_name'] = str_replace($search,"<span style='color: red;font-weight: bold'>$search</span>",$val['course_name']);
        }
        if ($request->get('order.0.column')!=0){
            //获取排序方式
            $order =$request->get('order.0.dir');

            //获取排序字段
            $column = $request->get('columns.1.data');
            //执行
            $data = Db::table('eb_course')->where('course_name','like',"%$where%")->order($column,$order)->limit($start,$length)->select();
        }
//        返回数据
//        print_r($data);die;
        return json(['code' => 200,'msg' => '查询成功','data' => $data]);
    }
    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
       return view('create');
    }

    /**
     * 异步上传
     */
    public function uploadFile(Request $request)
    {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Content-type: text/html; charset=gbk32");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        $folder = input('folder');
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit; // finish preflight CORS requests here
        }
        if ( !empty($_REQUEST[ 'debug' ]) ) {
            $random = rand(0, intval($_REQUEST[ 'debug' ]) );
            if ( $random === 0 ) {
                header("HTTP/1.0 500 Internal Server Error");
                exit;
            }
        }
        // header("HTTP/1.0 500 Internal Server Error");
        // exit;
        // 5 minutes execution time
        set_time_limit(5 * 60);
        // Uncomment this one to fake upload time
        usleep(5000);
        // Settings
        $targetDir = './Public'.DIRECTORY_SEPARATOR.'file_material_tmp';            //存放分片临时目录
        if($folder){
            $uploadDir = './Public'.DIRECTORY_SEPARATOR.'file_material'.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR.date('Ymd');
        }else{
            $uploadDir = './Public'.DIRECTORY_SEPARATOR.'file_material'.DIRECTORY_SEPARATOR.date('Ymd');    //分片合并存放目录
        }

        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds

        // Create target dir
        if (!file_exists($targetDir)) {
            mkdir($targetDir,0777,true);
        }
        // Create target dir
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir,0777,true);
        }
        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }
        $oldName = $fileName;

        $fileName = iconv('UTF-8','gb2312',$fileName);
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        // $uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
        // Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;
        // Remove old temp files
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory111."}, "id" : "id"}');
            }
            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}_{$chunk}" || $tmpfilePath == "{$filePath}_{$chunk}") {
                    continue;
                }
                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.(part|parttmp)$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }
        // Open temp file
        if (!$out = fopen("{$filePath}_{$chunk}", "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream222."}, "id" : "id"}');
        }
        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file333."}, "id" : "id"}');
            }
            // Read binary input stream and append it to temp file
            if (!$in = fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream444."}, "id" : "id"}');
            }
        } else {
            if (!$in = fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream555."}, "id" : "id"}');
            }
        }
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }
        fclose($out);
        fclose($in);
        rename("{$filePath}_{$chunk}", "{$filePath}_{$chunk}");
        $index = 0;
        $done = true;
        for( $index = 0; $index < $chunks; $index++ ) {
            if ( !file_exists("{$filePath}_{$index}") ) {
                $done = false;
                break;
            }
        }

        if ($done) {
            $pathInfo = pathinfo($fileName);
            $hashStr = substr(md5($pathInfo['basename']),8,16);
            $hashName = time() . $hashStr . '.' .$pathInfo['extension'];
            $uploadPath = $uploadDir . DIRECTORY_SEPARATOR .$hashName;
            if (!$out = fopen($uploadPath, "wb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream666."}, "id" : "id"}');
            }
            //flock($hander,LOCK_EX)文件锁
            if ( flock($out, LOCK_EX) ) {
                for( $index = 0; $index < $chunks; $index++ ) {
                    if (!$in = fopen("{$filePath}_{$index}", "rb")) {
                        break;
                    }
                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }
                    fclose($in);
                    unlink("{$filePath}_{$index}");
                }
                flock($out, LOCK_UN);
            }
            fclose($out);
            $response = [
                'success'=>true,
                'oldName'=>$oldName,
                'filePath'=>$uploadPath,
//                'fileSize'=>$data['size'],
                'fileSuffixes'=>$pathInfo['extension'],          //文件后缀名
//                'file_id'=>$data['id'],
            ];
            // 同步到阿里云oss
            try {
                $aliyunOss = $this->init();
                $res = $aliyunOss->upload('file');
                if ($res){
                    SystemAttachment::attachmentAdd($res['key'],15678,'image/jpg',$res['url'],$res['url'],1,1,time());
                }else{
                    return JsonService::fail($aliyunOss->getErrorInfo()['msg']);
                }
            }catch (\Exception $e){
                return JsonService::fail('上传失败');
            }
            return json($response);
        }
        // Return Success JSON-RPC response
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $param = $request->only('course_name,course_desn,sort,course_pic,course_price');
       $result = $this->validate($param,[
          'course_name|课程名称' => 'require|unique:course',
           'course_desn|课程简介' => 'require',
           'sort|排序' => 'require',
           'course_pic|课程图片' => 'require',
           'course_price|课程价格' => 'require',
       ]);
       if (true !== $result){
          $this->error($result,'create');
       }
       $res = \app\admin\model\course\Course::create($param);
       if ($res){
        Cache::store('redis')->set('kecheng'.$res['id'],$res);
        $this->redirect('index');
       }
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function exports()
    {
        $res = \app\admin\model\course\Course::all()->toArray();
        $resultPHPExcel = new \PHPExcel();
//设置参数

//设值

        $resultPHPExcel->getActiveSheet()->setCellValue('A1', 'id');
        $resultPHPExcel->getActiveSheet()->setCellValue('B1', '名称');
        $resultPHPExcel->getActiveSheet()->setCellValue('C1', '内容');
        $resultPHPExcel->getActiveSheet()->setCellValue('D1', '价格');
        $resultPHPExcel->getActiveSheet()->setCellValue('E1', '图片');
        $i = 2;
        foreach ($res as $item) {
            $resultPHPExcel->getActiveSheet()->setCellValue('A' . $i, $item['id']);
            $resultPHPExcel->getActiveSheet()->setCellValue('B' . $i, $item['course_name']);
            $resultPHPExcel->getActiveSheet()->setCellValue('C' . $i, $item['course_desn']);
            $resultPHPExcel->getActiveSheet()->setCellValue('D' . $i, $item['course_price']);
            $resultPHPExcel->getActiveSheet()->setCellValue('E' . $i, $item['course_pic']);
            $objDrawing = new \PHPExcel_Worksheet_Drawing();
            $objDrawing->setPath( $item['course_pic']);//这里拼接 . 是因为要在根目录下获取
            // 设置宽度高度
            $objDrawing->setHeight(50);//照片高度
            $objDrawing->setWidth(50); //照片宽度
            /*设置图片要插入的单元格*/
            $objDrawing->setCoordinates('C' . $i);
            // 图片偏移距离
            $objDrawing->setOffsetX(0);
            $objDrawing->setOffsetY(0);
            $objDrawing->setWorksheet($resultPHPExcel->getActiveSheet());
            $i++;
        }
        //设置导出文件名

        $outputFileName = 'total.xls';

        $xlsWriter = new \PHPExcel_Writer_Excel5($resultPHPExcel);

        ob_end_clean();
        header("Content-Type: application/force-download");

        header("Content-Type: application/octet-stream");

        header("Content-Type: application/download");

        header('Content-Disposition:inline;filename="' . $outputFileName . '"');

        header("Content-Transfer-Encoding: binary");

        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

        header("Pragma: no-cache");

        $xlsWriter->save("php://output");
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if(\app\admin\model\course\Course::destroy($id)){
            Cache::store('redis')->handler()->del('kecheng'.$id);
            $this->success('删除成功','index');
        }else{
            $this->error('删除失败','index');
        }


    }
}
