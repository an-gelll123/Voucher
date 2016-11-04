<?php
//	####################################################################	//
//			Module purpose: To provide session functionality		//
//			Parameters:									//
//													//
//	####################################################################	//

class SESSIONS {
	function SESSIONS() {
		//	Declare sessions functions...
		session_set_save_handler(
				array(&$this,"open"),
				array(&$this,"close"),
				array(&$this,"read"),
				array(&$this,"write"),
				array(&$this,"destroy"),
				array(&$this,"sess_gc"));
		ini_set("session.gc_probability", 5);
		ini_set("session.gc_divisor", 100);
//		session_name(Config::Variable("SESSION_NAME"));
		session_start();
	}
	
	static function clear() {session_unset();}
	static function open($path, $name) {return true;}
	static function close() {return true;}
	
	static function read($id) {
		$res = DBCON::query("SELECT data FROM sessions WHERE id = '$id' AND expiry > ".time(), __FILE__, __LINE__);
		if ($row = DBCON::fetch_assoc($res)) return $row['data'];
		else return false;
	}
	
	static function write($id, $data) {
		$newExp = time() + ((CONFIG::VARIABLE("SESSION_EXPIRE"))
													? CONFIG::VARIABLE("SESSION_EXPIRE")
													: get_cfg_var("session.gc_maxlifetime"));
		$res = DBCON::query("SELECT * FROM sessions WHERE id = '$id'", __FILE__, __LINE__);
		if (DBCON::num_rows($res)) {
			$sql = "UPDATE sessions SET expiry = '$newExp', data = '$data' WHERE id = '$id'";
			$res = DBCON::query($sql, __FILE__, __LINE__);
			if (DBCON::affected_rows($res)) return true;
		} else {
			$sql = "INSERT INTO sessions VALUES('$id', '$data', '$newExp')";
			$res = DBCON::query($sql, __FILE__, __LINE__);
			if (DBCON::affected_rows($res)) return true;
		}

		return false;
	}

	static function destroy($id) {
		$sql = "DELETE FROM sessions WHERE id = '$id'";
		$res = DBCON::query($sql, __FILE__, __LINE__);
		if (DBCON::affected_rows($res)) return true;
		else return false;
	}

	static function sess_gc($maxLifeTime) {
		$sql = "DELETE FROM sessions WHERE expiry < ".time();
		$res = DBCON::query($sql, __FILE__, __LINE__);
		return DBCON::affected_rows($res);
	}
	
	static function sess_unset(){
		session_unset();
	}
}

?>
