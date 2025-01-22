<?php

class Helper {

	public function section($section_id) {
		return Section::getInstance($section_id);
	}

	public function codebook($code, $record = "") {
		$codelistList = new CodelistList();
		return $codelistList->getCodelist($code)->getRecords($record);
	}

	public function user($user_id) {
		$user = new User($user_id);
		$user->load();
		return $user;
	}

	public function file($filename) {
		return new File($filename);
	}
}
