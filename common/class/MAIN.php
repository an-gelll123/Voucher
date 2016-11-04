<?php
//	####################################################################
//			Module purpose: MAIN class, extended for every module
//			Parameters:
//
//	####################################################################

include_once("CONFIG.php");
include_once("INPUT.php");
include_once("DBCON.php");
include_once("SESSIONS.php");
include_once("TPL.php");
include_once("AUTH.php");
include_once("FUNCS.php");
include_once("LOGGER.php");

class MAIN{
	function MAIN(){
		$skip = array("password");
		FUNCS::escape($skip);
		new INPUT;
		new DBCON;
		new SESSIONS;

		//	redirect to main page when SESSION is expired (and its not the main page)
//		if (!count($_SESSION)>0 && $_SERVER['PHP_SELF']!=FULL_ADMIN_PATH . "index.php")
		if (!count($_SESSION)>0 || !strstr($_SERVER['PHP_SELF'], FULL_ADMIN_PATH) )
			header("Location: " . FULL_ADMIN_PATH . "index.php");

		$_SESSION['history']['prev'] = $_SESSION['history']['current'];
		$_SESSION['history']['current']= (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : "";

		new TPL;
		TPL::INIT();

//		FUNCS::escape();
		$this->INIT();

		new AUTH($this->authType);

		$class = get_class($this);

		if (is_file(TEMPLATES_DIR . "common.html")){

			TPL::read(TEMPLATES_DIR . "common.html");
		}

		if (is_file(TEMPLATES_DIR . "header.html")){
			TPL::include_tpl("header");
		}

		if (is_file(TEMPLATES_DIR . $class . ".html")){
			TPL::read(TEMPLATES_DIR . $class . ".html");
		}

		if (is_file(TEMPLATES_DIR . $class . ".html")){
			TPL::show($class);
		}

		$this->Start();

		if (is_file(TEMPLATES_DIR . "footer.html")){
			TPL::include_tpl("footer");
		}
	}

	function INIT(){}
	function Start(){}
}
?>