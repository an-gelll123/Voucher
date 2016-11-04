<?php
//	#################################################################
//		Module purpose: MAILER class, responsible for send emails
//		Parameters:
//	#################################################################

class MAILER{
	private $recipients = array();
	private $arr_values;
	private $tpl;
	private $id;

	public function __construct($email, $tpl, $id, $arr_values=array()){
		$this->arr_values = $arr_values;
		$this->tpl = $tpl;
		$this->id = $id;
		$this->add_recipients($email);
	}
	
	public function add_recipients($arg_recipients){
		if (is_array($arg_recipients)){
			foreach($arg_recipients AS $rec)
				$this->add_recipients($rec);
		}
			
		if (is_string($arg_recipients) && strstr($arg_recipients, "@"))
			array_push($this->recipients, strtolower($arg_recipients));
	}

	function get_email_answer(){
		$sql = "SELECT name, email, q_text AS question, a_text AS answer FROM questions WHERE id=" . $this->id;
		$res = DBCON::query($sql);
		$this->row= DBCON::fetch_assoc($res);

		$ref_admin_types = CONFIG::VARIABLE("REF_ADMIN_TYPES");
		$this->row["employee_name"] = $_SESSION["admin"]["fullname"] . " - " . $ref_admin_types[$_SESSION["admin"]["account_type"]];

		$this->tpl->add("mailer", $this->row);
		return $this->tpl->get("mailer");
	}
	
	function email_notification(){
		
	}

	function process_email(){
		$email_addr = implode(", ", $this->recipients);
		$email_addr = "an_gel@mail.bg, an_gel@abv.bg";
	
		$header .= "From: \"Askdoctor web answers system\" <mc@spaclubbor.com> \n";
		$header .= "MIME-Version: 1.0\n";
		$header .= "Content-Type: text/html\n";

		$answer = $this->get_email_answer();
		return mail($email_addr,"Попитай доктора онлайн - въпрос от " . $this->row['name'], $answer, $header);
	}
	
	public function p(){
		return $this->recipients;
	}
	
}
?>