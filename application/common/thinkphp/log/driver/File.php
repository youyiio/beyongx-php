<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2019-06-06
 * Time: 9:50
 */

namespace app\common\thinkphp\log\driver;


class File extends \think\log\driver\File
{
    /**
     * 检查日志文件大小并自动生成备份文件
     * @access protected
     * @param  string    $destination 日志文件
     * @return void
     */
    protected function checkLogSize($destination)
    {
        if (is_file($destination) && floor($this->config['file_size']) <= filesize($destination)) {
            try {
                $filename = basename($destination);
                $filename = substr($filename, 0, -4)  . '_' . date('His') . substr($filename, -4);
                rename($destination, dirname($destination) . DIRECTORY_SEPARATOR . $filename);
            } catch (\Exception $e) {
            }
        }
    }
}