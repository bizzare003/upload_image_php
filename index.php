<?php
require_once(__DIR__. '/config.php');
$upload = new \MyApp\Imageloader();
if($_SERVER['REQUEST_METHOD'] === 'POST') {
	$upload->uploader();
}
$images = $upload->getImages();

list($success, $errors) = $upload->getMessage();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<title>Image Uploader</title>
	<link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php if($success || $errors):?>
<div class="message">
	<?php
		if($success) {
			echo '<p class="success">'.$success.'</p>';
		}
		if($errors) {
			echo '<p>'.$errors.'</p>';
		}
	?>
</div>
<?php endif;?>

<form action="" method="post" enctype="multipart/form-data">
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo h(MAX_FILE_SIZE);?>">
	<input type="file" name="image">
	<div class="submitContainer">
		<input type="submit" value="upload">
	</div>
</form>

<?php if($images):?>
<ul class="thumList">
	<?php foreach($images as $image):?>
	<li>
		<a href="">
			<img src="<?php echo $image;?>" alt="">
		</a>
	</li>
<?php endforeach;?>
</ul>
<?php endif;?>

<?php if($success || $errors):?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script>
	$(function() {
		$('.message').offset({top: 0}).fadeOut(2000);
	});
</script>
<?php endif;?>
</body>
</html>
