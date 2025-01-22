<?php

class File {

	public $filename = "";

	public function __construct($filename) {
		$this->filename = $filename;
	}

	public function getExt() {
		$return = "";
		$parts = explode(".", $this->filename);
		$return = array_pop($parts);
		return $return;
	}

	public function getSize($unit = 0) {
        if(file_exists(LOCALFILES.substr($this->filename, 0, 2).DS.$this->filename)) {
            return Common::getPCSize(filesize(LOCALFILES.substr($this->filename, 0, 2).DS.$this->filename), $unit);
        } else {
            return 0;
        }
	}

	public function getUploadTime() {
		return date("Y-m-d H:i:s", filectime(LOCALFILES.substr($this->filename, 0, 2).DS.$this->filename));
	}

}
