<?php
/**
 * FavoriteCookiePlugin.php
 *
 * @package FavoriteCookiePlugin
 * @author Moxiecode
 * @copyright Copyright © 2007, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class handles MCImageManager FavoriteCookiePlugin stuff.
 *
 * @package FavoriteCookiePlugin
 */
class Moxiecode_FavoritesPlugin extends Moxiecode_ManagerPlugin {
	/**#@+
	 * @access public
	 */

	var $_maxfavorites = 10;
	var $_cookieData = null;

	/**
	 * ..
	 */
	function Moxiecode_FavoritePlugin() {
	}

	function onInit(&$man) {
		$man->registerFileSystem('favorite', 'FavoriteFile');

		return true;
	}

	function onRPC(&$man, $cmd, $input) {
		if ($cmd == "addFavorites")
			return $this->_addFavorites($man, $input);

		if ($cmd == "removeFavorites")
			return $this->_removeFavorites($man, $input);

		return null;
	}

	function _removeFavorites(&$man, $input) {
		$result = new Moxiecode_ResultSet("status,file,message");

		for ($i=0; isset($input['path' . $i]); $i++) {
			$status = $this->removeFavorite($man, $input["path". $i]);

			if ($status)
				$result->add("OK", $man->encryptPath($input["path". $i]), "Path was removed.");
			else
				$result->add("FAILED", $man->encryptPath($input["path". $i]), "Path was not removed.");
		}

		return $result->toArray();
	}

	function _addFavorites(&$man, $input) {
		$result = new Moxiecode_ResultSet("status,file,message");

		for ($i=0; isset($input['path' . $i]); $i++) {
			$status = $this->addFavorite($man, $input["path". $i]);

			if ($status)
				$result->add("OK", $man->encryptPath($input["path". $i]), "Path was added.");
			else
				$result->add("FAILED", $man->encryptPath($input["path". $i]), "Path was not added.");
		}

		return $result->toArray();
	}

	function addFavorite(&$man, $path) {
		$config = $man->getConfig();
		$maxfavorites = isset($config["favorites.max"]) ? $config["favorites.max"] : $this->_maxfavorites;

		$type = $man->getType();
		$cookievalue = $this->getCookieData($type);

		$patharray = array();
		$patharray = split(",", $cookievalue);
		if (count($patharray) > 0) {

			for($i=0;$i<count($patharray);$i++) {
				if ($patharray[$i] == $path) {
					array_splice($patharray, $i, 1);
					break;
				}
			}

			array_unshift($patharray, $path);

			if (count($patharray) > $maxfavorites)
				array_pop($patharray);

		} else
			$patharray[] = $path;

		$cookievalue = implode(",", $patharray);

		$this->setCookieData($type, $cookievalue);
		return true;
	}

	function getCookieData($type) {
		if ($this->_cookieData)
			return $this->_cookieData;

		if (isset($_COOKIE["MCManagerFavoriteCookie_". $type]))
			return $_COOKIE["MCManagerFavoriteCookie_". $type];
		else
			return "";
	}

	function setCookieData($type, $val) {
		$this->_cookieData = $val;
		setcookie("MCManagerFavoriteCookie_". $type, $val, time()+(3600*24*30)); // 30 days
	}

	function clearFavorites(&$man) {
		setcookie ("MCManagerFavoriteCookie_". $man->getType(), "", time() - 3600); // 1 hour ago
		return true;
	}

	function removeFavorite(&$man, $path=array()) {
		$type = $man->getType();

		$cookievalue = $this->getCookieData($type);

		$patharray = array();
		$patharray = split(",", $cookievalue);

		$break = false;
		if (count($patharray) > 0) {
			for($i=0;$i<count($patharray);$i++) {
				if (is_array($path)) {
					if (in_array($patharray[$i], $path))
						$break = true;

				} else {
					if ($patharray[$i] == $path)
						$break = true;
				}
				
				if ($break) {
					array_splice($patharray, $i, 1);
					break;
				}
			}
		}

		$cookievalue = implode(",", $patharray);

		$this->setCookieData($type, $cookievalue);

		return true;
	}
}

class FavoriteFile extends Moxiecode_BaseFileImpl {
	function FavoriteFile(&$manager, $absolute_path, $child_name = "", $type = MC_IS_FILE) {
		$absolute_path = str_replace('favorite://', '', $absolute_path);
		Moxiecode_BaseFileImpl::Moxiecode_BaseFileImpl($manager, $absolute_path, $child_name, $type);
	}