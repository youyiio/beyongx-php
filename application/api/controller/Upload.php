<?php 
namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\FileModel;
use app\common\model\ImageModel;
use app\common\model\UserModel;
use think\facade\Env;

class Upload extends Base
{
    use \app\common\controller\Image;

    //图片上传
    public function image()
    {
        $isfile = $_FILES;
        if ($isfile['file']['tmp_name'] == '') {
            return ajax_return(ResultCode::ACTION_FAILED, '请选择上传文件');
        }

        $tmpFile = request()->file('file');
        //图片规定尺寸
        $imgWidth = request()->param('width/d', 0);
        $imgHeight = request()->param('height/d', 0);
        //缩略图尺寸
        $tbWidth = request()->param('thumbWidth/d', 0);
        $tbHeight = request()->param('thumbHeight/d', 0);
        //备注
        $remark = request()->param('remark/s', 0);

        //表单验证
        $check = $this->validate(
            ['file' => $tmpFile],
            ['file' => 'require|image|fileSize:4097152'],
            [
                'file.require' => '请上传图片',
                'file.image' => '不是图片文件',
                'file.fileSize' => '图片太大了',
            ]
        );
        if ($check !== true) {
            return ajax_return(ResultCode::E_PARAM_VALIDATE_ERROR, '参数验证失败！');
        }

        list($width, $height, $type) = getimagesize($tmpFile->getRealPath()); //获得图片宽高类型
        if ($imgWidth > 0 && $imgHeight > 0) {
            if (!($width >= $imgWidth - 10 && $width <= $imgWidth + 10 && $height >= $imgHeight - 10 && $height <= $imgHeight + 10)) {
                return ajax_return(ResultCode::E_PARAM_VALIDATE_ERROR, "图片尺寸不符合要求,原图:宽:$width*高:$height");
            }
        }

        //保存目录
        $filePath = Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . 'upload';
        //文件验证&文件move操作
        $file = $tmpFile->validate(['ext' => 'jpg,gif,png,jpeg,bmp,ico,webp'])->move($filePath);
        if (!$file) {
            return ajax_return(ResultCode::E_PARAM_VALIDATE_ERROR, '参数验证失败！',$tmpFile->getError());
        }

        $saveName = $file->getSaveName();
        $imgUrl = $filePath . DIRECTORY_SEPARATOR . $saveName;

        //图片缩放处理
        $image = \think\Image::open($file);
        $quality = get_config('image_upload_quality', 80); //获取图片清晰度设置，默认是80
        $extension = image_type_to_extension($type, false); //png格式时，quality不影响值；jpg|jpeg有效果
        if ($imgWidth > 0 && $imgHeight > 0) {
            //缩放至指定的宽高
            $image->thumb($imgWidth, $imgHeight, \think\Image::THUMB_FIXED); //固定尺寸缩放
            $image->save($imgUrl, $extension, $quality, true);
        }

        //插入数据库
        $data = [
            'image_name' => $file->getinfo()['name'],
            'thumb_image_url' => '',
            'image_url' => DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . $saveName,
            'image_size' => $file->getSize(),
            'remark' => $remark,
            'create_time' => date_time(),
        ];

        //缩略图
        if ($tbWidth > 0 && $tbHeight > 0) {
            $tbImgUrl = $file->getPath() . DIRECTORY_SEPARATOR . 'tb_' . $file->getFilename();
            //缩放至指定的宽高
            $image->thumb($tbWidth, $tbHeight, \think\Image::THUMB_FIXED); //固定尺寸缩放
            $image->save($tbImgUrl, $extension, $quality, true);
            $data['thumb_image_url'] =  DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . dirname($saveName) . DIRECTORY_SEPARATOR . 'tb_' . $file->getFilename();
            $data['thumb_image_size'] = $file->getSize();
        } 

        if (get_config('oss_switch') === 'true') {
            if (!class_exists('\think\oss\OSSContext')) {
                $this->error('您启用了OSS存储，却未安装 think-oss 组件，运行 > composer youyiio/think-oss 进行安装！');
            }

            $vendor = get_config('oss_vendor');
            $m = new \think\oss\OSSContext($vendor);
            $ossImgUrl = $m->doUpload($file->getSaveName(), 'cms');
            $data['oss_image_url'] = $ossImgUrl;
        }

        $ImageModel = new ImageModel();
        $imageId = $ImageModel->insertGetId($data);
        
        //返回数据
        $fields = 'id,image_name,thumb_image_url,image_url,oss_image_url,thumb_image_size,image_size,remark,create_time';
        $return = $ImageModel->where('id', '=', $imageId)->field($fields)->find()->toArray();
     
        $return['fullImageUrl'] = $ImageModel->getFullImageUrlAttr('', $return);
        $return['fullThumbImageUrl'] = $ImageModel->getFullThumbImageUrlAttr('',$return);
        $returnData = parse_fields($return, 1);
     
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功', $returnData);
    }

    //文件上传
    public function file()
    {
        $isfile = $_FILES;
        if ($isfile['file']['tmp_name'] == '') {
            return ajax_return(ResultCode::ACTION_FAILED, '请选择上传文件');
        }

        $rule = [
            'size' => 1024 * 1024 * 200, //200M
        ];

        //通用文件后缀，加强安全;
        $common_file_exts = 'zip,rar,doc,docx,xls,xlsx,ppt,pptx,ppt,pptx,pdf,txt,exe,bat,sh,apk,ipa';
        $exts = request()->param('exts', ''); //文件格式，中间用,分隔
        $remark = request()->param('remark/s', ''); //文件格式，中间用,分隔
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
        $tmpFile = request()->file('file');
        $file = $tmpFile->validate($rule)->move($path);;
        if (!$file) {
            $this->result(null, 0, $tmpFile->getError(), 'json');
        }

        $user = $this->user_info;
        $userInfo = UserModel::get($user->uid);
        $saveName = $file->getSaveName(); //实际包含日期+名字：如20180724/erwrwiej...dfd.ext
        $fileUrl = DIRECTORY_SEPARATOR . 'upload'. DIRECTORY_SEPARATOR . 'file' . DIRECTORY_SEPARATOR . $saveName;
        $data = [
            'file_url' => DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . $saveName,
            'file_path' => Env::get('root_path') . 'public',
            'file_name' => $file->getinfo()['name'],
            'file_size' => $file->getSize(),
            'remark' => $remark,
            'create_time' => date_time(),
        ];

        $FileModel = new FileModel();
        $fileId = $FileModel->insertGetId($data);

        $fields = 'id,file_url,file_path,file_name,file_size,remark,file_path,create_time';
        $return = $FileModel->where('id', '=', $fileId)->field($fields)->find()->toArray();
     
        $return['fullFileUrl'] = $FileModel->getFullFileUrlAttr('', $return);
        unset($return['file_path']);
        $returnData = parse_fields($return, 1);
     
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功', $returnData);
    }
}