<?php
namespace app\common\controller;

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
        $file = request()->file('Filedata');
        if (empty($file)) {
            $this->error('请选择上传文件');
        }

        //图片规定尺寸
        $imgWidth = request()->param('width/d',0);
        $imgHeight = request()->param('height/d',0);

        //缩略图尺寸
        $tbWidth = request()->param('thumbWidth/d',0);
        $tbHeight = request()->param('thumbHeight/d',0);

        $path = Env::get('root_path').'public'.DIRECTORY_SEPARATOR.'upload';

        $check = $this->validate(
            ['file' => $file],
            ['file'=>'require|image|fileSize:4097152'],
            [
                'file.require' => '请上传图片',
                'file.image' => '不是图片文件',
                'file.fileSize' => '图片太大了',
            ]
        );
        if ($check !== true) {
            $this->error($check);
        }

        list($width, $height, $type) = getimagesize($file->getRealPath()); //获得图片宽高类型
        if ($imgWidth > 0 && $imgHeight > 0) {
            if (!($width >= $imgWidth-10 && $width <= $imgWidth+10 && $height >= $imgHeight-10 && $height <= $imgHeight+10)) {
                $this->error('图片尺寸不符合要求:'.$imgWidth.'*'.$imgHeight);
            }
        }

        $info = $file->move($path);

        if (!$info) {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }

        $saveName = $info->getSaveName();
        $imgUrl = $path.DIRECTORY_SEPARATOR.$saveName;

        //图片缩放处理
        $image = \think\Image::open($info);
        if ($imgWidth > 0 && $imgHeight > 0) {
            //缩放至指定的宽高
            $image->thumb($imgWidth,$imgHeight,6);//固定尺寸缩放
            $image->save($imgUrl);
        }

        //缩略图
        if ($tbWidth > 0 && $tbHeight > 0) {
            // $image = \think\Image::open($info);

            $tbImgUrl = $info->getPath().DIRECTORY_SEPARATOR.'tb_'.$info->getFilename();

            //缩放至指定的宽高
            $image->thumb($tbWidth,$tbHeight,6);//固定尺寸缩放

            $image->save($tbImgUrl);

            $data = [
                'thumb_image_url' => DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.dirname($saveName).DIRECTORY_SEPARATOR.'tb_'.$info->getFilename(),
                'image_url' => DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.dirname($saveName).DIRECTORY_SEPARATOR.$info->getFilename(),
                'create_time' => date_time(),
                'remark' => input('post.remark'),
            ];
        } else {
            $data = [
                'thumb_image_url' => DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.dirname($saveName).DIRECTORY_SEPARATOR.$info->getFilename(),
                'image_url' => DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.dirname($saveName).DIRECTORY_SEPARATOR.$info->getFilename(),
                'create_time' => date_time(),
                'remark' => input('post.remark'),
            ];
        }

        if (get_config('oss_switch') === 'true') {
            if (!class_exists('\think\oss\OSSContext')) {
                $this->error('您启用了OSS存储，却未安装 think-oss 组件，运行 > composer youyiio/think-oss 进行安装！');
            }

            $vendor = get_config('oss_vendor');
            $m = new \think\oss\OSSContext($vendor);
            $ossImgUrl = $m->doUpload($info->getSaveName(), 'cms');
            $data['oss_image_url'] = $ossImgUrl;
        }

        $ImageModel = new ImageModel();
        $imageId = $ImageModel->insertGetId($data);

        $data['image_id'] = $imageId;

        //$this->success('图片上传成功',null, $data);
        $this->result($data, 1, '图片上传成功', 'json');
    }

}