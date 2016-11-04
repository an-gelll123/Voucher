<?php

CONFIG::VARIABLE("LOG_ACTIONS", array(
	1	=>	"login",
	2	=>	"logout",
	3	=>	"add",
	4	=>	"edit",
	5	=>	"delete",
	6	=>	"undelete",
));

class LOGGER{

	public function get($cond, $from_date="", $to_date=""){
		$sql="SELECT id, date_time, adminuser_id, page, rel_id, action, POST, GET FROM log WHERE 1=1";

		if ( is_array($cond) ){
			foreach($cond AS $key=>$val){
				$sql .= " AND " . $key . "='" . $val . "'";
			}
		} else {
			$sql .= " AND id=" . $cond;
		}

		if ( strlen($from_date)>0 )
			$sql .= " AND date_time>'" . $from_date . "'";

		if ( strlen($to_date)>0 )
			$sql .= " AND date_time<'" . $to_date . "'";

		$res = DBCON::query($sql);
		$row = DBCON::fetch_assoc($res);

		return $row;
	}

	public function set($rel_id, $action, $table){
			$adminuser_id = $_SESSION["admin"]["id"];
			$POST = serialize($_POST);
			$GET = serialize($_GET);

			$sql="INSERT INTO log VALUES('', NOW(), '" . $adminuser_id . "', '" . $table . "', '" . $rel_id . "', '" . $action . "', '" . $POST . "', '" . $GET . "')";

			if ( DBCON::query($sql) )
				return true;
			else
				return false;
	}
}
?> 