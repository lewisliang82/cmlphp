<?php
/* * *********************************************************
 * [cml] (C)2012 - 3000 cml http://cmlphp.51beautylife.com
 * @Author  linhecheng<linhechengbush@live.com>
 * @Date: 14-2-211 下午2:23
 * @version  2.5
 * cml框架 Ftp扩展类
 * *********************************************************** */
namespace Cml\Vendor;


class Ftp
{

    private $linkid;
    private $timeout = 50;

    /**
     * FTP-ftp连接
     *
     * @param array  $config 配置
     *
     * @return bool
     */
    public function connect(array $config)
    {
        $port = (isset($config['port'])) ? (int) $config['port'] : 21; //端口号
        $this->linkid = ftp_connect($config['service'], $port);
        if (!$this->linkid) return false;
        @ftp_set_option($this->linkid, FTP_TIMEOUT_SEC, $this->timeout);
        if (@!ftp_login($this->linkid, $config['username'], $config['password'])) {
            return false;
        }
        return true;
    }

    /**
     * FTP-文件上传
     *
     * @param string  $local_file 本地文件
     * @param string  $ftp_file Ftp文件
     *
     * @return bool
     */
    public function upload($local_file, $ftp_file)
    {
        if (empty($local_file) || empty($ftp_file)) return false;
        $ftppath = dirname($ftp_file);
        if (!empty($ftppath)) { //创建目录
            $this->makeDir($ftppath);
            @ftp_chdir($this->linkid, $ftppath);
            $ftp_file = basename($ftp_file);
        }
        $ret = ftp_nb_put($this->linkid, $ftp_file, $local_file, FTP_BINARY);
        while ($ret == FTP_MOREDATA) {
            $ret = ftp_nb_continue($this->linkid);
        }
        if ($ret != FTP_FINISHED) return false;
        return true;
    }

    /**
     * FTP-文件下载
     *
     * @param string  $local_file 本地文件
     * @param string  $ftp_file Ftp文件
     *
     * @return bool
     */
    public function download($local_file, $ftp_file)
    {
        if (empty($local_file) || empty($ftp_file)) return false;
        $ret = ftp_nb_get($this->linkid, $local_file, $ftp_file, FTP_BINARY);
        while ($ret == FTP_MOREDATA) {
            $ret = ftp_nb_continue ($this->linkid);
        }
        if ($ret != FTP_FINISHED) return false;
        return true;
    }

    /**
     * FTP-创建目录
     *
     * @param string  $path 路径地址
     *
     * @return bool
     */
    public function makeDir($path)
    {
        if (empty($path)) return false;
        $dir = explode("/", $path);
        $path = ftp_pwd($this->linkid) . '/';
        $ret = true;
        for ($i=0; $i<count($dir); $i++) {
            $path = $path . $dir[$i] . '/';
            if (!@ftp_chdir($this->linkid, $path)) {
                if (!@ftp_mkdir($this->linkid, $dir[$i])) {
                    $ret = false;
                    break;
                }
            }
            @ftp_chdir($this->linkid, $path);
        } 
        if (!$ret) return false;
        return true;
    }

    /**
     * FTP-删除文件目录
     *
     * @param string  $dir 删除文件目录
     *
     * @return bool
     */
    public function delDir($dir)
    {
        $dir = $this->checkpath($dir);
        if (@!ftp_rmdir($this->linkid, $dir)) {
            $this->close();
            return false;
        }
        $this->close();
        return true;
    }

    /**
     * FTP-删除文件
     *
     * @param string  $file 删除文件
     *
     * @return bool
     */
    public function delFile($file)
    {
        $file = $this->checkpath($file);
        if (@!ftp_delete($this->linkid, $file)) {
            $this->close();
            return false;
        }
        $this->close();
        return true;
    }

    /**
     * FTP-FTP上的文件列表
     *
     * @param string $path 路径
     *
     * @return bool
     */
    public function nlist($path = '/')
    {
        return ftp_nlist($this->linkid, $path);
    }

    /**
     * FTP-改变文件权限值
     *
     * @param string $file 文件
     * @param int $val  值
     *
     * @return bool
     */
    public function ftpChmod($file, $val = 0777)
    {
        return @ftp_chmod($this->linkid, $val, $file);
    }

    /**
     * FTP-返回文件大小
     *
     * @param string $file 文件
     *
     * @return bool
     */
    public function fileSize($file)
    {
        return ftp_size($this->linkid, $file);
    }

    /**
     * FTP-文件修改时间
     *
     * @param string $file 文件
     *
     * @return bool
     */
    public function mdtm($file)
    {
        return ftp_mdtm($this->linkid, $file);
    }

    /**
     * FTP-更改ftp上的文件名称
     *
     * @param string $oldname 旧文件
     * @param string $newname 新文件名称
     *
     * @return bool
     */
    public function changename($oldname, $newname)
    {
        return ftp_rename ($this->linkid, $oldname, $newname);
    }

    /**
     * FTP-关闭链接
     *
     * @return bool
     */
    public function close()
    {
        ftp_close($this->linkid);
    }

    /**
     * FTP-检测path
     *
     * @return string $path
     */
    private function checkpath($path)
    {
        return (isset($path)) ? trim(str_replace('\\', '/', $path), '/') . '/' : '/';
    }

}