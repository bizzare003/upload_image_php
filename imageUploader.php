<?php
namespace MyApp;
class Imageloader {
	private $_imageType;
	private $_imageFileName;
	public function uploader() {
		try {
			$this->_validMessage();
			$ext = $this->_validType();
			$savePath = $this->_save($ext);
			$this->_validData($savePath);

			$_SESSION['success'] = 'Success!';
		} catch (\Exception $e) {
			$_SESSION['errors'] = $e->getMessage();
		}
		header('Location: http://' . $_SERVER['HTTP_HOST']);
		exit;
	}
	private function _validData($savePath) {
		$imageSize = getimagesize($savePath);
		$width = $imageSize[0];
		$height = $imageSize[1];
		if($width > THUMBWIDTH) {
			$this->_thumbnails($savePath, $width, $height);
		}
	}
	private function _thumbnails($savePath, $width, $height) {
		switch ($this->_imageType) {
			case IMAGETYPE_GIF:
			$imageSrc = imagecreatefromgif($savePath);
			break;
			case IMAGETYPE_JPEG:
			$imageSrc = imagecreatefromjpeg($savePath);
			break;
			case IMAGETYPE_PNG:
			$imageSrc = imagecreatefrompng($savePath);
			break;
		}
		$thumbHeight = round($height * THUMBWIDTH / $width);
		$thumbImage = imagecreatetruecolor(THUMBWIDTH, $thumbHeight);
		imagecopyresampled($thumbImage, $imageSrc, 0,0,0,0, THUMBWIDTH, $thumbHeight, $width, $height);
		switch ($this->_imageType) {
			case IMAGETYPE_GIF:
				imagegif($thumbImage,THUMBDIR . '/' . $this->_imageFileName);
				break;
			case IMAGETYPE_JPEG:
				imagejpeg($thumbImage,THUMBDIR . '/' . $this->_imageFileName);
				break;
			case IMAGETYPE_PNG:
				imagepng($thumbImage,THUMBDIR . '/' . $this->_imageFileName);
				break;
		}
		imagedestroy($thumbImage);
	}
	private function _save($ext) {
		$this->_imageFileName = sprintf('%s_%s.%s', time(), sha1(uniqid(mt_rand(), true)), $ext);
		$savePath = IMGDIR . '/' . $this->_imageFileName;
		$res = move_uploaded_file($_FILES['image']['tmp_name'], $savePath);
		if($res === false) {
			throw new \Exception('Could not image!');
		}
		return $savePath;
	}
	private function _validType() {
		$this->_imageType = exif_imagetype($_FILES['image']['tmp_name']);
		switch ($this->_imageType) {
			case IMAGETYPE_GIF:
				return 'gif';
			case IMAGETYPE_JPEG:
				return 'jpg';
			case IMAGETYPE_PNG:
				return 'png';
			default:
				throw new \Exception('GIF/JPG/PNG ONLY!');
		}
	}

	private function _validMessage() {
		if(!isset($_FILES['image']) || !isset($_FILES['image']['error'])) {
			throw new \Exception('Upload Err!');
		}

		switch ($_FILES['image']['error']) {
			case UPLOAD_ERR_OK:
				return true;
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				throw new \Exception('File too large!');
			default:
				throw new \Exception('Err: ' . $_FILES['image']['error']);
		}
	}
	public function getImages() {
		$images = [];
		$files = [];
		$imageDir = opendir(IMGDIR);
		while(false !== ($file = readdir($imageDir))) {
			if($file === '.' || $file === '..') {continue;}
			$files[] = $file;
			if(file_exists(THUMBDIR . '/' . $file)) {
				$images[] = basename(THUMBDIR) . '/' . $file;
			} else {
				$images[] = basename(IMGDIR) . '/' . $file;
			}
		}
		array_multisort($files, SORT_DESC, $images);
		return $images;
	}
	public function getMessage() {
		$success = null;
		$errors = null;
		if(isset($_SESSION['success'])) {
			$success = $_SESSION['success'];
			unset($_SESSION['success']);
		}
		if(isset($_SESSION['errors'])) {
			$errors = $_SESSION['errors'];
			unset($_SESSION['errors']);
		}
		return [$success, $errors];
	}
}