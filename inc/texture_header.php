<?php
// attempt to make an object to pass around instead of all the code repeated in individual files.
class texture_header {
	public $components = array();
	private $index; // Used for keeping track of where we are at with the binary file
	private $binary; // Stores the binary copy of the header
	
	public function load($filename) {
		// Open up the header file so that we can extract information from it
		$fp = fopen($filename, 'rb');
		// read the entire file into a binary string
		$this->binary = fread($fp, filesize($filename));
		// finally close the file
		fclose($fp);

		// Replace the header options with what was sent in
		$temp = unpack("V7", $this->binary, 0);
		$this->components["header_size"] = array_shift($temp);
		$this->components["image_size"]  = array_shift($temp);
		$this->components["width"]       = array_shift($temp);
		$this->components["height"]      = array_shift($temp);
		$this->components["flags"]       = array_shift($temp);
		$this->components["fade1"]       = array_shift($temp);
		$this->components["fade2"]       = array_shift($temp);
		$temp = unpack("C", $this->binary, 28);
		$this->components["alpha"]       = array_shift($temp);
		$this->components["version"]     = substr($this->binary, 29, 3);

		// Get the filename
		$this->index = 32;
		$path = "";
		$done = false;
		while (!$done) {
			$char = substr($this->binary, $this->index, 1);
			if (preg_match('/[^\x20-\x7e]/', $char)) {
				$done = true;
			} else {
				$this->index += 1;
				$path .= $char;
			}
			if ($this->index > 1000) { $done = true; }
		}
		$this->components["fullpath"]      = $path;
		$this->components["internal_file"] = preg_replace('/.*\//', '', $path);
	}

	public function update_flags($flags) {
		$this->components["flags"] = $flags;
	}
	
	public function save($file) {
		// Reconstruct the header file
		$new_header = pack("V", $this->components["header_size"]);
		$new_header .= pack("V", $this->components["image_size"]);
		$new_header .= pack("V", $this->components["width"]);
		$new_header .= pack("V", $this->components["height"]);
		$new_header .= pack("V", $this->components["flags"]);
		$new_header .= pack("V", $this->components["fade1"]);
		$new_header .= pack("V", $this->components["fade2"]);
		$new_header .= pack("C", $this->components["alpha"]);
		$new_header .= $this->components["version"];
		$new_header .= $this->components["fullpath"];
		
		// Add in the rest of the header after the path. I am not sure what it is for
		while ($this->index < $this->components["header_size"]) {
			$temp = unpack("C", $this->binary, $this->index);
			$new_header .= pack("C", array_shift($temp));
			$this->index++;
		}
		$fp = fopen("images/".$this->components["internal_file"], 'rb');
		// read the entire file into a binary string
		$image_binary = fread($fp, $this->components["image_size"]);
		fclose($fp);

		// Write out the new header file
		$fp = fopen("headers/$file.texture.header", 'w');
		fwrite($fp, $new_header, $this->components["header_size"]);
		fclose($fp);		
		
		// Combine the header and image into a texture
		$fp = fopen("textures/$file.texture", 'w');
		fwrite($fp, $new_header, $this->components["header_size"]);
		fwrite($fp, $image_binary, $this->components["image_size"]);
		fclose($fp);	
	}
}
?>