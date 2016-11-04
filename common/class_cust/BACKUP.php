<?php
//	####################################################################	//
//			Module purpose: To create periodic DB backup		//
//			Parameters:									//
//	####################################################################	//

class BACKUP{
	var $conf_maxQTY = 50;
	var $conf_periodHours = 2;
	var $conf_uploadsDir = BACKUPS_DIR;
	var $filename;

	function BACKUP($type=null){
		$sql = "SELECT COUNT(id) AS qty, ((UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(MAX(date_time)))/3600) AS elapsed_hours FROM backups";
		list($qty, $elapsed_hours) = DBCON::fetch_row(DBCON::query($sql));

		if ( !($qty>=$this->conf_maxQTY) || $elapsed_hours>$this->conf_periodHours ){
			$this->create_backup($qty);
		}

	}

	private function create_backup($qty){
		if ($qty >= $this->conf_maxQTY){
			//	delete the oldest backup file and its sql record
			$sql = "SELECT id, filename FROM backups ORDER BY id LIMIT 1";
			list($del_id, $del_filename)=DBCON::fetch_row(DBCON::query($sql));
			if (is_file($this->conf_uploadsDir . $del_filename))
				unlink($this->conf_uploadsDir . $del_filename);

			$sql = "DELETE FROM backups WHERE id=" . $del_id;
			DBCON::query($sql);
		}

		$time_start = microtime(true);
		$bytes = $this->backup_tables();

		$time_end = microtime(true);

		$time = $time_end - $time_start;
		if ( $bytes>0 ){
			$sql = "INSERT INTO backups (filename, date_time, backup_time) VALUES ('" . $this->filename . "', NOW(), " . sprintf("%01.5f", $time) . ")";
			DBCON::query($sql);
		}
	}

	private function backup_tables($tables = '*'){
		//get all of the tables
		if($tables == '*'){
			$tables = array();
			$result = DBCON::query('SHOW TABLES');
			while($row = DBCON::fetch_row($result)){
				$tables[] = $row[0];
			}
		} else {
			$tables = is_array($tables) ? $tables : explode(',',$tables);
		}

		//cycle through
		foreach($tables as $table){
			$result = DBCON::query('SELECT * FROM '.$table);
			$num_fields = DBCON::num_fields($result);

			$return.= 'DROP TABLE '.$table.';';
			$row2 = DBCON::fetch_row(DBCON::query('SHOW CREATE TABLE '.$table));
			$return.= "\n\n".$row2[1].";\n\n";

			for ($i = 0; $i < $num_fields; $i++){
				while($row = DBCON::fetch_row($result)){
					$return.= 'INSERT INTO '.$table.' VALUES(';
					for($j=0; $j<$num_fields; $j++){
						$row[$j] = addslashes($row[$j]);
						$row[$j] = str_replace("\n","\\n",$row[$j]);
						if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
						if ($j<($num_fields-1)) { $return.= ','; }
					}
					$return.= ");\n";
				}
			}
			$return.="\n\n\n";
		}

		//save file
		$this->filename = 'db-backup_' . date("dmYHis", gmmktime()) . '.sql';
		$handle = fopen($this->conf_uploadsDir . $this->filename,'w+');
		$bytes = fwrite($handle,$return);
		fclose($handle);

		return $bytes;
	}
}
?>