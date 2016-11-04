<?php
//	####################################################################	//
//			Module purpose: To provide common functionality			//
//			Parameters:									//
//													//
//	####################################################################	//

class FUNCS {
	private static $skip;

	static function escape($aSkip=array()) {
		FUNCS::$skip = $aSkip;
		FUNCS::goEscape($_GET);
		FUNCS::goEscape($_POST);
	}

	static function goEscape($arg, $varname="<<<varname>>>"){
		if (is_array($arg)){
			foreach($arg AS $key=>$val){
				$arg[$key] = FUNCS::goEscape($val, $key);
			}
		} else if (strlen($arg)>0){
//			$arg= (!get_magic_quotes_gpc() && !in_array($varname, $this->skip)) ? FUNCS::esc($arg) : $arg;
			$arg= !in_array($varname, FUNCS::$skip, TRUE) ? FUNCS::esc($arg) : $arg;
//			$arg= (!in_array($varname, $this->skip)) ? FUNCS::esc($arg) : $arg;
		}
		return $arg;
	}

	static function esc($text) {
		// replace some characters with their HTML code
		// otherwise we may end up even with a script execution
		return addslashes(str_replace(
				array("&", "\"", "<", ">", "'"),
				array("&amp;", "&quot;", "&lt;", "&gt;", "&#039"), 
				$text));

/*
		return mysql_real_escape_string(str_replace(
				array("&", "\"", "<", ">", "'"),
				array("&amp;", "&quot;", "&lt;", "&gt;"), 
				$text));
*/
	}

	static function jsshow($str){
		$str = addslashes($str);
		echo "<script>";
		echo "alert('$str')";
		echo "</script>";
	}

	static function rteSafe($strText) {
		//returns safe code for preloading in the RTE
		$tmpString = $strText;
		
		//convert all types of single quotes
		$tmpString = str_replace(chr(145), chr(39), $tmpString);
		$tmpString = str_replace(chr(146), chr(39), $tmpString);
		$tmpString = str_replace("'", "&#39;", $tmpString);
		
		//convert all types of double quotes
		$tmpString = str_replace(chr(147), chr(34), $tmpString);
		$tmpString = str_replace(chr(148), chr(34), $tmpString);
//		$tmpString = str_replace("\"", "&quot;", $tmpString);
		
		//replace carriage returns & line feeds
		$tmpString = str_replace(chr(10), " ", $tmpString);
		$tmpString = str_replace(chr(13), " ", $tmpString);
		
		return $tmpString;
	}

	static function escapeArr($dataArr){
		if (is_array($dataArr)){
			foreach($dataArr AS $key=>$val){
				$dataArr[$key] = FUNCS::goEscape($val);
			}
			return $dataArr;
		} else {
			return array();
		}
	}

	//	only for debug;
	static function p($arg, $terminate = true){
		if (is_array($arg) || is_object($arg)){
			echo "<pre>";
			print_r($arg);
			echo "</pre>";
		} else {
			echo $arg;
		}

		if ($terminate)
			die ();
	}

	//	reverse date presentation
	static function date_format($date, $show_time=true){
		if ( strpos($date, " ") ){
			$arr_date = explode(" ", $date);
			$date = $arr_date[0];
			if ($show_time)
				$time = " " . $arr_date[1];
		}

		if ( strpos($date, "-") ){
			$arr_date = explode("-", $date);
			return $arr_date[2] . "." . $arr_date[1] . "." . $arr_date[0] . $time;
		} else if ( strpos($date, ".") ){
			$arr_date = explode(".", $date);
			return $arr_date[2] . "-" . $arr_date[1] . "-" . $arr_date[0] . $time;
		} else {
			return "";
		}
	}

	static function strtolower_cust($str){
		return strtr($str, "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏĞÑÒÓÔÕÖ×ØÙÜÚŞß", "àáâãäåæçèéêëìíîïğñòóôõö÷øùüúşÿ");
	}

	static function strtohigher_cust($str){
		return strtr($str, "àáâãäåæçèéêëìíîïğñòóôõö÷øùüúşÿ", "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏĞÑÒÓÔÕÖ×ØÙÜÚŞß");
	}
}