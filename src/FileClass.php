<?php
/**
 * Created by PhpStorm.
 * User: kekst
 * Date: 12.06.2018
 * Time: 16:23
 */

namespace MyFileLoader;


class FileClass implements IFileClass
{

    private $filename;
    private $file_extension;
    private $max_file_size; //10 МБ
    private $files_directory;
    private $realName = '';

    public function __construct($files_directory)
    {
        $this->files_directory = $files_directory;
    }

    private function init($filename = 'file', $filesize = 10)
    {
        $this->filename = $filename;
        $this->max_file_size = 1024*1024*$filesize;
    }

    private function isLoaded()
    {
        //Checks
        if (!isset($_FILES[$this->filename])) throw new \Exception('Файл не передан');
        if ($_FILES[$this->filename]["name"] == '') throw new \Exception('Отсутствует имя файла');
        if ($_FILES[$this->filename]["size"] > $this->max_file_size) throw new \Exception('Размер файла превышаем максимально допустимый размер');

        //Ok
        $this->file_extension = strtolower(pathinfo($_FILES[$this->filename]['name'], PATHINFO_EXTENSION));
        $this->realName = $_FILES[$this->filename]["name"];
        return true;
    }

    private function checkType($arr)
    {
        if (in_array($this->file_extension, $arr)) {
            return true;
        }
        return false;
    }

    private function SaveFile($name)
    {
        if (move_uploaded_file($_FILES[$this->filename]["tmp_name"], $this->files_directory.$name)) return true;
        return false;
    }

    private function getNewName()
    {
        $name = md5(date('ymd-His-').rand(0, 9999)).'.'.$this->file_extension;
        return $name;
    }

    private function SaveResizedImage($filename, $width = 200, $height = 0)
    {
        list($w, $h) = getimagesize($_FILES[$this->filename]['tmp_name']);

        /* calculate new image size with ratio */

        $ratio = $w/$width;

        //$h = ceil($height / $ratio);
        //$x = ($w - $width / $ratio) / 2;
        //$w = ceil($width / $ratio);

        $pic_width = $width;
        $pic_height = ceil($h / $ratio);

        if ($pic_height > ($width*1.5)) $pic_height = $width;

        $imgString = file_get_contents($_FILES[$this->filename]['tmp_name']);
        $image = imagecreatefromstring($imgString);
        $tmp = imagecreatetruecolor($pic_width, $pic_height);

        $white = imagecolorallocate($tmp, 255, 255, 255);
        imagefill($tmp, 0, 0, $white);

        imagecopyresampled($tmp, $image,
            0, 0,
            0, 0,
            $pic_width, $pic_height,
            $w, $h);

        /* Save image */
        $path = $this->files_directory.$filename;
        switch ($_FILES[$this->filename]['type']) {
            case 'image/jpeg':
                imagejpeg($tmp, $path, 100);
                break;
            case 'image/png':
                imagepng($tmp, $path, 0);
                break;
            case 'image/gif':
                imagegif($tmp, $path);
                break;
            default:
                exit;
                break;
        }

        /* cleanup memory */
        imagedestroy($image);
        imagedestroy($tmp);

        return $path;

    }


    public function saveImage($imageName, $size = 7)
    {
        $this->init($imageName, $size);
        $this->isLoaded();

        if (!$this->checkType(array('jpg', 'jpeg', 'bmp', 'gif', 'png')))
            throw new \Exception('Не верный тип файла изображения');

        $new_name = $this->getNewName();
        $this->SaveResizedImage($new_name, 200, 0);
        $this->SaveResizedImage('s_' . $new_name, 100, 0);
        return $new_name;

    }

    public function saveDocument($documentName, $size = 17)
    {
        $this->init($documentName, $size);
        $this->isLoaded();
        if ($this->checkType(array('php', 'exe', 'js')))
            throw new \Exception('Не верный тип файла изображения');
        $new_name = $this->getNewName();
        $this->SaveFile($new_name);
        return $new_name;
    }

    public function deleteFile($fileName)
    {
        unlink($this->files_directory.'/'.$fileName);
    }

    public function getRealName($filename)
    {
        return $_FILES[$filename]["name"];
    }

    public function setFileSizeLimit($limit = 5)
    {
        $this->max_file_size = 1024*1024*$limit;
    }
}