<?php
	session_start();
	ini_set('display_errors', 1);
	define('MAX_FILE_SIZE', 1 * 1024 * 1024);
	define('IMGDIR', __DIR__. '/images');
	define('THUMBDIR', __DIR__. '/thumbnails');
	define('THUMBWIDTH', '640');
	require_once(__DIR__ . '/imageUploader.php');
	function h($s) {
		return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
	}