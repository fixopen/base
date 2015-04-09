<?php

trait BinaryTransfer
{
    /*
multipart/form-data; boundary=---------------------------1674634213656540309701995077

-----------------------------1674634213656540309701995077
Content-Disposition: form-data; name="file"; filename="import.csv"
Content-Type: text/csv

﻿学校,学段,年级,班级,姓名,ukey_id,所属区县,发行平台用户-id,发行平台用户-账号,发行平台用户-密码
上海市大宁国际小学,小学,2012,4,聂思宏,ZHYS201401SH10003322,闸北区,21647,NSH814065,123456

-----------------------------1674634213656540309701995077--
    */
/*
<!-- The data encoding type, enctype, MUST be specified as below -->
<form enctype="multipart/form-data" action="__URL__" method="POST">
    <!-- MAX_FILE_SIZE must precede the file input field -->
    <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
    <!-- Name of input element determines name in $_FILES array -->
    Send this file: <input name="userfile" type="file" />
    <input type="submit" value="Send File" />
</form>
*/
    //enctype="multipart/form-data"
    /*
     *
$_FILES['userfile']['name']

    The original name of the file on the client machine.
$_FILES['userfile']['type']

    The mime type of the file, if the browser provided this information. An example would be "image/gif". This mime type is however not checked on the PHP side and therefore don't take its value for granted.
$_FILES['userfile']['size']

    The size, in bytes, of the uploaded file.
$_FILES['userfile']['tmp_name']

    The temporary filename of the file in which the uploaded file was stored on the server.
$_FILES['userfile']['error']

    The error code associated with this file upload.
    */

    public function upload($type, $fileContent)
    {
        //mime-type total-length file-name
        $uri = $this->getContent($type);
        //remove [scheme://host:port/path/]specPath/name.ext
        //$filename = basename($uri);
        $filename = $uri;
        $file = fopen($filename, 'wb');
        fwrite($file, $fileContent);
        fclose($file);
    }

    public function uploadSlice($type, $fileContent, $offset, $count)
    {
        //mime-type total-length file-name|file-uri start-position transfer-length
        $uri = $this->getContent($type);
        //remove [scheme://host:port/path/]specPath/name.ext
        //$filename = basename($uri);
        $filename = $uri;
        $file = fopen($filename, 'ab');
        fseek($file, $offset);
        fwrite($file, $fileContent, $count);
        fclose($file);
    }

    public function download($type)
    {
        //mime-type total-length file-name
        $uri = $this->getContent($type);
        $filename = basename($uri);
        $file = fopen($filename, 'rb');
        fseek($file, 0, SEEK_END);
        $length = ftell($file);
        $content = fread($file, $length);
        fclose($file);
        return $content;
    }

    public function downloadSlice($type, $offset, $count)
    {
        //mime-type total-length file-name|file-uri start-position transfer-length
        $uri = $this->getContent($type);
        //remove [scheme://host:port/path/]specPath/name.ext
        $filename = basename($uri);
        $file = fopen($filename, 'rb');
        fseek($file, $offset);
        $content = fread($file, $count);
        fclose($file);
        return $content;
    }

}

