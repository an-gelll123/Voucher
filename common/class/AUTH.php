<?php
//	####################################################################	//
//			Module purpose: To provide authentification services		//
//			Parameters:									//
//				$type - type of account ([client|admin])			//
//	####################################################################	//

class AUTH{
	function AUTH($type=null){
//		if ( isset($_GET['action']) && $_GET['action']=="logout" && isset($_SESSION[$type]) && (int)$_SESSION[$type]['id']>0){
		if ( isset($_GET['action']) && $_GET['action']=="logout"){
//			LOGGER::set($_SESSION["admin"]["id"], 2, "adminusers");
			SESSIONS::sess_unset();
		}

		//	If session var is not correct redirect to login screen...
//		if ( !(int)$_SESSION[$type]['id']>0 && !strlen($_POST['username'])>0 && $type!=null){
		if ( 	 !isset($_SESSION[$type]) || (!(int)$_SESSION[$type]['id']>0 && 
			!strlen($_POST['username'])>0 )){
				new TPL;
				TPL::INIT();
				TPL::read("templates/login.htm");
				TPL::add("login", array("", ""));

				if(isset($_SESSION['log_error'])){
					TPL::add("error", array("CODE"=>$_SESSION['log_error']));
				}
				unset($_SESSION['log_error']);
				TPL::show("login");
				exit ();
		}
	}
}
?>