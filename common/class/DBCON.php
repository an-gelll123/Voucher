<?php
//	####################################################################	//
//			Module purpose: To provide DB connection				//
//			Parameters:									//
//													//
//	####################################################################	//

class DBCON{
	private static $db_link;

	function DBCON(){
			DBCON::$db_link = $this->connect(CONFIG::VARIABLE("HOST"), CONFIG::VARIABLE("USER"), CONFIG::VARIABLE("PASS"));

			if (!$this->selectDB(CONFIG::VARIABLE("DATABASE"))){
				echo "Can't select DB!!!<Br>";
				die ();
			}

			$this->query("SET names cp1251");
	}

	static function connect($host, $user, $pass){
			return mysql_connect($host, $user, $pass);
	}

	static function selectDB($database){
			return mysql_select_db($database);
	}

	static function check($str){
			echo "CHECKING...<Br>";
	}

	static function query($query, $file=__FILE__, $line=__LINE__){
		return mysql_query($query);
	}

	static function fetch_row($res){
		return (mysql_fetch_row($res));
//		return FUNCS::goEscape(mysql_fetch_row($res));
	}

	static function fetch_assoc($res){
		return mysql_fetch_assoc($res);
	}

	static function num_rows($res){
		return is_resource($res) ? mysql_num_rows($res) : null;
	}

	static function affected_rows($res){
		return (is_resource($res) && mysql_affected_rows($res));
	}

	static function insert_id(){
		return mysql_insert_id();
	}

	static function num_fields($res){
		return mysql_num_fields($res);
	}

	static function update_insert_pos($table, $columns, $skip=array(), $id=NULL){
		if ( !strlen($table)>0 )
			return false;

		$sql = "SELECT MAX(pos) FROM $table";
		$res = DBCON::query($sql);

		list($pos) = DBCON::fetch_row($res);
		$pos++;

		if ( (int)$id>0 ){
			//	UPDATE...
			if ( count($columns)>0 ){

				foreach($columns AS $key=>$val){
					if ( !count($skip)>0 && !in_array($key, $skip)){
						$val = "'" . $val . "'";
					}
					$tmpColArr[] = "$key=$val";
				}
			}
			$tmpArr[] = "pos='$pos'";
			$columnsStr = implode(", ", $tmpColArr);
			return "UPDATE $table SET " . $columnsStr . " WHERE id=" . $id;
		} else {
			//	INSERT...
			if ( count($columns)>0 ){
				foreach($columns AS $key=>$val){
					if ( !count($skip)>0 && !in_array($key, $skip)){
						$val = "'" . $val . "'";
					}
					$tmpArr[] = "$key=$val";
				}
			}
			$tmpArr[] = "pos='$pos'";
			$tmpArr[] = "id=NULL";

			$columnsStr = implode(", ", $tmpArr);
			return "INSERT INTO  $table SET " . $columnsStr;
		}
	}

	static function update_insert($table, $columns, $skip=array(), $id=NULL){
		if ( !strlen($table)>0 )
			return false;

		if ( (int)$id>0 ){
			//	UPDATE...
			if ( count($columns)>0 ){

				foreach($columns AS $key=>$val){
					if ( !(count($skip)>0 && in_array($key, $skip))){
						$val = "'" . $val . "'";
					}
					$tmpColArr[] = "$key=$val";
				}
			}
			$columnsStr = implode(", ", $tmpColArr);
			return "UPDATE $table SET " . $columnsStr . " WHERE id=" . $id;
		} else {
			//	INSERT...
			if ( count($columns)>0 ){
				foreach($columns AS $key=>$val){
					if ( !(count($skip)>0 && in_array($key, $skip))){
						$val = "'" . $val . "'";
					}
					$tmpArr[] = "$key=$val";
				}
			}
			$tmpArr[] = "id=NULL";

			$columnsStr = implode(", ", $tmpArr);
			return "INSERT INTO  $table SET " . $columnsStr;
		}
	}

	static function replace($table, $columns, $skip=array()){
		if ( !strlen($table)>0 || !is_array($columns) || !count($columns)>0 )
			return false;

		foreach($columns AS $key=>$val){
			if ( !count($skip)>0 && !in_array($key, $skip)){
				$val = "'" . $val . "'";
			}
			$tmpColArr[] = "$key=$val";
		}
		$columnsStr = implode(", ", $tmpColArr);

		return "REPLACE INTO $table SET " . $columnsStr;
	}

	static function insert($table, $columns, $skip=array()){
		if ( !strlen($table)>0 || !is_array($columns) || !count($columns)>0 )
			return false;

		$keys = $vals = array();
		foreach($columns AS $key=>$val){
			if ( !count($skip)>0 || !in_array($key, $skip)){
				$val = "'" . $val . "'";
			}
			$keys[] = $key;
			$vals[] = $val;
		}
		$sql =  "INSERT INTO %s(%s) VALUES(%s)";
		return sprintf($sql, $table, implode(", ", $keys), implode(", ", $vals));
	}
}
?>
