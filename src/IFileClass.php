<?php


namespace MyFileLoader;

interface IFileClass
{
	public function __construct($files_directory);
	public function saveImage($imageName, $size = 7);
	public function saveDocument($documentName, $size = 17);
	public function deleteFile($fileName);
	public function getRealName($filename);
	public function setFileSizeLimit($limit = 5);
}