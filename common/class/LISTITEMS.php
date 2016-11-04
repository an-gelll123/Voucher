<?php
//	include template library...
include_once("TPL.php");

/**
 * @author Angel Shukalov
 *
 */
class LISTITEMS {
	private $columns;
	private $allowSearchColumns;	//	columns allowed to be searched in
	private $tablename;
	private $canMove;
	private $actions;
	private $tpl;
	private $def_orderby = "id";
	private $pos_orderby = "ASC";
	private $rel_id;
	private $LJTable = array();		//	manage LEFT JOINS
	private $LJOrigins = array();		//	manage LEFT JOINS origin fields
	private $LJRelations = array();	//	manage LEFT JOINS relations fields

	//	pager
	private $rpp = 20;	//	results per page
	private $page = 1;	//	page
	private $num_rows = 0;//	number of rows extracted with SQL query
	private $num_rowset;	//	number of row in rowset

	//	`Search table`, `New item` and `Delete` buttons
	private $search_table = true;
	private $ni_button = true;
	private $del_button = true;
	private $search_datetime = false;
	
	//	show down arrow when list is ordered by user
	private $down_arrow = "<img src='../pics/arrow_down.png' />";
	private $up_arrow = "<img src='../pics/arrow_up.png' />";

	//	Custom classes for table rows (if any)
	private $arrClasses = array();

/**
 * Constructor function
 * 
 * @param STRING $tablename
 * @param ARRAY $columns
 * @param ARRAY $actions
 * @param STRING $def_orderby
 * @param BOOL $canMove
 * @return N/A
 */
	public function LISTITEMS($tablename, $columns, $actions, $def_orderby="id", $pos_orderby="ASC", $canMove=false, $rel_id="id") {
/*
Array
(
    [id] => 4
    [name] => 
    [city] => 
    [EGN] => 
    [rpp] => 20
    [submitButton] => --ТЪРСИ--
)



$this->columns
Array
(
    [id] => Array
        (
            [0] => идент. номер
            [1] => sprintf('%07s', ptrn)
            [2] => 
        )

    [name] => Име
    [city] => град
    [EGN] => ЕГН
)

*/
		$this->tpl = new TPL();
		$this->tpl->__construct();

		if ( !strlen($tablename)>0 ){
			die ("Error in LISTITEMS: no tablename");
		}
		$this->tablename = $tablename;
		$this->canMove = $canMove;
		$this->rel_id = $rel_id;
		if ( strlen($def_orderby)>0 )
			$this->def_orderby = $def_orderby;

		if ( $pos_orderby=="DESC" )
			$this->pos_orderby = $pos_orderby;
			
		$this->columns = $columns;
		$this->actions = $actions;
	}

/**
 * Service function
 * @return N/A
 */
	private function setSessionValues(){
		//	if this is first click
//		if (!count($_GET)>0 && !count($_POST)>0){
		if (!count($_GET)>0 && !count($_POST)>0){
			unset($_SESSION['search']);
			return;
		}

		//	initialize pager
		if (strlen($_POST["rpp"])>0)
			$_SESSION["rpp"] = $_POST["rpp"];
		
		if (strlen($_SESSION["rpp"])>0)
			$this->rpp = $_SESSION["rpp"];

		if ((int)$_GET["page"]>0)
			$_SESSION["page"] = $_GET["page"];

		if ((int)$_SESSION["page"]>0)
			$this->page = $_SESSION["page"];

		if ($_POST['submitButton'])
			$_SESSION['search'] = $_POST['search'];


/*
		//	add search criteria to $_SESSION (based on search table)
		if ($_POST['submitButton']){
			foreach($this->columns AS $column=>$alias){
				if (!is_array($alias) || $alias[count($alias)-2]){
					$column_name = is_array($alias) ? $alias[0] : $alias;
					$_SESSION['criteria'][$column] = $_POST[$column];
				}
			}

			if (count($this->LJTable)>0){
				foreach($this->LJTable AS $LJTable=>$arr_LJFields){
					foreach($arr_LJFields AS $column=>$alias){
						$column = $LJTable . "." . $column;
//						column = $this->convertDot($column);

						$column_name = is_array($alias) ? $alias[0] : $alias;
						$_SESSION['criteria'][$column] = $_POST[$this->convertDot($column)];
					}
				}
			}
		}
*/
	}

	//	Add LEFT JOIN tables
	//	void addLJTable{LJ TABLENAME, LJ TABLENAME RELATION FIELD, FIELDS}
/**
 * Add LEFT JOIN tables
 * @param STRING $tablename
 * @param STRING $relation_field
 * @param array  $arr_fields - relation field; have this structure:
 * 				array(FIELD_NAME => TABLE_HEADER_NOTE),
 * 				where 	FIELD_NAME - LJ extracted field name
 * 						TABLE_HEADER_NOTE - column name in search table
 * @return N/A
 */
	public function addLJTable($tablename, $origin_field, $lj_field, $arr_fields){
		$this->LJTable[$tablename] = $arr_fields;
		$this->LJRelations[$tablename] = $lj_field;
		$this->LJOrigins[$tablename] = $origin_field;
	}

	private function setSQL(){
		if (is_array($this->columns) && count($this->columns)>0){
			//	make every key in columns array looking field in sql query...
			$arr_fields = array_keys($this->columns);

			//	add 'id' and 'active' fields in sql query (if not exists)...
//			if (!in_array("id", $arr_fields))
			if ( $this->rel_id=="id" )
				array_unshift($arr_fields, "id");

			if ($this->canMove && !in_array("pos", $arr_fields))
				array_unshift($arr_fields, "pos");

			if (!in_array("active", $arr_fields))
				array_unshift($arr_fields, "active");

			//	add {roomname.} before every field...
			array_walk($arr_fields, array($this, 'define_fields'));

			if ( strpos($this->rel_id, ".") && $this->rel_id!="id" )
				array_unshift($arr_fields, $this->rel_id);

			//	this array is needed for searchings (no LJ renames in it)
			$arr_search_fields = $arr_fields;

			//	add fileds for LEFT JOIN tables to SQL
			if (count($this->LJTable)>0){
				foreach($this->LJTable AS $LJTable=>$arr_LJFields){
					foreach($arr_LJFields AS $LJField=>$LJTitle){

						if ( strpos($LJField, " ") ){
							$arr_elem = explode(" ", $LJField);
							array_unshift($arr_fields, $arr_elem[0] . " " . $LJTable . "." . $arr_elem[1] . " AS " . $LJTable . "_" . $arr_elem[1]);
							array_unshift($arr_search_fields, $LJTable . "." . $arr_elem[1]);
						} else {
							array_unshift($arr_fields, $LJTable .  "." . $LJField . " AS " . $LJTable . "_" . $LJField);
							array_unshift($arr_search_fields, $LJTable . "." . $LJField);
						}

//						array_unshift($arr_fields, $LJTable .  "." . $LJField . " AS " . $LJTable . "_" . $LJField);
//						array_unshift($arr_search_fields, $LJTable . "." . $LJField);
					}
				}
			}

			$list = implode(", ", $arr_fields);

			$sql = "SELECT SQL_CALC_FOUND_ROWS " . $list . " FROM " . $this->tablename . " ";

			//	add LEFT JOINS to SQL
			if (count($this->LJTable)>0){
				foreach($this->LJTable AS $LJTable=>$arr_LJFields){
					$orig = strpos($this->LJOrigins[$LJTable], ".")>0 ? $this->LJOrigins[$LJTable] : $this->tablename . "." . $this->LJOrigins[$LJTable];
					$lj = strpos($this->LJRelations[$LJTable], ".")>0 ? $this->LJRelations[$LJTable] : $LJTable . "." . $this->LJRelations[$LJTable];
					
//					$leftJoins[] = "LEFT JOIN " . $LJTable . " ON " . $this->tablename . "." . $this->LJOrigins[$LJTable] . "=" . $LJTable . "." . $this->LJRelations[$LJTable];
					$leftJoins[] = "LEFT JOIN " . $LJTable . " ON " . $orig . "=" . $lj;
				}
				$sql .= implode(" ", $leftJoins);
			}

			$sql .= " WHERE 1=1 ";

			//	include search criteria ('search field' and / or 'show inactive')
			$showActiveCriteria= strlen($_POST["showAll"])>0 ? "" : " AND " . $this->tablename . ".active=1";

			if (strlen($_SESSION['search'])){
				$search = strtoupper($_SESSION['search']);
				foreach($arr_search_fields AS $column=>$value){
					if(count($this->allowSearchColumns)>0 && !in_array($value, $this->allowSearchColumns))
					{
						continue;
					}

					$searchCriteria[] = "BINARY UPPER(" . $value . ") LIKE '%" . $search . "%'";
				}
				$showActiveCriteria .= " AND " . implode(" OR ", $searchCriteria);
			}

			//	filter content based on search table
			$sql .= $showActiveCriteria;

			//	Restrict showing user information on every user
			if ($this->tablename=="adminusers" && $_SESSION["admin"]["account_type"]!="admin"){
				$restrictUsersCriteria = " AND id=".$_SESSION['admin']['id'];
				$sql .= $restrictUsersCriteria;
				
				$this->del_button = false;
			}
			
			if ($this->canMove)
				$orderClause = "pos";
			else {
				if (strlen($_GET['order'])>0){
					$this->def_orderby= $_GET['order'];

					$this->pos_orderby= $_GET['order_pos']=="DESC" ? "DESC" : "ASC";
				}

				$orderClause = $this->def_orderby . " " . $this->pos_orderby;
			}

			$sql .=  " ORDER BY " . $orderClause;

			//	set first result for current page
//			$first_result = (($this->page-1)*$this->rpp)+1;
			$first_result = (($this->page-1)*$this->rpp);
			//	set page number and count of records per page
			if ((int)$this->page>0 && (int)$this->rpp>0)
				$sql .= " LIMIT " . $first_result . "," . $this->rpp;
			$this->sql = $sql;
		}
	}

/**
 * Set presence or absance of search table, `New item` and `Delete` buttons
 * @param BOOL $search_table	- defines whether search table will be present 
 * @param BOOL $ni_button		- defines whether `New item` will be present
 * @param BOOL $del_button		- defines whether `Delete` will be present
 * @param BOOL $search_datetime	- defines whether -from -to dates will be present in search table
 * @return N/A
 */
	public function setEnvironment($search_table=true, $ni_button=true, $del_button=true, $search_datetime=false){
		$this->search_table = $search_table;
		$this->ni_button = $ni_button;
		$this->del_button = $del_button;
		$this->search_datetime = $search_datetime;
	}

/**
 * Sets columns for search. If not any search will be conducted in every single column!
 * @return N/A
 */
	public function setAllowSearchColumn($columns){
		if(is_array($columns))
		{
			foreach($columns AS $column)
			{
				$this->allowSearchColumns[] = $this->tablename . "." . $column;
			}
		} else {
			$this->allowSearchColumns[] = $this->tablename . "." . $columns;
		}
	}

	//	Make type of fields TABLENAME.FIELD
	private function define_fields(&$elem){
//		$elem = $this->tablename . "." . $elem;
		if ( strpos($elem, " ") ){
			$arr_elem = explode(" ", $elem);
			$elem = $arr_elem[0] . " " . $this->tablename . "." . $arr_elem[1];
		} else {
			$elem = $this->tablename . "." . $elem;
		}
	}

/**
 * 
 * @return unknown_type
 */
	public function get_head_elements(){
		$this->tpl->read("../templates/list_items.htm");

		if ($this->canMove)
			$this->tpl->add("head_elements", "CANMOVE", "1");
		else
			$this->tpl->add("head_elements", "CANMOVE", "0");

		return $this->tpl->get("head_elements");
	}

/**
 * 
 * @param unknown_type $part
 * @return unknown_type
 */
	public function get_list($part="main"){
		$this->setSQL();

		if ( strlen($this->sql)>0 ){
			$sql = $this->sql;
		} else {
			$sql = "SELECT * FROM " . $this->tablename;
		}

		$res = DBCON::query($sql);

//		$num_rowset = DBCON::fetch_row(DBCON::query("SELECT FOUND_ROWS()"));
		$num_rowset = DBCON::fetch_row(DBCON::query("SELECT COUNT(*) FROM " . $this->tablename));
		$this->num_rowset = $num_rowset[0];

		$this->tpl->addGlobal("EDIT", $this->actions['edit']);
		$this->tpl->addGlobal("ADD", $this->actions['add']);
		$this->tpl->addGlobal("DELETE", $this->actions['delete']);
		$this->tpl->addGlobal("MAIN_DIR", "../");

		$this->tpl->read("../templates/list_items.htm");

		$this->num_rows = DBCON::num_rows($res);

		if ($this->num_rows>0){
			foreach($this->columns AS $column=>$alias){
				$valias = !is_array($alias) ? $alias : $alias[0];

				//	don`t show column if there is no column name
				if (!strlen($valias)>0)
					continue;
				
				//	show down_arrow or up_arrow if column is ordered by user
				if ($this->def_orderby == $column){
					if ($this->pos_orderby!="DESC"){
						$column .= "&order_pos=DESC";
						$valias .= $this->down_arrow;
					} else {
						$valias .= $this->up_arrow;
					}
				}

				$this->tpl->add("hProperty", array(
					"CAN_MOVE"=> $this->canMove ? "" : "no_move",
					"VALUE"=>$column,
					"VALUE_ALIAS"=>$valias,
				));
				$this->tpl->parse("hProperty", true);
			}

			//	add HEADER columns for LEFT JOIN fields to HTML
			if (count($this->LJTable)>0){
				foreach($this->LJTable AS $LJTable=>$arr_LJFields){
					foreach($arr_LJFields AS $LJField=>$LJTitle){
						$valias = !is_array($LJTitle) ? $LJTitle : $LJTitle[0];

						//	show down_arrow or up_arrow if column is ordered by user
						if ($this->def_orderby == $LJTable . "_" . $LJField){
							if ($this->pos_orderby!="DESC"){
								$LJField .= "&order_pos=DESC";
								$valias .= $this->down_arrow;
							} else {
								$valias .= $this->up_arrow;
							}
						}

						$this->tpl->add("hProperty", array(
							"CAN_MOVE"=> $this->canMove ? "" : "no_move",
							"VALUE"=>$LJTable . "_" . $LJField,
							"VALUE_ALIAS"=>$valias,
						));
						$this->tpl->parse("hProperty", true);					
					}
				}
			}

			$i = 0;
			while($row=DBCON::fetch_assoc($res)){
				if ( !(int)$row['id']>0 || ($this->canMove && !(int)$row['pos']>0) ){
					echo "No ID or POS in list query!";
					exit;
				}

				$i++;
				$this->tpl->clear("property");
				$this->tpl->clear("moveNav");
				$this->tpl->clear("del_button");

				foreach($this->columns AS $column=>$alias){
					//	parse user defined function
					if (!is_array($alias)){

						//	don`t show column if there is no column name
						if (!strlen($alias)>0)
							continue;

						$value =$row[$column];
					} else if (!$alias[1]){
						$value =$row[$column];
					} else {
						eval("\$value = " . str_replace("ptrn", "$row[$column]", $alias[1]) . ";");
					}
					$this->tpl->add("property", array(
						"VALUE"=> $value,
						"ID"=> $row['id'],
					));
					$this->tpl->parse("property", true);
				}

				//	add values from LEFT JOIN fields to HTML
				if (count($this->LJTable)>0){
					foreach($this->LJTable AS $LJTable=>$arr_LJFields){
						foreach($arr_LJFields AS $LJField=>$LJTitle){

							//	make advantage of DISTINCT keyword
							if ( strpos($LJField, " ") ){
								$arr_elem = explode(" ", $LJField);
								$LJField = $arr_elem[1];
							}

							//	parse user defined function
							if (!is_array($LJTitle)){
								$value =$row[$LJTable . "_" . $LJField];
							} else if (!$LJTitle[1]){
								$value =$row[$LJTable . "_" . $LJField];
							} else {
								$val = $LJTable . "_" . $LJField;
								eval("\$value = " . str_replace("ptrn", "$row[$val]", $LJTitle[1]) . ";");
							}
							$this->tpl->add("property", array(
								"VALUE"=> $value,
								"ID"=> $row['id'],
							));
							$this->tpl->parse("property", true);
						}
					}
				}

				$del_button = "";
				if ($this->del_button>0){
					$del_button = $row['active']>0 ? "delete" : "undelete";
				}
				
//				$row_class= $i%2==0 ? "even" : "odd";
				$row_class = $this->getRowClass($row, $i);
				$this->tpl->add("item", array(
					"NUMBER"=> $i,
					"ID"=> $row['id'],
					"COLOR"=> $row['active']>0 ? $row_class : "disabled",
					"ACTION"=> $del_button,
				));

				// show arrows for moving rows up and down
				$this->tpl->add("moveNav", "ID", $row['id']);
				if ($this->canMove){
					$this->tpl->add("moveNav", "SHOW", "true");
					$this->tpl->add("moveNavHeader", "SHOW", "true");
				}
				$this->tpl->parse("item", true);
			}
		}

//		if ($this->ni_button>0){
//			$this->tpl->add("list", "ADD", "button");
//		}
		
		//	set pagination (prev, next, current and available pages)
		$this->set_pagination();

		if ($this->ni_button>0)
			$this->tpl->add("add_button", "SHOW", "true");

		return $this->tpl->get($part);
	}

/**
 * 
 * @return unknown_type
 */
	public function get_search_table(){
		$this->tpl->read("../templates/list_items.htm");

		$vals = array(10, 20, 50, 100, " ");
		foreach ($vals as $val){
			$this->tpl->add("options", array(
				"VALUE"		=>	$val,
				"OPTION"		=>	$val,
				"SELECTED"	=>	$val == $this->rpp ? "SELECTED" : "",
			));
			$this->tpl->parse("options", true);
		}

		$this->tpl->add("search_table", "SHOWALL", strlen($_POST["showAll"])>0 ? "checked" : "");

		$this->tpl->add("search_field", array(
			"TITLE"	=>	"Търсене",
			"NAME"	=>	"search",
			"VALUE"	=>	$_SESSION['search'],
		));
		$this->tpl->parse("search_field", true);

/*
		foreach($this->columns AS $column=>$alias){
			if (!is_array($alias) || $alias[count($alias)-2]){
				$this->tpl->add("search_field", array(
					"TITLE"	=>	is_array($alias) ? $alias[0] : $alias,
					"NAME"	=>	$column,
					"VALUE"	=>	$_SESSION['criteria'][$column],
				));
				$this->tpl->parse("search_field", true);
			}
		}

		//	add HEADER fields for LEFT JOIN fields
		if (count($this->LJTable)>0){
			foreach($this->LJTable AS $LJTable=>$arr_LJFields){
				foreach($arr_LJFields AS $column=>$alias){
					$column = $LJTable . "." . $column;
//					$converted_column = $this->convertDot($column);

					$this->tpl->add("search_field", array(
						"TITLE"	=>	is_array($alias) ? $alias[0] : $alias,
						"NAME"	=>	$this->convertDot($column),
						"VALUE"	=>	$_SESSION['criteria'][$column],
					));
					$this->tpl->parse("search_field", true);
				}
			}
		}
*/

		if ($this->search_datetime>0)
			$this->tpl->add("search_date_field", "SHOW", "true");

		return $this->search_table>0 ? $this->tpl->get("search_table") : "";
	}

/**
 * 
 * @return unknown_type
 */
	public function dispatcher(){
		$this->setSessionValues();
		if (!$this->canMove) return 0;

		if ( $_GET['action']=="goMove" ){
			$this->goMove();
			die ();
		}
	}

	function set_pagination(){
/*
		$this->num_rows;
		$this->rpp;
		$this->tpl;
		$this->page;
		$this->num_rowset;
*/
		if ($this->rpp>0)
		{
			$compensation= ($this->num_rowset % $this->rpp)>0 ? 1 : 0;
			$num_pages = (int)($this->num_rowset / $this->rpp) + $compensation;
		} else {
			$compensation = 0;
			$num_pages = 1;
		}

		if ($num_pages>1){
			$this->tpl->add("pagination", "SHOW", "TRUE");

			for ($i=0; $i<$num_pages; $i++){
				$this->tpl->add("page", array(
					"CLASS"	=> "",
					"VALUE"	=> "",
				));
				$this->tpl->parse("page", true);
			}
		}
/*
			<patTemplate:tmpl name="pagination" type="simplecondition" requiredvars="SHOW">
			<div class="pagination">
				<ul>	
					<patTemplate:tmpl name="prev_page" type="simplecondition" requiredvars="SHOW">
					<li class="disablepage"><a href="javascript: return false;">« Предишна</a></li>
					</patTemplate:tmpl>
					<patTemplate:tmpl name="page">
					<li {CLASS}>{VALUE}</li>
					</patTemplate:tmpl>
<!--
					<li class="currentpage">1</li>
					<li><a href="?page=1&sort=date&type=desc">2</a></li>
					<li><a href="?page=2&sort=date&type=desc">3</a></li>
//-->
					<patTemplate:tmpl name="next_page" type="simplecondition" requiredvars="SHOW">
					<li><a href="?page=1&sort=date&type=desc">Следваща »</a></li>
					</patTemplate:tmpl>
				</ul>
			</div>
			</patTemplate:tmpl>
*/
	}

	private function goMove(){
		if (!$this->canMove) return 0;

		$sql="SELECT pos FROM " . $this->tablename . " WHERE id=" . $_GET['id'];
		$res=DBCON::query($sql);
		list($position)=DBCON::fetch_row($res);

		$sql="";
		if ($_GET['dir']=="up"){
			$sql="SELECT MIN(pos) FROM " . $this->tablename . " WHERE pos>'" . $position . "' AND active>0";
			$res=DBCON::query($sql);
			if(DBCON::num_rows($res)>0)
				list($new_pos)=DBCON::fetch_row($res);
			else
				$new_pos = 0;

			if ($new_pos>$position){
				$sql="UPDATE " . $this->tablename . " SET pos=" . $position . " WHERE pos='" . $new_pos . "'";
				$res=DBCON::query($sql);

				$sql="UPDATE " . $this->tablename . " SET pos=" . $new_pos . " WHERE id='" . $_GET['id'] . "'";
				$res=DBCON::query($sql);
			}
		} else if ($_GET['dir']=="down" && $position>1) {
			$sql="SELECT MAX(pos) FROM " . $this->tablename . " WHERE pos<" . $position . " AND active>0";
			$res=DBCON::query($sql);
			if(DBCON::num_rows($res)>0)
				list($new_pos)=DBCON::fetch_row($res);
			else
				$new_pos = 0;

			//	change pos values
			if ($new_pos<$position){
				$sql="UPDATE " . $this->tablename . " SET pos=" . $position . " WHERE pos='" . $new_pos . "'";
				$res=DBCON::query($sql);

				$sql="UPDATE " . $this->tablename . " SET pos=" . $new_pos . " WHERE id='" . $_GET['id'] . "'";
				$res=DBCON::query($sql);
			}
		} else {
			//	means there is a problem - row is first or last one...
			return 1;
		}

		$this->callBack();

/*
		$sql="";
		if ($_GET['dir']=="up"){
			$sql="SELECT MAX(pos) FROM " . $this->tablename;
			$res=DBCON::query($sql);
			list($maxPos)=DBCON::fetch_row($res);
			$sql="";
			if ($position<$maxPos){
				$sql="UPDATE " . $this->tablename . " SET pos=$position WHERE pos='".($position+1)."'";
				$position++;
			}
		}
		else if ($_GET['dir']=="down" && $position>1) {
			$sql="UPDATE " . $this->tablename . " SET pos=$position WHERE pos='".($position-1)."'";
			$position--;
		}
		if ($sql!=""){
			DBCON::query($sql);
			$sql="UPDATE " . $this->tablename . " SET pos='$position' WHERE id=" . $_GET['id'];

			$res = DBCON::query($sql);
			if ($res)
				$this->callBack();
		}
*/
	}
/*
	function goDelete(){
		$id = $_GET['id'];
		if (!(int)$id>0)
			return false;

		$sql = "DELETE FROM " . $this->tablename . " WHERE id=" . $id;
		DBCON::query($sql);

		//	Refine articles position if position exists...
		if ($this->canMove){
			$sql = "SELECT pos FROM " . $this->tablename . "WHERE id=" . $id;
			$res = DBCON::query($sql);
			list($pos)=DBCON::fetch_row($res);

			if ((int)$pos>0){
				$sql = "UPDATE " . $this->tablename . " SET pos = pos -1 WHERE pos>" . $pos;
				DBCON::query($sql);
			}
		}
	}
*/
	private function callBack(){
		echo "<script>parent.swapArticlesRows('" . $_GET['id'] . "','" . $_GET['dir'] . "')</script>";
	}
/*
	function convertDot($input){
		if (strpos($input, "&dot"))
			return str_replace("&dot", ".", $input);
		else
			return str_replace(".", "&dot", $input);
	}
*/

	private function set_yes_no($ptrn){
		if ($ptrn>0) return YES;
		return NO;
	}

	private function reffer_array($arr, $ptrn){
		$res = CONFIG::VARIABLE($arr);
		if (strlen($res[$ptrn])>0)
			return $res[$ptrn];
		else
			return $this->set_yes_no($ptrn); 
	}

/**
 * Sets parameters for custom table row classes - where user wants given rows
 * of search table to have custom classes; In table row processing every condition
 * is checked and if comparison succeeds given class is taking place; Logic stops 
 * for given row on each successfull comparison;
 * @param $arrClasses	Settings array. Every element of this array must have 
 * following structure:
 * 					array(selector, "ptrn COMPARED_TO value", className),
 * where 	selector 	- name of extracted field to be compared to
 * 			COMPARED_TO	- compare operation - ==, <, >, =>, =< and so on
 * 			value	 	- right side comparison value
 * 			className	- className to be assigned to given table row if
 * 			comparison succeeds; could be blank - in that case default classes
 * 			(even and odd) will take place;
 * @return N/A
 */
	public function setRowClasses($arrClasses){
		$this->arrClasses = $arrClasses;
	}

	private function getRowClass($row, $i){
		if (count($this->arrClasses)>0){
			foreach($this->arrClasses AS $cond){
				$expr = str_replace("ptrn", $row[$cond[0]], $cond[1]);
				eval("\$condition= $expr;");
				if ((int)$condition)
					if (strlen($cond[2])>0)
						return $cond[2];
					else
						break;
			}
		}
		$row_class= $i%2==0 ? "even" : "odd";
		return $row_class;
	}
}
