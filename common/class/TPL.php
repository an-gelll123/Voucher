<?php
//	####################################################################	//
//			Module purpose: To provide template functionalities		//
//			Parameters:									//
//													//
//	####################################################################	//

require_once("patTemplate.php");

class TPL {
	private static $template;
	function __construct() {
		TPL::$template = new patTemplate();
//		TPL::$template->setBasedir(Config::Variable("TEMPLATE_PATH") . "/" . Language::code());
//		TPL::$template->addGlobalVar("TEMPLATE_PATH", Config::Variable("TEMPLATE_PATH") . "/" . Language::code());
	}

	static function INIT() {
		TPL::$template = new patTemplate();
//		TPL::$template->setBasedir(Config::Variable("TEMPLATE_PATH") . "/" . Language::code());
//		TPL::$template->addGlobalVar("TEMPLATE_PATH", Config::Variable("TEMPLATE_PATH") . "/" . Language::code());
	}

//	static function getHeader() { return $this->header; }
//	static function getFooter() { return $this->footer; }

	static function read($template) {
//		TPL::$template->readTemplatesFromFile($template . "." . Config::Variable("TEMPLATE_CONTENT"));
		TPL::$template->readTemplatesFromFile($template);
	}

	static function show($template) {
		TPL::$template->displayParsedTemplate($template);
	}

	static function preview($template, $variables = NULL) {
		if (!$template) return;
//		TPL::read($template);

		if ($variables)
			TPL::add($template, $variables);

		TPL::show($template);
	}

	static function add($template, $name, $value="") {
		if (is_array($name)){
			TPL::$template->addVars($template, $name);
		} else {
			TPL::$template->addVar($template, $name, $value);
		}
	}

	static function adds($template, $name, $parse=false) {
		TPL::$template->addVars($template, $name);

		if ($parse)
			TPL::$template->parseTemplate($template, "a");
	}

	static function addGlobal($name, $value) {
		TPL::$template->addGlobalVar($name, $value);
	}

	static function parse($template, $type="false") {
		$modifier= $type ? "a" : "w";
		TPL::$template->parseTemplate($template, $modifier);
	}

	static function clear($template) {
		TPL::$template->clearTemplate($template);
	}

	static function get($template) {
		return TPL::$template->getParsedTemplate($template);
	}

	static function include_tpl($tpl_name){
		TPL::$template->readTemplatesFromFile(TEMPLATES_DIR . $tpl_name . ".html");
		TPL::$template->displayParsedTemplate($tpl_name);
	}

	static function setAttribute($template, $attribute, $value){
		TPL::$template->setAttribute($template, $attribute, $value);
	}
/*
	static function goto($location) {
		Display::read("goto");
		Display::add("goto", "LOCATION", $location);
		Display::show("goto");
	}
*/
}

?>
