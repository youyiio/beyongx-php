<?php
namespace app\common\controller;

use app\common\model\FileModel;
use think\facade\Env;

/**
 * 文件上传组件
 * 使用方法，文件控制器中，use \app\common\controller\File,
 * 即会继承这些方法
 */
trait File
{

    /**
     * 通用文件上传
     * 支持参数：file,exts
     */
    public function upload()
    {
   
        $tmpFile = request()->file('file');
        if (empty($tmpFile)) {
            $this->result(null, 0, '请选择上传文件', 'json');
        }

        $rule = [
            'size' => 1024 * 1024 * 200, //200M
        ];

        //通用文件后缀，加强安全;
        $common_file_exts = 'zip,rar,doc,docx,xls,xlsx,ppt,pptx,ppt,pptx,pdf,txt,exe,bat,sh,apk,ipa';
        $common_file_exts .= '.pg,gif,png,jpg,jpeg,webp,bmp';
        $exts = request()->param('exts', ''); //文件格式，中间用,分隔
        if (empty($exts)) {
            $exts = $common_file_exts;
        } else {
            $exts = strtolower($exts);
            $exts = explode(',', $exts);
            $exts = array_diff($exts, ['php']);
            $exts = implode(',', $exts);
        }
        $rule['ext'] = $exts;

        //文件目录
        $filePath = Env::get('root_path') . 'public';
        $fileUrl = DIRECTORY_SEPARATOR . 'upload'. DIRECTORY_SEPARATOR . 'file';
        $path = $filePath . $fileUrl;

        //不能信任前端传进来的文件名, thinkphp默认使表单里的filename后缀
        $file = $tmpFile->validate($rule)->move($path);;
        if (!$file) {
            $this->result(null, 0, $tmpFile->getError(), 'json');
        }

        $saveName = $file->getSaveName(); //实际包含日期+名字：如20180724/erwrwiej...dfd.ext
        $fileUrl = DIRECTORY_SEPARATOR . 'upload'. DIRECTORY_SEPARATOR . 'file' . DIRECTORY_SEPARATOR . $saveName;

        $fileSize = $file->getSize();
        $ext = $file->getExtension();

        //原始上传文件名
        $fileName = $_FILES['file']['name'];
        //存入数据库
        $data = [
            'file_url' => $fileUrl,
            'file_path' => $filePath,
            'size' => $fileSize,
            'ext' => strtolower($ext),
            'name' => $fileName,
            'real_name' => $file->getinfo()['name'],
            'create_by' => $this->uid,
            'create_time' => date_time()
        ];
        $FileModel = new FileModel();
        $fileId = $FileModel->insertGetId($data);

        $data['id'] = $fileId;
        $data['ext_icon_url'] = '/static/common/img/format/' . strtolower($ext) . '.png';

        $this->result($data, 1, '文件上传成功', 'json');
    }

    //上传软件，桌面端软件，如果.exe.zip
    // 文件过大时，需要在php.ini配置post_max_size, upload_max_filesize
    public function uploadSoftware()
    {
        ini_set('memory_limit', '256M');
        //ini_set('post_max_size', '128M');
        //ini_set('upload_max_filesize', '128M');
        $file = request()->file('file');
        if (empty($file)) {
            //$this->error('请选择上传文件');
            $this->result(null, 0, '请选择上传文件', 'json');
        }
        $rule = [
            'ext' => 'zip,rar,exe',
            'size' => 1024*1024*200, //200M
        ];

        $filePath = Env::get('root_path') . 'public';
        $fileUrl = DIRECTORY_SEPARATOR . 'upload'. DIRECTORY_SEPARATOR . 'software';
        $path = $filePath . $fileUrl;
        $check = $file->validate($rule);

        if (!$check) {
            $this->error($file->getError());
        }

        $version = input('param.version');
        $fileName = $file->getInfo('name');

        //不传值时，系统生成文件名，格式为YYYYmmdd/xxx.....xxxx.ext
        $saveName = $version . DIRECTORY_SEPARATOR . $fileName; //文件命名
        $info = $file->move($path, $saveName);
        //$saveName = $info->getSaveName();
        $fileUrl = DIRECTORY_SEPARATOR . 'upload'. DIRECTORY_SEPARATOR . 'software' . DIRECTORY_SEPARATOR . $saveName;

        $fileSize = $info->getSize();

        //原始上传文件名
        $fileName = $_FILES['file']['name'];

        //存入数据库
        $data = [
            'file_url' => $fileUrl,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'create_time' => date_time()
        ];
        $FileModel = new FileModel();
        $fileId = $FileModel->insertGetId($data);

        $data['id'] = $fileId;
        $data['ext'] = $info->getExtension(); //文件后缀

        $this->success('文件上传成功', false, $data);
    }

    //上传应用,移动类应用，如apk, ipa
    public function uploadApp()
    {
        $file = request()->file('file');
        if (empty($file)) {
            $this->error('请选择上传文件');
        }
        $rule = [
            'ext' => 'apk,ipa',
            'size' => 1024*1024*200, //200M
        ];

        $filePath = Env::get('root_path') . 'public';
        $fileUrl = DIRECTORY_SEPARATOR . 'upload'. DIRECTORY_SEPARATOR . 'app';
        $path = $filePath . $fileUrl;
        $check = $file->validate($rule);

        if (!$check) {
            $this->error($file->getError());
        }

        $appId = input('param.app_id');
        $version = input('param.version');
        $fileName = $file->getInfo('name');

        //不传值时，系统生成文件名，格式为YYYYmmdd/xxx.....xxxx.ext
        $saveName = $appId . DIRECTORY_SEPARATOR . $version . DIRECTORY_SEPARATOR . $fileName; //文件命名
        $info = $file->move($path, $saveName);
        //$saveName = $info->getSaveName();
        $fileUrl = DIRECTORY_SEPARATOR . 'upload'. DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . $saveName;

        $fileSize = $info->getSize();

        //原始上传文件名
        $fileName = $_FILES['file']['name'];

        //存入数据库
        $data = [
            'file_url' => $fileUrl,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'create_time' => date_time()
        ];
        $FileModel = new FileModel();
        $fileId = $FileModel->insertGetId($data);

        $data['id'] = $fileId;
        $data['ext'] = $info->getExtension(); //文件后缀

        $this->success('文件上传成功', false, $data);
    }
}
