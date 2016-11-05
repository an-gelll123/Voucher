<?php

class ItemsList{
	private $tablename;
	private $arrColumns;
	private $sql;

	private function __construct($aTablename){
		$this->tablename = $aTablename;
	}

	public static function Factory($aTablename){
		$itemsList = new ItemsList($aTablename);
		return $itemsList;
	}

	public function addColumn($column, $description){
		$this->arrColumns[$column] = $description;

		return $this;
	}

	public function show(){
		$arrKeys = array_keys($this->arrColumns);

		$sql = "SELECT ";
		$sql .= implode(", ", $arrKeys);
		$sql .= " FROM " . $this->tablename;

		echo $sql;
	}
}