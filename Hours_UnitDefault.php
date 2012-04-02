<?php

//require_once(dirname(__FILE__)."/DbObject.php");
//require_once(dirname(__FILE__)."/Semester.php");

class Hours_UnitDefault extends DbObject
{

    public static $tableName = "hours.dbo.unit_defaults";
    public $exists = false;

    /** [[Column=unit_default_id, DataType=int, Description=Unit Default, ReadOnly=true]] */
    public $unit_default_id;

    /** [[Column=semester_id, DataType=int, Description=Semester, Required=true]] */
    public $semester_id;

    /** [[Column=label, DataType=varchar, Description=Label, MaxLength=200, Required=true]] */
    public $label;

    /** [[Column=note, DataType=varchar, Description=Note]] */
    public $note;

    /** [[Column=unit_id, DataType=int, Description=Unit]] */
    public $unit_id;

    /** [[Column=mon_start, DataType=varchar, Description=Mon Start, MaxLength=50]] */
    public $mon_start;

    /** [[Column=mon_end, DataType=varchar, Description=Mon End, MaxLength=50]] */
    public $mon_end;

    /** [[Column=tue_start, DataType=varchar, Description=Tue Start, MaxLength=50]] */
    public $tue_start;

    /** [[Column=tue_end, DataType=varchar, Description=Tue End, MaxLength=50]] */
    public $tue_end;

    /** [[Column=wed_start, DataType=varchar, Description=Wed Start, MaxLength=50]] */
    public $wed_start;

    /** [[Column=wed_end, DataType=varchar, Description=Wed End, MaxLength=50]] */
    public $wed_end;

    /** [[Column=thu_start, DataType=varchar, Description=Thu Start, MaxLength=50]] */
    public $thu_start;

    /** [[Column=thu_end, DataType=varchar, Description=Thu End, MaxLength=50]] */
    public $thu_end;

    /** [[Column=fri_start, DataType=varchar, Description=Fri Start, MaxLength=50]] */
    public $fri_start;

    /** [[Column=fri_end, DataType=varchar, Description=Fri End, MaxLength=50]] */
    public $fri_end;

    /** [[Column=sat_start, DataType=varchar, Description=Sat Start, MaxLength=50]] */
    public $sat_start;

    /** [[Column=sat_end, DataType=varchar, Description=Sat End, MaxLength=50]] */
    public $sat_end;

    /** [[Column=sun_start, DataType=varchar, Description=Sun Start, MaxLength=50]] */
    public $sun_start;

    /** [[Column=sun_end, DataType=varchar, Description=Sun End, MaxLength=50]] */
    public $sun_end;
    // foreign key fields (will be populated upon request)

    public $unit;

    public function getUnit()
    {
        $this->unit = new Directory_Unit($this->unit_id);
    }

    /**
     * @var \Hours_Semester
     */
    public $semester;

    public function getSemester()
    {
        if (!isset($this->semester))
            $this->semester = new Hours_Semester($this->semester_id);
    }

    public $weekday_hash = array(
        0 => 'sun',
        1 => 'mon',
        2 => 'tue',
        3 => 'wed',
        4 => 'thu',
        5 => 'fri',
        6 => 'sat'
    );

    public function getClosedDays(){
        $closed_days = array();
        for($i = 0; $i < 7; $i++){
            if($this->{$this->weekday_hash[$i] . "_start"} == null){
                array_push($closed_days, $this->weekday_hash[$i]);
            }
        }

        return $closed_days;
    }

    public function getStartTimeStamp($weekday)
    {
        $start = $this->{$this->weekday_hash[$weekday] . "_start"};
        return strtotime(!empty($start) ? $start : null);
    }

    public function getEndTimeStamp($weekday)
    {
        $end = $this->{$this->weekday_hash[$weekday] . "_end"};
        return strtotime(!empty($end) ? $end : null);
    }

    public function fetchData($id)
    {
        //list($arg1, $arg2) = $args;
        $query = "SELECT * FROM " . self::$tableName . " WHERE unit_default_id=" . $id;

        $row = $this->db->getRow($query);

        if (count($row) > 0)
            $this->setPropertyValues($row);

        if ($this->unit_default_id > 0)
            $this->exists = true;
    }

    public function getUnitDefaults($args = array())
    {
        global $db;

        $query = "SELECT *
			FROM " . self::$tableName . " ud			
				INNER JOIN " . Hours_Semester::$tableName . " s ON ud.semester_id = s.semester_id 
			WHERE 1=1 
				" . (@$args['unit_default_id'] != '' ? " AND unit_default_id={$args['unit_default_id']} " : "") . "
				" . (@$args['semester_date'] != '' ? " AND s.semester_id IN (select TOP 1 semester_id from " . Hours_Semester::$tableName . " s where s.semester_start <= '{$args['semester_date']} 12:00:00' AND s.semester_end >= '{$args['semester_date']} 12:00:00') " : "") . "

				" . (@$args['date_NEEDS_MODIFICATION'] != '' ? " AND (start_date <= '{$args['date']} 23:59:59' AND end_date >= '{$args['date']} 00:00:00')" : "") . "
				
				" . (@is_array($args['date_range']) && count($args['date_range']) == 2 ?
            "	AND   (  (s.semester_start <= '{$args['date_range'][0]} 23:59:59'
					AND s.semester_end >= '{$args['date_range'][1]} 00:00:00')
					or (s.semester_start BETWEEN '{$args['date_range'][0]} 00:00:00' AND '{$args['date_range'][1]} 23:59:59')
					or (s.semester_end BETWEEN '{$args['date_range'][0]} 00:00:00' AND '{$args['date_range'][1]} 23:59:59'))" : "") . "
				" . (@$args['semester_id'] != '' ? " AND s.semester_id={$args['semester_id']} " : "") . "
				" . (@$args['label'] != '' ? " AND label='{$args['label']}' " : "") . "
				" . (@$args['note'] != '' ? " AND note='{$args['note']}' " : "") . "
				" . (@$args['unit_id'] != '' ? " AND unit_id={$args['unit_id']} " : "") . "

				" . (!empty($args['unit_ids']) && @is_array($args['unit_ids']) ? " AND unit_id IN (" . implode(",", $args['unit_ids']) . ") " : "") . "

				" . (!empty($args['library_unit_ids']) && @is_array($args['library_unit_ids']) ? " AND (unit_id IN (" . implode(",", $args['library_unit_ids']) . ") OR unit_id IS NULL)" : "") . "

				" . (@$args['mon_start'] != '' ? " AND mon_start='{$args['mon_start']}' " : "") . "
				" . (@$args['mon_end'] != '' ? " AND mon_end='{$args['mon_end']}' " : "") . "
				" . (@$args['tue_start'] != '' ? " AND tue_start='{$args['tue_start']}' " : "") . "
				" . (@$args['tue_end'] != '' ? " AND tue_end='{$args['tue_end']}' " : "") . "
				" . (@$args['wed_start'] != '' ? " AND wed_start='{$args['wed_start']}' " : "") . "
				" . (@$args['wed_end'] != '' ? " AND wed_end='{$args['wed_end']}' " : "") . "
				" . (@$args['thu_start'] != '' ? " AND thu_start='{$args['thu_start']}' " : "") . "
				" . (@$args['thu_end'] != '' ? " AND thu_end='{$args['thu_end']}' " : "") . "
				" . (@$args['fri_start'] != '' ? " AND fri_start='{$args['fri_start']}' " : "") . "
				" . (@$args['fri_end'] != '' ? " AND fri_end='{$args['fri_end']}' " : "") . "
				" . (@$args['sat_start'] != '' ? " AND sat_start='{$args['sat_start']}' " : "") . "
				" . (@$args['sat_end'] != '' ? " AND sat_end='{$args['sat_end']}' " : "") . "
				" . (@$args['sun_start'] != '' ? " AND sun_start='{$args['sun_start']}' " : "") . "
				" . (@$args['sun_end'] != '' ? " AND sun_end='{$args['sun_end']}' " : "") . "
			ORDER BY label";

        //echo $query;

        $results = $db->getAll($query);

        $items = array();
        foreach ($results as $r) {
            $obj = new Hours_UnitDefault();
            $obj->setPropertyValues($r);
            $obj->exists = true;

            $obj->semester = new Hours_Semester();
            $obj->semester->setPropertyValues($r);
            $obj->semester->exists = true;

            array_push($items, $obj);
        }

        return $items;
    }

    //get unit default and semesters information by joining two tables
    public function getUnitDefaultsSemesters()
    {
        global $db;

        $query = "SELECT *
			FROM unit_defaults hd
				LEFT JOIN semesters hs ON hd.semester_id = hs.semester_id 
			WHERE 1=1 
				" . (isset($_parameters['unit_id']) ? " AND unit_id IN (" . implode(', ', $_parameters['unit_id']) . ") " : "") . "
				" . (@$args['unit_default_id'] != '' ? " AND semester_id={$args['semester_id']} " : "") . "
				" . (@$args['semester_date'] != '' ? " AND semester_id IN (select TOP 1 semester_id from " . Hours_Semester::$tableName . " s where s.semester_start <= '{$args['semester_date']} 12:00:00' AND s.semester_end >= '{$args['semester_date']} 12:00:00') " : "") . "
				" . (@$args['semester_id'] != '' ? " AND semester_id={$args['semester_id']} " : "") . "
				" . (@$args['label'] != '' ? " AND label='{$args['label']}' " : "") . "
				" . (@$args['note'] != '' ? " AND note='{$args['note']}' " : "") . "
				" . (@$args['unit_id'] != '' ? " AND unit_id={$args['unit_id']} " : "") . "
				" . (@$args['mon_start'] != '' ? " AND mon_start='{$args['mon_start']}' " : "") . "
				" . (@$args['mon_end'] != '' ? " AND mon_end='{$args['mon_end']}' " : "") . "
				" . (@$args['tue_start'] != '' ? " AND tue_start='{$args['tue_start']}' " : "") . "
				" . (@$args['tue_end'] != '' ? " AND tue_end='{$args['tue_end']}' " : "") . "
				" . (@$args['wed_start'] != '' ? " AND wed_start='{$args['wed_start']}' " : "") . "
				" . (@$args['wed_end'] != '' ? " AND wed_end='{$args['wed_end']}' " : "") . "
				" . (@$args['thu_start'] != '' ? " AND thu_start='{$args['thu_start']}' " : "") . "
				" . (@$args['thu_end'] != '' ? " AND thu_end='{$args['thu_end']}' " : "") . "
				" . (@$args['fri_start'] != '' ? " AND fri_start='{$args['fri_start']}' " : "") . "
				" . (@$args['fri_end'] != '' ? " AND fri_end='{$args['fri_end']}' " : "") . "
				" . (@$args['sat_start'] != '' ? " AND sat_start='{$args['sat_start']}' " : "") . "
				" . (@$args['sat_end'] != '' ? " AND sat_end='{$args['sat_end']}' " : "") . "
				" . (@$args['sun_start'] != '' ? " AND sun_start='{$args['sun_start']}' " : "") . "
				" . (@$args['sun_end'] != '' ? " AND sun_end='{$args['sun_end']}' " : "") . "
			ORDER BY semester_start desc, semester_name, label";

        /* $query = "SELECT unit_default_id, label, semester_name, semester_start
            FROM  unit_defaults hd
            LEFT JOIN semesters hs ON hd.semester_id = hs.semester_id ";
            if (isset($_parameters['unit_id'])){
            $str = implode(', ', $_parameters['unit_id']);
            $query.= " WHERE unit_id IN ($str)";
            }
            $query .= " ORDER BY semester_start desc, semester_name, label"; */

        $results = $db->getAll($query);
        $items = array();
        foreach ($results as $r) {
            $obj = new Hours_UnitDefault();
            $obj->setPropertyValues($r);
            $obj->exists = true;

            $obj->semester = new Hours_Semester();
            $obj->semester->setPropertyValues($r);
            $obj->semester->exists = true;

            //$obj->unit_name = $r['unit_name'];
            array_push($items, $obj);
        }

        return $items;
    }

    // used by hours.php to get weekly unit default information in addition to semester information that the week period belongs to for the whole library or the first floor
    public function getWeeklyUnitDefaults($args = array())
    {
        global $db;

        $query = "SELECT *
			FROM (semesters s 
				LEFT OUTER JOIN unit_defaults as d on s.semester_id = d.semester_id)
			WHERE (
					(semester_start BETWEEN '" . $args['start'] . "' AND '" . $args['end'] . "') OR 
					(semester_end BETWEEN '" . $args['start'] . "' AND '" . $args['end'] . "') OR 
					('" . $args['start'] . "' >= semester_start AND '" . $args['end'] . "' <= semester_end)
				  ) 
				and (d.unit_id is null or d.label like '%first floor%')
			ORDER BY semester_start";

        $results = $db->getAll($query);
        $items = array();
        foreach ($results as $r) {
            $obj = new Hours_UnitDefault();
            $obj->setPropertyValues($r);
            $obj->exists = true;

            $obj->semester = new Hours_Semester();
            $obj->semester->setPropertyValues($r);
            $obj->semester->exists = true;

            array_push($items, $obj);
        }

        return $items;
    }

    /* used basically by home.php with the purpose to get unit_default and unit information within a semester for the whole library or the first floor by joining table unit_defaults and directory.dbo.units  */

    public function getUnitDefaultsDirectoryUnits($semester)
    {
        global $db;

        $query = "SELECT d.*, u.unit_name, d.note defaults_note
				FROM unit_defaults d 
					LEFT JOIN " . Directory_Unit::$tableName . " u ON u.unit_id = d.unit_id
				WHERE d.semester_id=" . (!isset($semester) || ($semester->semester_id == null) ? "''" : $semester->semester_id) . "
				ORDER BY u.unit_name";
        $results = $db->getAll($query);
        $items = array();
        foreach ($results as $r) {
            $obj = new Hours_UnitDefault();
            $obj->setPropertyValues($r);
            $obj->exists = true;
            $obj->unit_name = $r['unit_name'];
            array_push($items, $obj);
        }

        return $items;
    }

    //and (d.unit_id=null or u.unit_name like '%library caf%')
    /* used basically by home.php with the purpose to get unit_default and exception information  within a semester for the whole library or the first floor by joining table unit_defaults and exceptions and directory.dbo.units  */
    public function getUnitDefaultsExceptions($semester)
    {
        global $db;

        $query = "SELECT u.unit_name unit_name, e.exception_note, d.label, exception_id, start_date, end_date,is_open, e.exception_name exception_name
				FROM unit_defaults d JOIN exceptions e ON e.unit_default_id = d.unit_default_id 
				LEFT JOIN directory.dbo.units u ON u.unit_id = d.unit_id
				WHERE semester_id=" . (!isset($semester) || ($semester->semester_id == null) ? "''" : $semester->semester_id) . "
				and (d.unit_id=null or u.unit_name like '%first floor%')
				ORDER BY start_date, e.exception_name";
        $results = $db->getAll($query);
        //var_dump($query);
        $items = array();
        //var_dump($results);die;
        foreach ($results as $r) {
            $obj = new Hours_UnitDefault();
            $obj->setPropertyValues($r);
            $obj->exists = true;
            $obj->note = $r['exception_note'];
            $obj->unit_name = $r['unit_name'];
            $obj->exception_name = $r['exception_name'];
            $obj->start_date = $r['start_date'];
            $obj->end_date = $r['end_date'];
            $obj->is_open = $r['is_open'];

            array_push($items, $obj);
        }
        return $items;
    }

    public function getCount()
    {
        global $db;

        $query = "SELECT count(*) FROM " . self::$tableName;
        return $db->getOne($query);
    }

    public function save($delete = 0)
    {
        /*
            global $login_info;

            $user = $login_info->get_user_info();

            $this->modified_by = (isset($user['first_name']) ? $user['first_name'].' '.$user['last_name'] : $user['username']);
           */
        //$this->last_modified = date("Y-m-d G:i:s");
        //$this->deleted = ($delete == 1 || $delete === true ? '1' : '0');
        // create query
        $query = ($this->exists ? $this->updateString(self::$tableName, 'unit_default_id') : $this->insertString(self::$tableName));

        // prepare query
        $stmt = $this->db->prepare($query);

        // execute statement
        $result = & $this->db->execute($stmt);

        /* if (PEAR::isError($result)) {
            throw new Exception($result->getMessage());
            } */

        return $result;
    }

    public function delete($method = 'hard')
    {
        if ($method == 'soft')
            return $this->save(1);

        // create query
        $query = "DELETE FROM " . self::$tableName . " WHERE unit_default_id=" . $this->unit_default_id;

        // prepare query
        $stmt = $this->db->prepare($query);

        // execute statement
        $result = & $this->db->execute($stmt);

        /* if (PEAR::isError($result)) {
            throw new Exception($result->getMessage());
            } */

        return $result;
    }

    public function hasConflict(EventInterface $event)
    {

        $event_weekday = date('w', $event->getStartTimeStamp());

        $event_start_time = strtotime(date('h:i a', $event->getStartTimeStamp()));
        $event_end_time = strtotime(date('h:i a', $event->getEndTimeStamp()));

        $default_hours_start = $this->getStartTimeStamp($event_weekday);
        $default_hours_end = $this->getEndTimeStamp($event_weekday);

        return (empty($default_hours_start) || $event_start_time < $default_hours_start || $event_end_time > $default_hours_end);
    }

    public function getName()
    {
        return "Unit Default";
    }

    public function getId()
    {
        return $this->unit_default_id;
    }

    public function exists()
    {
        return !empty($this->unit_default_id);
    }

    public function getUpdateLink()
    {
        return "?view=add_unit_default&id={$this->getID()}";
    }


}
