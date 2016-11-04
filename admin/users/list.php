<?php

include_once("../../common/class/MAIN.php");

class index extends MAIN{
	function INIT(){
		$this->authType = ADMIN;
	}

	function Start(){
		TPL::read(CONFIG::VARIABLE("URL") . "/templates/list.htm");
		TPL::read(CONFIG::VARIABLE("URL") . "/templates/sidebar_nav.htm");
		TPL::read(CONFIG::VARIABLE("URL") . "/templates/items_list.htm");
		TPL::add("main", "URL", CONFIG::VARIABLE("URL"));
		TPL::adds("sidebar-nav", array(
			"URL"					=>	CONFIG::VARIABLE("URL"),
			"CAT_SETTINGS_COLLAPSE"		=>	"in",
			"ADMINISTRATORS_ACTIVE"	=>	"class='active'",
		));
		TPL::show("main");
	}
}

new index;
?>