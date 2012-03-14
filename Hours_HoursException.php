<?php
//require_once(dirname(__FILE__)."/DbObject.php");
//require_once(dirname(__FILE__)."/UnitDefault.php");

class Hours_HoursException extends DbObject implements EventInterface
{
    public static $tableName = "hours.dbo.exceptions";
    public $exists = false;

    /** [[Column=exception_id, DataType=int, Description=Exception, ReadOnly=true]]*/
    public $exception_id;

    /** [[Column=padding, DataType=varchar, Description=Padding, ReadOnly=true]]*/
    public $padding = 0;

    /** [[Column=unit_default_id, DataType=int, Description=Unit Default]]*/
    public $unit_default_id;

    /** [[Column=exception_name, DataType=varchar, Description=Exception Name, MaxLength=200]]*/
    public $exception_name;

    /** [[Column=exception_note, DataType=varchar, Description=Exception Note]]*/
    public $exception_note;

    /** [[Column=start_date, DataType=datetime, Description=Start Date, Required=true]]*/
    public $start_date;

    /** [[Column=end_date, DataType=datetime, Description=End Date, Required=true]]*/
    public $end_date;

    /** [[Column=is_open, DataType=bit, Description=Is Open, Required=true]]*/
    public $is_open;

    // foreign key fields (will be populated upon request)

    public $unitDefault;

    public function isClosedAllDay(){
        return ($this->isClosed() && $this->getStartTimeStamp('only_time') == strtotime('12:00:00 AM') && $this->getEndTimeStamp('only_time') == strtotime('11:59:00 PM'));
    }

    public function isOpen(){
        return $this->is_open;
    }

    public function isClosed(){
        return !$this->isOpen();
    }

    public function getStartTimeStamp($options = 'both')
        {
            switch ($options) {
                case 'both' :
                    return strtotime($this->start_date);
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
                    return strtotime($this->end_date);
                    break;
                case 'only_time' :
                    return ConflictUtility::convertTimeStampToTimeOnly($this->getEndTimeStamp());
                    break;
                case 'only_date' :
                    return ConflictUtility::convertTimeStampEndToDateOnly($this->getEndTimeStamp());
                    break;
            }
        }

    public function getUnitDefault()
    {
        $this->unitDefault = new Hours_UnitDefault($this->unit_default_id);
    }

    public function getStartDate()
    {
        return date('Y-m-d', strtotime($this->start_date));
    }

    public function getEndDate()
    {
        return date('Y-m-d', strtotime($this->end_date));
    }

    public function setStartDate($date){
        $this->start_date = $date;
    }

    public function setEndDate($date){
        $this->end_date = $date;
    }


    public function fetchData($id)
    {
        //list($arg1, $arg2) = $args;
        $query = "SELECT * FROM " . self::$tableName . " WHERE exception_id=" . $id;

        $row = $this->db->getRow($query);

        if (count($row) > 0)
            $this->setPropertyValues($row);

        if ($this->exception_id > 0)
            $this->exists = true;
    }

    // getExceptions(array('date'=>'2011-03-17'))
    // getExceptions(array('date_range'=>array('2011-03-10','2010-03-17')))
    public function getExceptions($args = array())
    {
        global $db;

        $query = "SELECT *
			FROM " . self::$tableName . " u
			" . (@$args['with_unit_defaults'] == true ?
            " INNER JOIN " . Hours_UnitDefault::$tableName . " ud ON u.unit_default_id = ud.unit_default_id" : "") . "
			WHERE 1=1
				" . (!empty($args['library_unit_ids']) && @$args['with_unit_defaults'] == true && @is_array($args['library_unit_ids']) ? " AND ( unit_id IN (" . implode(",", $args['library_unit_ids']) . ") OR unit_id IS NULL )" : "") . "


				" . (@$args['semester_id'] != '' ? " AND u.unit_default_id in (select unit_default_id from " . Hours_UnitDefault::$tableName . " where semester_id = {$args['semester_id']}) " : "") . "

				" . (!empty($args['semester_unit_ids']) && @is_array($args['semester_unit_ids']) ? " AND u.unit_default_id IN ( SELECT unit_default_id FROM " . Hours_UnitDefault::$tableName . " WHERE semester_id IN (" . implode(",", $args['semester_unit_ids']) . ")) " : "") . "

				" . (@$args['unit_default_id'] != '' ? " AND u.unit_default_id={$args['unit_default_id']} " : "") . "

				" . (!empty($args['unit_default_ids']) && @is_array($args['unit_default_ids']) ? " AND u.unit_default_id IN (" . implode(",", $args['unit_default_ids']) . ") " : "") . "


				" . (@$args['exception_id_not'] != '' ? " AND exception_id <> '{$args['exception_id_not']}' " : "") . "
				" . (@$args['exception_id'] != '' ? " AND exception_id='{$args['exception_id']}' " : "") . "
				" . (@$args['exception_name'] != '' ? " AND exception_name='{$args['exception_name']}' " : "") . "
				" . (@$args['exception_note'] != '' ? " AND exception_note='{$args['exception_note']}' " : "") . "
				" . (@$args['is_open'] != '' ? " AND is_open={$args['is_open']} " : "") . "
				" . (@$args['start_date'] != '' ? " AND start_date BETWEEN '{$args['start_date']}' AND '{$args['start_date']} 23:59:59'" : "") . "
				" . (@$args['end_date'] != '' ? " AND end_date BETWEEN '{$args['end_date']}' AND '{$args['end_date']} 23:59:59'" : "") . "

				" . (@$args['date'] != '' ? " AND (start_date <= '{$args['date']} 23:59:59' AND end_date >= '{$args['date']} 00:00:00')" : "") . "
				
				" . (@is_array($args['date_range']) && count($args['date_range']) == 2 ?
            " AND   (  (start_date <= '{$args['date_range'][0]} 23:59:59' AND end_date >= '{$args['date_range'][1]} 00:00:00')
														or (start_date BETWEEN '{$args['date_range'][0]} 00:00:00' AND '{$args['date_range'][1]} 23:59:59')
														or (end_date BETWEEN '{$args['date_range'][0]} 00:00:00' AND '{$args['date_range'][1]} 23:59:59')
														)" : "") . "
			ORDER BY start_date, exception_name";
        $results = $db->getAll($query);

        echo $query;

        //echo($query);

        $items = array();
        foreach ($results as $r) {
            $obj = new Hours_HoursException();
            $obj->setPropertyValues($r);
            $obj->exists = true;
            array_push($items, $obj);
        }

        return $items;
    }


    // get exception information within a week period for the whole library or the first floor
    public function getWeeklyExceptionInfo($args = array())
    {
        global $db;

        $query = "SELECT unit_id, e.*
			from (" . Hours_Semester::$tableName . " as e left outer join " . Hours_UnitDefault::$tableName . " as d on e.unit_default_id = d.unit_default_id)
			where (
				(start_date BETWEEN '" . $args[0] . "' AND '" . $args[1] . "')
				OR (end_date BETWEEN '" . $args[0] . "' AND '" . $args[1] . "')
				OR ('" . $args[0] . "' >= start_date AND '" . $args[1] . "' <= end_date)
				) 
				AND (d.unit_id is null or d.label like '%first floor%')
			order by start_date";
        $results = $db->getAll($query);
        $items = array();
        foreach ($results as $r) {
            $obj = new Hours_HoursException();
            $obj->setPropertyValues($r);
            $obj->exists = true;
            //$obj->label = @$r['label'];
            $obj->unit_id = @$r['unit_id'];
            //$obj->note = @$r['note'];
            array_push($items, $obj);
        }

        return $items;
        //var_dump()
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
        $query = ($this->exists ? $this->updateString(self::$tableName, 'exception_id') : $this->insertString(self::$tableName));

        // prepare query
        $stmt = $this->db->prepare($query);

        // execute statement
        $result =& $this->db->execute($stmt);

        /*if (PEAR::isError($result)) {
              throw new Exception($result->getMessage());
          }*/

        return $result;
    }

    public function delete($method = 'hard')
    {
        if ($method == 'soft')
            return $this->save(1);

        // create query
        $query = "DELETE FROM " . self::$tableName . " WHERE exception_id=" . $this->exception_id;

        // prepare query
        $stmt = $this->db->prepare($query);

        // execute statement
        $result =& $this->db->execute($stmt);

        /*if (PEAR::isError($result)) {
              throw new Exception($result->getMessage());
          }*/

        return $result;
    }

    public function hasConflict(EventInterface $pending_event)
    {
        if ($this->isOpen()) {
           return (ConflictUtility::isInDateRange($this, $pending_event) && ConflictUtility::isNotWithinOpenHours($this, $pending_event));

        } else {
            return ConflictUtility::isTimeCollision($this, $pending_event);
        }
    }
    
    public function getName(){
	    return $this->exception_name;
    }
    
    public function getDetails($format = 'g:ia'){
        if ($this->isClosedAllDay()) return "Closed All Day";
        $isopenstring =  $this->isOpen() ? "Open" : "Closed";
        return date( $format, $this->getStartTimeStamp()). " - " . date( $format, $this->getEndTimeStamp())." ($isopenstring)";
    }

    public function getType(){
        return "exception";
    }

    public function getId(){
            return $this->exception_id;
    }

    public function exists(){
            return isset($this->exception_id);
    }

    public function getUpdateLink(){
           return "?view=add_exception&id={$this->getID()}";
    }

    public function setPadding($padding)
    {
        $this->padding = $padding;
    }

    public function getPadding(){
        return $this->padding;
   }


}
