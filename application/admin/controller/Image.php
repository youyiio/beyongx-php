<?php
namespace app\admin\controller;

use app\common\model\FileModel;
use app\common\model\ImageModel;
use app\common\model\UserModel;
use think\facade\Env;

/**
 * 图片控制器
 */
class Image extends Base
{
    use \app\common\controller\Image; //使用trait

    /**
     * 图片上传,格式不正确时，提示裁前，根据设置，截取图片
     * upcrop
     * */
    public function upcrop()
    {
        $imageId = request()->param('imageId/d', 0);
        
        //id不存在时，图片上传
        if (empty($imageId)) {
            $tmpFile = request()->file('file');
            if (empty($tmpFile)) {
                $this->result(null, 0, '请选择上传文件', 'json');
            }

            //图片规定尺寸
            $imgWidth = request()->param('width/d', 0);
            $imgHeight = request()->param('height/d', 0);

            //缩略图尺寸
            $tbWidth = request()->param('thumbWidth/d', 0);
            $tbHeight = request()->param('thumbHeight/d', 0);

            $path = Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . 'upload';

            //表单验证
            $check = $this->validate(
                ['file' => $tmpFile],
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

            //文件验证&文件move操作
            $file = $tmpFile->validate(['ext' => 'jpg,gif,png,jpeg,bmp,ico,webp'])->move($path);
            if (!$file) {
                // 上传失败获取错误信息
                $this->error($tmpFile->getError());
            }
            list($width, $height, $type) = getimagesize($file->getRealPath()); //获得图片宽高类型
            
            $saveName = $file->getSaveName();

            $data = [
                'file_url' => DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . dirname($saveName) . DIRECTORY_SEPARATOR . $file->getFilename(),
                'file_path' => Env::get('root_path') . 'public',
                'size' => $file->getSize(),
                'ext' => strtolower($file->getExtension()),
                'name' => $file->getFilename(),
                'real_name' => $file->getinfo()['name'],
                'thumb_image_url' => DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . dirname($saveName) . DIRECTORY_SEPARATOR . $file->getFilename(),
                'remark' => input('post.remark'),
                'create_by' => $this->uid,
                'create_time' => date_time(),
            ];
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
            if ($imgWidth > 0 && $imgHeight > 0) {
                if (!($width >= $imgWidth-10 && $width <= $imgWidth+10 && $height >= $imgHeight-10 && $height <= $imgHeight+10)) {
                    $this->result($data, 1, 'image_need_crop', 'json');
                }
            }
            $this->result($data, 1, '图片上传成功', 'json');
        }


        //图片裁剪
        $FileModel = FileModel::get($imageId);
        if (!$FileModel) {
            $this->error('图片不存在');
        }

        $thumbWidth = request()->param('thumbWidth/d', 0); //截取后缩略图的宽
        $thumbHeight = request()->param('thumbHeight/d', 0); //截取后缩略图的高
        if (!$this->request->isAjax()) {
            $this->assign('image', $FileModel);
            $this->assign('thumbWidth', $thumbWidth);
            $this->assign('thumbHeight', $thumbHeight);

            return $this->fetch('resource/crop');
        }

        //获取图片截取的尺寸参数
        $degrees = request()->param('rotate/d', 0); //旋转的度数
        $scale = request()->param('scale/d', 0); //缩放

        $x = request()->param('x/d', 0); //源图的x点
        $y = request()->param('y/d', 0); //源图的y点
        $width = request()->param('width/d', 0); //源图截取的宽
        $height = request()->param('height/d', 0); //源图截取的高


        $path = Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR;
        $realPath = $path . $FileModel->file_url;
        $file = new \SplFileInfo($realPath);
        $srcImage = \think\Image::open($file);
        if (!$srcImage) {
            $this->error('读取图片文件失败!');
        }

        //图片旋转
        $srcImage->rotate($degrees);

        //图片裁剪，并保存为数据库的缩略图
        $srcImage->crop($width, $height, $x, $y);

        $imgUrl = $file->getPath() . DIRECTORY_SEPARATOR . 'tb_crop_' . $file->getFilename();
        $quality = get_config('image_upload_quality', 80); //获取图片清晰度设置，默认是80
        list(, , $type) = getimagesize($file->getRealPath());
        $extension = image_type_to_extension($type, 0);
        $srcImage->save($imgUrl, $extension, $quality, true);

        //图片压缩至目标大小，保存为数据库中的图片
        $srcImage->thumb($thumbWidth, $thumbHeight, \think\Image::THUMB_FIXED);
        $tbImgUrl = $file->getPath() . DIRECTORY_SEPARATOR . 'crop_' . $file->getFilename();
        $srcImage->save($tbImgUrl, $extension, $quality, true);

        $FileModel->file_url = DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.date('Ymd').DIRECTORY_SEPARATOR.'crop_'.$file->getFilename();
        $FileModel->thumb_image_url = DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.date('Ymd').DIRECTORY_SEPARATOR.'tb_crop_'.$file->getFilename();
        $FileModel->save();

        $data = $FileModel;
        $this->result($data, 1, '图片裁剪成功', 'json');
    }

}