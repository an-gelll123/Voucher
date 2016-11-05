<?php

include_once("../../common/class/MAIN.php");
include_once("../../common/class/ITEMSLIST.php");

class index extends MAIN{
	public $tablename = "adminusers";

	function INIT(){
		$this->authType = ADMIN;
	}

	function Start(){
	
		ItemsList::Factory($this->tablename)
			->addColumn("fullname", "Име")
			->addColumn("login", "login")
			->addColumn("phone", "Телефон")
			->addColumn("account_type", "Тип акаунт")
			->show();
		die ();
	
		TPL::read(CONFIG::VARIABLE("URL") . "/templates/list.htm");
		TPL::read(CONFIG::VARIABLE("URL") . "/templates/sidebar_nav.htm");
		TPL::read(CONFIG::VARIABLE("URL") . "/templates/items_list.htm");
		TPL::add("main", "URL", CONFIG::VARIABLE("URL"));
		TPL::adds("sidebar-nav", array(
			"URL"					=>	CONFIG::VARIABLE("URL"),
			"CAT_SETTINGS_COLLAPSE"	=>	"in",
			"ADMINISTRATORS_ACTIVE"	=>	"class='active'",
		));
		TPL::show("main");
	}
}

new index;
?>