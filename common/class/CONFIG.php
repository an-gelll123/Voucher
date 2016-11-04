<?php
//	####################################################################
//			Module purpose: To provide set configuration parameters
//			Parameters:
//
//	####################################################################

class CONFIG{
	static function VARIABLE($name="", $value=""){
		static $vars;
		if ($name!="" && ($value!="" || (is_array($value) && count($value)>0))){
			$vars[$name] = $value;
		} else {
			return $vars[$name];
		}
	}
}

CONFIG::VARIABLE("HOST", "localhost");
CONFIG::VARIABLE("USER", "vOucherUser");
CONFIG::VARIABLE("PASS", "jjjhu7567JHfg^");
CONFIG::VARIABLE("DATABASE", "voucher");


CONFIG::VARIABLE("URL", "http://127.0.0.1/voucher/admin/");

CONFIG::VARIABLE("ARR_LANGS", array(
	1	=>	"���������",
	2	=>	"���������",
	3	=>	"�����",
	4	=>	"������",
	5	=>	"��������",
));

CONFIG::VARIABLE("REF_ADMIN_TYPES", array(
	"admin"		=> "�������������",
	"operator"		=> "��������",
	"accounter"		=> "������������",
));

CONFIG::VARIABLE("PAYMENT_TYPES", array(
		"1"	=> "� ���� / Cache",
		"2"	=> "� ����� / Credit card",
		"3"	=> "�� ������ / Bank transfer",
));

CONFIG::VARIABLE("PAYMENT_TYPES_INVOICE", array(
		"1"	=> "� ���� / Cache",
		"2"	=> "� ����� / Credit card",
		"3"	=> "�� ������ ��� / Bank transfer",
));

CONFIG::VARIABLE("VOUCHER_STATUS", array(
		"1"	=> "��������",
		"2"	=> "������",
));

CONFIG::VARIABLE("INVOICE_TYPES", array(
		"1"	=> "�������",
		"2"	=> "�������� ��������",
		"3"	=> "������� ��������",
));

CONFIG::VARIABLE("DDS_TYPES", array(
		"1"	=> "0",
		"2"	=> "9",
		"3"	=> "20",
));

CONFIG::VARIABLE("invoice_prefix", "1");
CONFIG::VARIABLE("invoice_item", "�������� �����������");
CONFIG::VARIABLE("invoice_reason", "��������� �� ������������� �� ��� - ��.86 ��.1 �� ������");

//CONFIG::VARIABLE("main_dir", "");
//CONFIG::VARIABLE("main_url", "");

CONFIG::VARIABLE("SESSION_EXPIRE", 3600);

$DOC_ROOT_PATH = $_SERVER['DOCUMENT_ROOT'] . "/";
$WEBSITE_DIR = "voucher";
$ADMIN_DIR = "admin";
$TEMPLATES_DIR = "templates";

define('FULL_WEBSITE_PATH',	"/" . $WEBSITE_DIR . "/");
define('FULL_ADMIN_PATH', 	"/" . $WEBSITE_DIR . "/" . $ADMIN_DIR . "/");

define('TEMPLATES_DIR', "templates/");
define('BACKUPS_DIR', $DOC_ROOT_PATH . "voucher/admin/backups/");
define('FULL_WEBSITE_DIR', $DOC_ROOT_PATH . "/" . $WEBSITE_DIR);
define('FULL_ADMIN_DIR', $DOC_ROOT_PATH . $WEBSITE_DIR . "/" . $ADMIN_DIR);
define('FULL_TEMPLATES_DIR', $DOC_ROOT_PATH . $WEBSITE_DIR . "/" . TEMPLATES_DIR);
define('FULL_ADMIN_TEMPLATES_DIR', $DOC_ROOT_PATH . $WEBSITE_DIR . "/" . $ADMIN_DIR . "/" . TEMPLATES_DIR);
define('FULL_CLASS_DIR', $DOC_ROOT_PATH . $WEBSITE_DIR . "/common/class/");

define('ADMIN', "admin");
define('CLIENT', "client");
define ('ADMIN_LANG', 1);

define('YES', "<font class='yes'>��</font>");
define('NO', "<font class='no'>��</font>");
?>