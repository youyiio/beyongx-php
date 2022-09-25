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

    /**
     * 获取主日志文件名
     * @access public
     * @return string
     */
    protected function getMasterLogFile()
    {
        if ($this->config['max_files']) {
            $files = glob($this->config['path'] . '*.log');

            try {
                if (count($files) > $this->config['max_files']) {
                    unlink($files[0]);
                }
            } catch (\Exception $e) {
            }
        }

        $cli = PHP_SAPI == 'cli' ? '_cli' : '';

        if ($this->config['single']) {
            $name = is_string($this->config['single']) ? $this->config['single'] : 'single';

            $destination = $this->config['path'] . $name . $cli . '.log';
        } else {
            if ($this->config['max_files']) {
                $filename = date('Ymd') . $cli . '.log';
            } else {
                $filename = date('Ymd') . $cli . '.log';
            }

            $destination = $this->config['path'] . $filename;
        }

        return $destination;
    }

    /**
     * 获取独立日志文件名
     * @access public
     * @param  string $path 日志目录
     * @param  string $type 日志类型
     * @return string
     */
    protected function getApartLevelFile($path, $type)
    {
        $cli = PHP_SAPI == 'cli' ? '_cli' : '';

        if ($this->config['single']) {
            $name = is_string($this->config['single']) ? $this->config['single'] : 'single';
        } elseif ($this->config['max_files']) {
            $name = date('Ymd');
        } else {
            $name = date('Ymd');
        }

        return $path . DIRECTORY_SEPARATOR . $name . '_' . $type . $cli . '.log';
    }
}