<?php

/**
 * 文件上传类
 * by kerring for weixin
 */

define('BASEPATH','kerring');

new Upload;

class Upload {

    /**
     * 变量声明
     */
    private $conf, $upload;

    /**
     * 构造函数
     */
    public function __construct($args = array()) {
        $this->conf = array(
            'file_secret' => 'a1d33d0dfe',
            'isFigureBed' => true,
            'figureBed'   => array(
                'domain'  => '',
                'space'   => '',
                'user'    => '',
                'password'=> ''
            ),
            'maxSize' => '4096000',
            'savePath' => '/demo/',
            'allowExt'=> array(
                'image' => 'jpg,png,gif,bmp,jpeg',
                'media' => 'mp4,swf,flv'
            ),
            'eventText' => array(
                0 => '上传成功',
                1 => '上传参数错误',
                2 => '您需要上传文件类型为jpg的图片',
                3 => '文件上传不完整或大小超出限制范围',
                4 => '无法移动文件到指定目录',
                5 => '不可预料的错误',
                6 => '未设置上传服务器'
            )
        );
        include('upyun.php');
        $this->upyun = new UpYun($this->conf['figureBed']['space'], $this->conf['figureBed']['user'], $this->conf['figureBed']['password']);
    
        $this->uploadPic();
    }

    /**
     * 单文件上传(仅限目前图床上传环境)
     * @param string $fileinfo 上传文件信息
     * @param string $prefix 文件保存名称前缀
     * @return array(
     *  'code' 上传结果代码
     *  'text' 上传结果描述
     *  'type' 原始文件类型
     *  'size' 实际文件大小
     *  'file' 上传文件
     *  'upyun'图床信息
     * )
     */
    public function uploadPic(){
        $fid = 'order_img'; $dat = array(); $file = '';
        $data = array('code' => 1);

        if(!empty($fid) && isset($_FILES[$fid]) && is_array($_FILES[$fid])){
            $copy = $_FILES[$fid];

            (!$copy['size'] || $copy['size'] > $this->conf['maxSize']) && $data['code'] = 3;

            $type = $this->fileType($copy['name'], $copy['tmp_name']);
            !$type && $data['code'] = 2;

            if($copy['error'] === 0){
                $file = $this->conf['savePath'].uniqid().'.'.$type;

                if( $this->conf['isFigureBed'] == true ){
                    $res = $this->upyun->writeFile($file, fopen($copy['tmp_name'],'rb'), true);
                }else{
                    $data['code'] = 6;
                }

                if( $res != false ) {
                    $data['code'] = 0;
                    $data['type'] = $type;
                    $data['size'] = $copy['size'];
                    $data['file'] = $this->conf['figureBed']['domain'].$file;
                    $data['upyun'] = $res;
                }else{
                    $data['code'] = 4;
                }
            }
        }
        $data['text'] = $this->conf['eventText'][$data['code']];
        echo json_encode($data);
        exit;
    }

    /**
     * 检查文件类型是否允许上传
     */
    private function fileType($name, $file = '') {
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if($ext && stripos($this->conf['allowExt']['image'], $ext)) {
            return @getimagesize($file) ? $ext : '';
        }
        return $ext;
    }

}

?>
