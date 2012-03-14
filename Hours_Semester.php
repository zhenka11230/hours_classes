<?php

//require_once(dirname(__FILE__)."/DbObject.php");
//require_once("UnitDefault.php");
//require_once("HoursException.php");

class Hours_Semester extends DbObject implements EventInterface {

	public static $tableName = "hours.dbo.semesters";
	public $exists = false;

	/** [[Column=semester_id, DataType=int, Description=Semester, ReadOnly=true]] */
	public $semester_id;

    /** [[Column=padding, DataType=varchar, Description=Padding, ReadOnly=true]]*/
    public $padding = 0;

	/** [[Column=semester_name, DataType=varchar, Description=Semester Name, MaxLength=50, Required=true]] */
	public $semester_name;

	/** [[Column=semester_start, DataType=datetime, Description=Semester Start, Required=true]] */
	public $semester_start;

	/** [[Column=semester_end, DataType=datetime, Description=Semester End, Required=true]] */
	public $semester_end;

	/** [[Column=semester_note, DataType=varchar, Description=Semester Note]] */
	public $semester_note;
	// foreign key fields (will be populated upon request)

	public static $semesters = array('Fall', 'Winter Intersession', 'Spring', 'Summer');

	public function getStartDate() {

		return date('Y-m-d', strtotime($this->semester_start));
	}

	public function getEndDate() {
		return date('Y-m-d', strtotime($this->semester_end));
    }

   	public function getStartTimeStamp($options = 'both')
       {
           switch ($options) {
               case 'both' :
                   return strtotime($this->semester_start);
                   break;
               case 'only_time' :
                   return ConflictUtility::convertTimeStampToTimeOnly($this->getStartTimeStamp());
                   break;
               case 'only_date' :
                   return ConflictUtility::convertTimeStampStartToDateOnly($this->getStartTimeStamp());
                   break;
           }
       }

       public function getEndTimeStamp($options = 'both')
       {
           switch ($options) {
               case 'both' :
                   return strtotime($this->semester_end);
                   break;
               case 'only_time' :
                   return ConflictUtility::convertTimeStampToTimeOnly($this->getEndTimeStamp());
                   break;
               case 'only_date' :
                   return ConflictUtility::convertTimeStampEndToDateOnly($this->getEndTimeStamp());
                   break;
           }
    }

	public function fetchData($id) {
		//list($arg1, $arg2) = $args;
		$query = "SELECT * FROM " . self::$tableName . " WHERE semester_id=" . $id;

		$row = $this->db->getRow($query);

		if (count($row) > 0)
			$this->setPropertyValues($row);

		if ($this->semester_id > 0)
			$this->exists = true;
	}

	public function getSemesters($args=array()) {
		global $db;

		$query = "SELECT * 
			FROM " . self::$tableName . " 
			WHERE 1=1 
				" . (@$args['semester_name'] != '' ? " AND semester_name='{$args['semester_name']}' " : "") . "
				" . (@$args['semester_start'] != '' ? " AND semester_start BETWEEN '{$args['semester_start']}' AND '{$args['semester_start']} 23:59:59'" : "") . "
				" . (@$args['semester_end'] != '' ? " AND semester_end BETWEEN '{$args['semester_end']}' AND '{$args['semester_end']} 23:59:59'" : "") . "
				" . (@$args['date'] != '' ? " AND (semester_start <='{$args['date']} 00:00:00' AND semester_end >= '{$args['date']} 23:59:59')" : "") . "
				" . (@$args['semester_note'] != '' ? " AND semester_note='{$args['semester_note']}' " : "") . "
				" . (@$args['starts_after'] != '' ? " AND semester_start >'{$args['starts_after']}' " : "") . "
				" . (@$args['queried_semester'] != '' ? " AND semester_start <='{$args['queried_semester']}' AND semester_end >='{$args['queried_semester']}'" : "") . "
			ORDER BY semester_start DESC";
		$results = $db->getAll($query);

		$items = array();
		foreach ($results as $r) {
			$obj = new Hours_Semester();
			$obj->setPropertyValues($r);
			$obj->exists = true;
			array_push($items, $obj);
		}

		return $items;
	}

	public function getMinMaxSemester() {
		global $db;

		$query = "SELECT MIN(semester_start) as semester_start, MAX(semester_end) as semester_end 
				FROM semesters";
		$results = $db->getRow($query);

		$obj = new Hours_Semester();
		$obj->setPropertyValues($results);

		return $obj;
	}

	public function getCount() {
		global $db;

		$query = "SELECT count(*) FROM " . self::$tableName;
		return $db->getOne($query);
	}

	public function save($delete=0) {
		/*
		  global $login_info;

		  $user = $login_info->get_user_info();

		  $this->modified_by = (isset($user['first_name']) ? $user['first_name'].' '.$user['last_name'] : $user['username']);
		 */
		//$this->last_modified = date("Y-m-d G:i:s");
		//$this->deleted = ($delete == 1 || $delete === true ? '1' : '0');
		// create query
		$query = ($this->exists ? $this->updateString(self::$tableName, 'semester_id') : $this->insertString(self::$tableName));

		// prepare query
		$stmt = $this->db->prepare($query);

		// execute statement
		$result = & $this->db->execute($stmt);

		/* if (PEAR::isError($result)) {
		  throw new Exception($result->getMessage());
		  } */

		return $result;
	}

	public function delete($method='hard') {
		if ($method == 'soft')
			return $this->save(1);

		// create query
		$query = "DELETE FROM " . self::$tableName . " WHERE semester_id=" . $this->semester_id;

		// prepare query
		$stmt = $this->db->prepare($query);

		// execute statement
		$result = & $this->db->execute($stmt);

		/* if (PEAR::isError($result)) {
		  throw new Exception($result->getMessage());
		  } */

		return $result;
	}

	public function getName() {
		
	}

	public function getDetails() {
		
	}
	
	public function hasConflict(EventInterface $event){
		
	}

    public function setPadding($padding)
    {
        $this->padding = $padding;
    }

    public function getPadding(){
        return $this->padding;
    }

}
