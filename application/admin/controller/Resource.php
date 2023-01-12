<?php
/**
 * Created by VSCode.
 * User: Administrator
 * Date: 2018/5/9
 * Time: 11:29
 */

namespace app\admin\controller;

use app\common\model\FileModel;
use app\common\model\ImageModel;
use think\facade\Env;

class Resource extends Base
{
    //文档列表
    public function documents()
    {
        
        $map[] = ['ext', 'in', ['doc','docx','ppt','pptx','txt','avi','pdf','mp3','zip','mp4','xlsx']];
        $key = input('param.key');
        if ($key) {
            $map[] = ['remark', 'like', "%$key%"];
        }

        $FileModel = new FileModel();
        $files = $FileModel->where($map)->paginate(21);
        $pages = $files->render();
      
        $this->assign('files', $files);
        $this->assign('pages', $pages);

        return $this->fetch('documents');
    }

    public function uploadDocument()
    {
        if (request()->isAjax()) {
            $fileId = input('param.fileId');
            $remark = input('param.remark');

            if ($fileId && $remark) {
                $FileModel = new FileModel();
                $result = $FileModel->save(['remark' => $remark], ['id' => $fileId]);

                if ($result) {
                    $this->success('上传成功','documents');
                } else {
                    $this->error('上传失败');
                }
            }
        }

        return $this->fetch('uploadDocument');
    }

    public function deleteDocument($fileId = 0)
    {
        $FileModel = new FileModel();
        $file = $FileModel->where('id', $fileId)->find();
        if (empty($file)) {
            $this->error('文件不存在');
        }

        //删除文件
        $filePath = $file['file_path'].$file['file_url'];
        is_file($filePath) && unlink($filePath);

        //删除数据
        $FileModel = new FileModel();
        $res = $FileModel->where('id', $fileId)->delete();

        if (!$res) {
            $this->error('删除失败');
        }

        $this->success('成功删除');

    }

    //图片列表
    public function images()
    {
        $map[] = ['ext', 'in', ['jpg','gif','png','jpeg','webp','bmp']];
        $key = input('param.key');
        if ($key) {
            $map[] = ['remark', 'like', "%$key%"];
        }

        $FileModel = new FileModel();
        $imageList =  $FileModel->where($map)->paginate(21);

        $this->assign('imageList', $imageList);
        $this->assign('pages', $imageList->render());
        return view();
    }

    public function uploadImage()
    {
        if (request()->isAjax()) {
            $imageId = input('param.imageId');
            $remark = input('param.remark');

            if ($imageId) {
                $FileModel = new FileModel();
                $result = $FileModel->save(['remark' =>$remark], ['id' =>$imageId]);

                if ($result) {
                    $this->success('上传成功','images');
                } else {
                    $this->error('上传失败');
                }
            }
        }


        return $this->fetch('uploadImage');
    }

    public function deleteImage($imageId = 0)
    {
        $FileModel = new FileModel();
        $image = $FileModel->where('id', $imageId)->find();
        if (empty($image)) {
            $this->error('图片不存在');
        }
        //删除图片
        $imageUrl = Env::get('root_path').'public'.$image['file_url'];
        is_file($imageUrl) && unlink($imageUrl);
        $tbImageUrl = Env::get('root_path').'public'.$image['thumb_image_url'];
        is_file($tbImageUrl) && unlink($tbImageUrl);

        //删除数据
        $res = $FileModel->where('id', $imageId)->delete();

        if (!$res) {
            $this->error('删除失败');
        }

        $this->success('成功删除');
    }
}