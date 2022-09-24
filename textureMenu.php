<head>
<title>Texture Manipulation</title>
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
</head>
<body>
<form action=textureMenu.php method=POST enctype="multipart/form-data">

Select the image to manipulate here and it will show up in the bottom frame <select name=image onchange="window.open('modifyTexture.php?filename='+this.value,'bottom')">
<?php
session_start();

if (isset($_SESSION['message']) && $_SESSION['message']) {
	echo '<p>Session Message: '.$_SESSION['message'].'</p>';
	unset($_SESSION['message']);
}

$message = "nothing happened";

// Get a listing of the files we can modify
$dh = opendir("textures");
while (false !== ($file = readdir($dh))) {
	if (!preg_match('/^\./', $file)) {
		$file = preg_replace('/\..*/', '', $file);
		print "<option>$file</option>\n";
	}
}
closedir($dh);
?>
</select>
<?php	
if (isset($_FILES['filename']) && $_FILES['filename']['error'] === UPLOAD_ERR_OK) {
	// get details of the uploaded file
	$fileTmpPath = $_FILES['filename']['tmp_name'];
	$fileName = $_FILES['filename']['name'];
	$fileSize = $_FILES['filename']['size'];
	$fileType = $_FILES['filename']['type'];
	$fileNameCmps = explode(".", $fileName);
	$fileExtension = strtolower(end($fileNameCmps));
	$message = "$fileName";

	// Only allow texture files
	$allowedfileExtensions = array('texture');
    if (in_array($fileExtension, $allowedfileExtensions)) {
		if (move_uploaded_file($fileTmpPath,"textures/$fileName")) {
			$message = "File moved to texture folder";
			// Split the file into a header and image file
			// get size of the binary file
			$filesize = filesize("textures/$fileName");
			// open file for reading in binary mode
			$fp = fopen("textures/$fileName", 'rb');
			// read the entire file into a binary string
			$binary = fread($fp, $filesize);
			// finally close the file
			fclose($fp);

			// Read the header and image sizes
			// These files use little endian format
			$temp = unpack("V7", $binary, 0);
			$header_size = array_shift($temp);
			$image_size = array_shift($temp);
			$width = array_shift($temp);
			$height = array_shift($temp);
			$flags = array_shift($temp);
			$fade1 = array_shift($temp);
			$fade2 = array_shift($temp);
			$temp = unpack("C", $binary, 28);
			$alpha = array_shift($temp);
			$version = substr($binary, 29, 3);
			
			// Write out the header file
			$fp = fopen("headers/$fileName.header", 'w');
			fwrite($fp, $binary, $header_size);
			fclose($fp);
			
			// Get the filename
			$index = 32;
			$path = "";
			$done = false;
			while (!$done) {
				$char = substr($binary, $index, 1);
				if (preg_match('/[^\x20-\x7e]/', $char)) {
					$done = true;
				} else {
					$index += 1;
					$path .= $char;
				}
				if ($index > 1000) { $done = true; }
			}
			$path=preg_replace('/.*\//', '', $path);
			
			// Write out the image file
			$image = substr($binary, $header_size);
			$fp = fopen("images/$path", 'w');
			fwrite($fp, $image);
			fclose($fp);
		}
    }
}

$_SESSION['message'] = $message;
?>
	<p>Or upload a new texture to work with
	<input type=file id=texture name=filename>
	<input type=submit name=submit value="Start Upload"></p>
	<p>These are links to the directories where the texture, images, and header files reside
	<a href="images">Images</a>
	<a href="headers">Header Files</a>
	<a href="textures">Texture Files</a>
</form>
<iframe name=bottom width=100% height=80% src=modifyTexture.php></iframe>
</body>