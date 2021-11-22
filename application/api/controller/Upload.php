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
        $image = request()->file('image');
        if (empty($image)) {
            return ajax_return(ResultCode::E_PARAM_ERROR, '请选择上传图片');
        }

        $params = $this->request->put();
        $width = $params['width']?? 800;
        $height = $params['height']?? 100;
        $thumbWidht = $params['thumbWidht']?? 400;
        $thumbHeight = $params['thumbHeight']?? 500;

        //表单验证
        $check = $this->validate(
            ['file' => $image],
            ['file'=>'require|image|fileSize:4097152'],
            [
                'file.require' => '请上传图片',
                'file.image' => '不是图片文件',
                'file.fileSize' => '图片太大了',
            ]
        );
        if ($check !== true) {
            return ajax_return(ResultCode::E_PARAM_VALIDATE_ERROR, '参数验证失败！', $this->error($check));
        }

        //文件目录
        $filePath = Env::get('root_path') . 'public';
        $fileUrl = DIRECTORY_SEPARATOR . 'upload'. DIRECTORY_SEPARATOR . 'file';
        $path = $filePath . $fileUrl;

        //文件验证&文件move操作
        $file = $image->validate(['ext' => 'jpg,gif,png,jpeg,bmp,ico,webp'])->move($path);
        if (!$file) {
            return ajax_return(ResultCode::E_PARAM_VALIDATE_ERROR, '参数验证失败！', $this->error($image->getError()));
        }
        
        //插入数据库
        $user = $this->user_info;
        $userInfo = UserModel::get($user->uid);
        $saveName = $file->getSaveName(); //实际包含日期+名字：如20180724/erwrwiej...dfd.ext
       
        $data = [
            'file_url' => DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . $saveName,
            'file_path' => $filePath,
            'size' => $file->getSize(),
            'ext' => $file->getExtension(),
            'name' => $saveName,
            'real_name' => $file->getinfo()['name'],
            'create_time' => date_time(),
            'create_by' => $userInfo['nickname']?? '',
            'remark' => $params['remark']?? '',
        ];

        $ImageModel = new ImageModel();
        $imageId = $ImageModel->insertGetId($data);

        $fields = 'id,name,real_name,size,ext,file_url,file_path,thumb_image_url,create_by,create_time';
        $return = $ImageModel->where('id', '=', $imageId)->field($fields)->find()->toArray();
        $return['fullFileUrl'] = $return['file_path'].$return['file_url'];
        $return['fullThumbImageUrl'] = $ImageModel->getFullThumbImageUrlAttr('',$return['thumb_image_url']);
        unset($return['file_path']);
        $returnData = parse_fields($return, 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功', $returnData);
    }

    //文件上传
    public function file()
    {

    }
}