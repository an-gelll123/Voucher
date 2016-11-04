<?php
include_once("../common/class/MAIN.php");
//include_once("../common/class_cust/BACKUP.php");

class login extends MAIN{
	var $usersTables = array(
		ADMIN	=> "adminusers",
		CLIENT	=> "clientusers",
	);

	function INIT(){
		$this->authType = ADMIN;

		//	ToDo...
		//	To handle active (some users may not be active)...
		$sql = "SELECT id, password, fullname, account_type FROM " . $this->usersTables[$this->authType] . " WHERE login='".$_POST['username']."' AND active=1";
		$res = DBCON::query($sql,__FILE__,__LINE__);
		if((int)DBCON::num_rows($res)>0){
			list($id, $password, $fullname, $account_type) = DBCON::fetch_row($res);
			if ( md5($_POST['password']) == $password ){
				//	Log-in client...
				//		1. Handle sessions changes...
				//		2. Redirect user to right login template...
				$_SESSION[$this->authType]['id'] = $id;
				$_SESSION["type"] = $this->authType;
				$_SESSION["admin"]["fullname"] = $fullname;
				$_SESSION["admin"]["account_type"] = $account_type;

//				LOGGER::set($id, 1, "adminusers");
//				new BACKUP;
//				exit();
			} else {
				$_SESSION['log_error'] = 1;			
			}
		} else {
			$_SESSION['log_error'] = 1;
		}
		header("Location: index.php");
	}
}

new login;
?>
