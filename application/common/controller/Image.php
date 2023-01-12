<?php
namespace app\common\controller;

use app\common\model\FileModel;
use think\facade\Env;
use app\common\model\ImageModel;

/**
 * 图片上传组件
 * 使用方法，图片控制器中，use \app\common\controller\Image,
 * 即会继承这些方法
 */
trait Image
{
    public function upload()
    {
        
        $tmpFile = request()->file('Filedata');
        if (empty($tmpFile)) $tmpFile = request()->file('file');
        if (empty($tmpFile)) {
            //$this->error('请选择上传文件');
            $this->result(null, 0, '请选择上传文件', 'json');
        }

        //图片规定尺寸
        $imgWidth = request()->param('width/d', 0);
        $imgHeight = request()->param('height/d', 0);

        //缩略图尺寸
        $tbWidth = request()->param('thumbWidth/d', 0);
        $tbHeight = request()->param('thumbHeight/d', 0);

        $path = Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . 'upload';

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
            $this->error($check);
        }

        list($width, $height, $type) = getimagesize($tmpFile->getRealPath()); //获得图片宽高类型
        if ($imgWidth > 0 && $imgHeight > 0) {
            if (!($width >= $imgWidth-10 && $width <= $imgWidth+10 && $height >= $imgHeight-10 && $height <= $imgHeight+10)) {
                $this->error('图片尺寸不符合要求:'.$imgWidth.'*'.$imgHeight);
            }
        }

        //不能信任前端传进来的文件名, thinkphp默认使表单里的filename后缀
        $file = $tmpFile->validate(['ext' => 'jpg,gif,png,jpeg,bmp,ico,webp'])->move($path);
        if (!$file) {
            // 上传失败获取错误信息
            $this->error($tmpFile->getError());
        }

        $saveName = $file->getSaveName();
        $fileName = $file->getInfo()['name'];
        $fileSize = $file->getSize();
        $imgUrl = $path . DIRECTORY_SEPARATOR . $saveName;

        //图片缩放处理
        $image = \think\Image::open($file);
        $quality = get_config('image_upload_quality', 80); //获取图片清晰度设置，默认是80
        $extension = image_type_to_extension($type, false); //png格式时，quality不影响值；jpg|jpeg有效果
        if ($imgWidth > 0 && $imgHeight > 0) {
            //缩放至指定的宽高
            $image->thumb($imgWidth, $imgHeight, \think\Image::THUMB_FIXED);//固定尺寸缩放
            $image->save($imgUrl, $extension, $quality, true);
        }

        //缩略图
        if ($tbWidth > 0 && $tbHeight > 0) {
            // $image = \think\Image::open($file);

            $tbImgUrl = $file->getPath() . DIRECTORY_SEPARATOR . 'tb_' . $file->getFilename();

            //缩放至指定的宽高
            $image->thumb($tbWidth, $tbHeight, \think\Image::THUMB_FIXED);//固定尺寸缩放

            $image->save($tbImgUrl, $extension, $quality, true);

            $data = [
                'file_url' => DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . dirname($saveName) . DIRECTORY_SEPARATOR . $file->getFilename(),
                'file_path' => Env::get('root_path') . 'public',
                'size' => $fileSize,
                'ext' => strtolower($file->getExtension()),
                'name' => $file->getFilename(),
                'real_name' => $fileName,
                'thumb_image_url' => DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . dirname($saveName) . DIRECTORY_SEPARATOR . 'tb_' . $file->getFilename(),
                'remark' => input('post.remark'),
                'create_by' => $this->uid,
                'create_time' => date_time(),
            ];
        } else {
            $data = [
                'file_url' => DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . dirname($saveName) . DIRECTORY_SEPARATOR . $file->getFilename(),
                'file_path' => Env::get('root_path') . 'public',
                'size' => $fileSize,
                'ext' => strtolower($file->getExtension()),
                'name' => $file->getFilename(),
                'real_name' => $fileName,
                'thumb_image_url' => DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . dirname($saveName) . DIRECTORY_SEPARATOR . $file->getFilename(),
                'remark' => input('post.remark'),
                'create_by' => $this->uid,
                'create_time' => date_time(),
            ];
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

        $FileModel = new FileModel();
        $imageId = $FileModel->insertGetId($data);

        $data['id'] = $imageId;

        //$this->success('图片上传成功',null, $data);
        $this->result($data, 1, '图片上传成功', 'json');
    }

}