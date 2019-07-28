<?php
	namespace MyApp;

	class ImageUploader {
		private $_imageFileName;
		private $_imageFileNameThum;
		private $_imageType;

		public function upload() {
			try {
				// error check
				$this->_validateUpload();

				// image type check $extはextentionという意味の変数
				$ext = $this->_validateImageType();
				$pathData = pathinfo( IMG_DIR.'/'.$_FILES['image']['name'] );
				$this->pathDateName = $pathData["filename"];
				// save
				$savePath = $this->_save($ext);

				// Thumnails
				$this->_createThumnail($savePath);

				$_SESSION['success'] = 'Upload Done!';
			} catch (\Exception $e) {
				$_SESSION['error'] = $e->getMessage();
			}

			header('Location: http://'.$_SERVER['HTTP_HOST']);
			exit;
		}// public function upload()

		public function getResults() {
			$success = null;
			$error = null;
			if(isset($_SESSION['success'])) {
				$success = $_SESSION['success'];
				unset($_SESSION['success']);
			}
			if(isset($_SESSION['error'])) {
				$success = $_SESSION['error'];
				unset($_SESSION['error']);
			}
			return [$success,$error];
		}

		private function _createThumnail($savePath) {
			$imageSize = getimagesize($savePath);
			$width = $imageSize[0];
			$height = $imageSize[1];
			if($width > THUM_WIDTH) {
				$this->_createThumnailMain($savePath,$width,$height);
			}
		}

		private function _createThumnailMain($savePath,$width,$height) {
			switch($this->_imageType) {
				case IMAGETYPE_GIF:
					$srcImage = imagecreatefromgif($savePath);
					break;
				case IMAGETYPE_JPEG:
					$srcImage = imagecreatefromjpeg($savePath);
					break;
				case IMAGETYPE_PNG:
					$srcImage = imagecreatefrompng($savePath);
					break;
			}

			$thumbHeight = round($height * THUM_WIDTH / $width);
			$thumbImage = imagecreatetruecolor(THUM_WIDTH,$thumbHeight);
			imagecopyresampled($thumbImage, $srcImage, 0, 0, 0, 0, THUM_WIDTH, $thumbHeight, $width, $height);

			switch($this->_imageType) {
				case IMAGETYPE_GIF:
					imagegif($thumbImage,THUM_DIR.'/'.$this->_imageFileName);
					break;
				case IMAGETYPE_JPEG:
					imagejpeg($thumbImage,THUM_DIR.'/'.$this->_imageFileName);
					break;
				case IMAGETYPE_PNG:
					imagepng($thumbImage,THUM_DIR.'/'.$this->_imageFileName);
					break;
			}
		}

		private function _save($ext) {
			$date01 = date('Ymd');
			$date02 = date('His');
			$this->_imageFileName = sprintf(
				'%s_%s_'.$this->pathDateName.'.%s',$date01,$date02,$ext
			);
			$savePath = IMG_DIR.'/'.$this->_imageFileName;
			$res = move_uploaded_file($_FILES['image']['tmp_name'], $savePath);
			if($res === false) {
				throw new \Exception('Could not Upload!');
			}
			return $savePath;
		}// private function _save()

		private function _validateImageType() {
			$this->_imageType = exif_imagetype($_FILES['image']['tmp_name']);
			switch($this->_imageType) {
				case IMAGETYPE_GIF:
					return 'gif';
				case IMAGETYPE_JPEG:
					return 'jpg';
				case IMAGETYPE_PNG:
					return 'png';
				default:
					throw new \Exception('GIF/JPG/PNG ONLY!');
			}
		}// private function _validateImageType()

		private function _validateUpload() {
			// var_dump($_FILES);
			// exit;
			if(!isset($_FILES['image']) || !isset($_FILES['image']['error'])) {
				throw new \Exception('Upload Error!');
			}

			switch($_FILES['image']['error']) {
				case UPLOAD_ERR_OK:
					return true;
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					throw new \Exception('File too large!');
				default:
					throw new \Exception('Err : '.$_FILES['image']['error']);
			}
		}// private function _validateUpload()

		public function getImages() {
			$images = [];
			$files = [];
			$imageDir = opendir(IMG_DIR);
			while(false !== ($file = readdir($imageDir))) {
				if($file === '.' || $file === '..') {
					continue;
				}
				$files[] = $file;
				if(file_exists(THUM_DIR.'/'.$file)) {
					$images[] = basename(THUM_DIR).'/'.$file;
				} else {
					$images[] = basename(IMG_DIR).'/'.$file;
				}
			}
			array_multisort($files, SORT_DESC,$images);
			return $images;
		}
	}
