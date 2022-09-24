<head>
<title>Texture Manipulation</title>
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
</head>
<body>
You can download the image from the table below. Once you are done modifying it you can then Upload it below and update the texture file. Then you can download the newly made texture file for inclusion into the pigg file.
<form action=modifyTexture.php method=POST>
<table border=1>
	<tr>
		<th>Attribute</th>
		<th>Header Info</th>
		<th>Image Info</th>
	</tr>
<?php
	include_once('inc/texture_header.php');

	$file;
	if (isset($_GET["filename"])) {
		$file = $_GET["filename"];
	}
	if (isset($_POST["filename"])) {
		$file = $_POST["filename"];
	}

	$header  = new texture_header;

	// Header is being updated
	if (isset($_POST["header"])) {
		$header->load("headers/$file.texture.header");
		
		// Now update the flags
		$flags=0;
		for ($x = 0; $x <= 29; $x++) {
			if (str_contains($_POST["opt".$x],"true")) {
				$flags += pow(2, $x);
			}
		}
		$header->update_flags($flags);
		
		if (isset($_POST["width"])) {
			$header->components["width"] = $_POST["width"];
		}

		if (isset($_POST["height"])) {
			$header->components["height"] = $_POST["height"];
		}
		
		// Save the new header and texture
		$header->save($file);
	}
	
	// Image is being updated
	if (isset($file)) {
		// If this was an upload or initial
		if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
			// get details of the uploaded file
			$fileTmpPath = $_FILES['image']['tmp_name'];
			$image = $_FILES['image']['name'];
	
			// Copy the image to the right folder
			print "images/$image\n";
			move_uploaded_file($fileTmpPath,"images/$image");
			
			// Update the header with the new image size
			// The save will trigger the texture to update as well
			$header->load("headers/$file.texture.header");
			$header->components["image_size"] = filesize("images/$image");
			$header->save($file);
			
			print "Texture file updated\n";
		}
		
		// Load the header info
		$header->load("headers/$file.texture.header");
		
		print "<tr><td>Header Size</td><td>".$header->components["header_size"]." bytes</td><td></td>\n";
		print "<tr><td>Image Size</td><td>".$header->components["image_size"]." bytes</td><td>".filesize("images/".$header->components["internal_file"]);
		print " bytes <a href=\"images/".$header->components["internal_file"]."\">".$header->components["internal_file"]."</a></td>\n";
		print "<tr><td>Width</td><td><input type=text size=5 name=width value=\"".$header->components["width"]."\"> pixels</td><td></td>\n";
		print "<tr><td>Height</td><td><input type=text size=5 name=height value=\"".$header->components["height"]."\"> pixels</td><td></td>\n";
		print "<tr><td>Alpha</td><td>".$header->components["alpha"]."</td><td></td>\n";
		if (str_contains($header->components["version"],"TX2")) {
			print "<tr><td>Version</td><td>".$header->components["version"]." Version 2</td><td></td>\n";
		} else {
			print "<tr><td>Version</td><td>".$header->components["version"]." Version 1</td><td></td>\n";
		}
		print "<tr><td>Path</td><td>".$header->components["fullpath"]."</td><td></td>\n";
?>
</table>
Texture Options
<table>
  <tr>
    <th>Option Name</th>
	<th>Value</th>
    <th>Option Name</th>
	<th>Value</th>
  </tr>
<?php
		$options = array("Fade","Truecolor","Treat as multitexture","Multitexture",
		  "No Random Glow","Full Bright","Clamp S","Clamp T","Always Add Glow",
		  "Mirror S","Mirror T","Replaceable","Bump Map","Repeat S","Repeat T",
		  "Cube Map","No MIP","JPEG","No Dither","No Coll","Surface Slick",
		  "Surface Ice","Surface Bouncy","Border","Old Tint","Double Fusion",
		  "Point Sample","Normal Map","Spec In Alpha","Fallback Force Opaque");
		
		$row=0;
		for ($x = 0; $x <= 29; $x++) {
			if ($row == 0) {
				print "<tr>\n";
			}
			print "<td>".$options[$x]."</td><td><select name=opt$x>";
			if (pow(2,$x) & $header->components["flags"]) {
				print "<option selected>true</option><option>false</option>";
			} else {
				print "<option>true</option><option selected>false</option>";
			}
			print "</select></td>\n";
			if ($row == 2) {
				print "</tr>\n";
				$row = 0;
			} else {
				$row++;
			}
		}

		print "</table>\n";
		print "<input type=hidden name=filename value=\"$file\">\n";
		print "<input type=submit name=header value=\"Update Header File\">\n";
		print "</form>\n";
		print "<hr>\n";
		print "<form action=modifyTexture.php method=POST enctype=\"multipart/form-data\">\n";
		print "<input type=hidden name=filename value=".$file.">\n";
		print "<p><br>Upload replacement image\n";
		print "<input type=file id=image name=image>\n";
		print "<input type=submit name=submit value=\"Update Texture\">\n";
		print "</form>\n";
		
		print "<p>Texture download\n";
		print "<a href=\"textures/$file.texture\">$file</a>\n";
	}
	
?>

</body>