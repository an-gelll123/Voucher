<?php
class INPUT {
	function INPUT($skip = array()) {
		// form elements that don't need character replacing
		// are defined in $skip. such elements are passwords.
		foreach($_POST as $name=>$value) {
			$flag = 0;
//			foreach($skip as $var) if ($var == $name) $flag = 1;
			if (in_array($name, $skip)) $flag = 1;

			if (is_array($value)) {
				foreach ($value as $n=>$v) $value[$n] = ($flag) ? addslashes($v) : $this->prepare($v);
			} else $value = ($flag) ? addslashes($value) : $this->prepare($value);
			$this->POST($name, $value);
		}

		foreach($_GET as $name=>$value) {
			$flag = 0;
			foreach($skip as $var) if ($var == $name) $flag = 1;

			if (is_array($value)) {
				foreach ($value as $n=>$v) $value[$n] = ($flag) ? addslashes($v) : $this->prepare($v);
			} else $value = ($flag) ? addslashes($value) : $this->prepare($value);
			$this->GET($name, $value);
		}
	}

	// since we're going to use the methods statically
	// a special "static" variable is defined to contain the form elements
	// get: METHOD("name")
	// set: METHOD("name", "value")
	function POST($name = NULL, $value = NULL) {
		static $__post;
		if ($name && $value) $__post[$name] = $value;
		else return $__post[$name];
	}
	
	function GET($name = NULL, $value = NULL) {
		static $__get;
		if ($name && $value) $__get[$name] = $value;
		else return $__get[$name];
	}

	function prepare($text) {
		// replace some characters with their HTML code
		// otherwise we may end up even with a script execution
		return addslashes(str_replace(
				array("&", "\"", "<", ">", "'"),
				array("&amp;", "&quot;", "&lt;", "&gt;"), 
				$text));
	}

	function redirect($href, $parent=NULL){
		if ( !strlen($href)>0 )
			return false;
		$parent= $parent ? $parent."." : "";

		echo "<script>";
		echo $parent."location.href='$href'";
		echo "</script>";
		exit;
	}

	function reload($parent=NULL){
		$parent= $parent ? $parent."." : "";

		echo "<script>";
		echo $parent . "location.reload()";
		echo "</script>";
		exit;
	}
}