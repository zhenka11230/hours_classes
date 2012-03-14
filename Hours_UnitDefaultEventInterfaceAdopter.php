<?php

//convert UnitDefaults into a event like object for reuse of the same functions that we use with Events
//in other words: adopts it to EventInterface for function reuse.
class Hours_UnitDefaultEventInterfaceAdopter implements EventInterface
{

    /**
     * @var ./Hours_UnitDefault
     */
    private $unit_default;

    /**
     * @param string semester or weekday.
     *
     * Get Start/End Date using the Semester start/end or start/end for that specific week day.
     */
    private $get_date_setting = 'semester';

    /**
     *
     * @var int numeric representation of a week day to get default hours by.
     */
//    private $week_day = 'unset';

    private $date = 'unset';

    /**
     *
     * @param ./Hours_UnitDefault $unit_default
     */
    function __construct(Hours_UnitDefault $unit_default)
    {
        $this->unit_default = $unit_default;
    }

    /**
     *
     * @param type $week_day int
     */
    public function treatAsEvent()
    {
        $this->get_date_setting = 'event';
    }

    public function treatAsSemester()
    {
        $this->get_date_setting = 'semester';
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

//    public function setWeekDay($week_day)
//    {
//
////        echo "CHANGINg WEEK DAY from: " . @$this->week_day . " to " . $week_day . "<br />";
//        //
//        //		if (!is_int($week_day)) {
//        //			die('Hours_UnitDefaultEventInterfaceAdopter::treatAsWeekDayEvent was not passed an integer as an argument');
//        //		}
//        $this->week_day = $week_day;
//    }

    public function getStartDate()
    {
        switch ($this->get_date_setting) {
            case 'semester':
                $this->assertSemesterExists();
                return $this->unit_default->semester->getStartDate();
                break;
            case 'event':
                $this->assertDateIsSet();
                return $this->date;
                break;
        }

    }

    public function getEndDate()
    {
        switch ($this->get_date_setting) {
            case 'semester':
                $this->assertSemesterExists();
                return $this->unit_default->semester->getEndDate();
                break;
            case 'event':
                $this->assertDateIsSet();
                return $this->date;
                break;
        }
    }

    public function getWeekDay()
    {
        $this->assertDateIsSet();
        return date('w', strtotime($this->date));
    }

    public function getStartTimeStamp($options = 'both')
    {
        switch ($options) {
            case 'both' :
                switch ($this->get_date_setting) {
                    case 'semester':
                        $this->assertSemesterExists();
                        return $this->unit_default->semester->getStartTimeStamp();
                        break;
                    case 'event':
                        if (!$this->isClosed()) {
                            $this->assertDateIsSet();
                            $time_str = date('H:i', $this->unit_default->getStartTimeStamp($this->getWeekDay()));
                            return strtotime($this->date . " " . $time_str);
                        } else {
                            return null;
                        }
                        break;
                }
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
                switch ($this->get_date_setting) {
                    case 'semester':
                        $this->assertSemesterExists();
                        return $this->unit_default->semester->getEndTimeStamp();
                        break;
                    case 'event':
                        if (!$this->isClosed()) {
                            $this->assertDateIsSet();
                            $time_str = date('H:i', $this->unit_default->getEndTimeStamp($this->getWeekDay()));
                            return strtotime($this->date . " " . $time_str);
                        } else {
                            return null;
                        }
                        break;
                }
                break;
            case 'only_time' :
                return ConflictUtility::convertTimeStampToTimeOnly($this->getEndTimeStamp());
                break;
            case 'only_date' :
                return ConflictUtility::convertTimeStampEndToDateOnly($this->getEndTimeStamp());
                break;
        }
    }

    //b/c php doesn't have object casting
    public function getUnitDefault()
    {
        return $this->unit_default;
    }

    public function assertSemesterExists()
    {
        if (!isset($this->unit_default->semester)) {
            die('Hours_UnitDefaultEventInterfaceAdopter was passed a UnitDefault obj without semester obj inside.');
        }
    }

    public function assertDateIsSet()
    {
        if ($this->date == 'unset') die('Hours_UnitDefaultEventInterfaceAdopter requires date to be set for this function.');
    }

//    public function assertWeekDayIsSet(){
//        if($this->week_day == 'unset') die('Hours_UnitDefaultEventInterfaceAdopter requires week_day to be set for this function.');
//    }

    public function hasConflict(EventInterface $pending_event)
    {
        return $this->unit_default->hasConflict($pending_event);
    }

    public function getName()
    {
        return $this->unit_default->label;
    }

    public function getDetails($format = 'g:ia')
    {
        if (!$this->isClosed()) return date($format, $this->getStartTimeStamp()) . " - " . date($format, $this->getEndTimeStamp()) . " (Open)";
        else return "Closed";
    }

    /**
     * @return string
     */
    public function getHours()
    {
        $start = $this->getStartTimeStamp();
        $end = $this->getEndTimeStamp();

        if ($this->isClosed()) {
            return 'Closed';
        } else {
            return date('h:m', $start) . " - " . date('h:m', $end);
        }
    }

    /**
     * @return bool
     */
    public function isClosed()
    {
        $start = $this->unit_default->getStartTimeStamp($this->getWeekDay());
        return empty($start);
    }

    public function getType()
    {
        return "unit default";
    }

    public function getId()
    {
        return $this->unit_default->{__FUNCTION__}();
    }

    public function exists()
    {
        return $this->unit_default->{__FUNCTION__}();
    }

    public function getUpdateLink()
    {
        return $this->unit_default->{__FUNCTION__}();
    }

    public function setPadding($padding)
    {
        $this->padding = $padding;
    }

    public function getPadding(){
        return $this->padding;
    }

    /** [[Column=padding, DataType=varchar, Description=Padding, ReadOnly=true]]*/
    public $padding = 0;


}
