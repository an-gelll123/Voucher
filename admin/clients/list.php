<?php

include_once("../../common/class/MAIN.php");

class index extends MAIN{
	function INIT(){
		$this->authType = ADMIN;
	}

	function Start(){
		TPL::read("../templates/sidebar_nav.htm");

		TPL::read("../templates/index.htm");
		TPL::add("main", "URL", CONFIG::VARIABLE("URL"));
		TPL::show("main");
	}
}

new index;
?>