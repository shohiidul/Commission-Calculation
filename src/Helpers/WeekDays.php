<?php 

namespace App\Helpers;

class WeekDays
{

	/**
	 * [date_diff is a method for find out the total number of days between two dates ]
	 * @param  string $start_date [start date]
	 * @param  string $end_date   [end date]
	 * @return integer  [total days]
	 */
	public function date_diff(string $start_date, string $end_date) : int
	{
		$start_date = date('Y-m-d', strtotime($start_date));
		$end_date   = date('Y-m-d', strtotime($end_date));

		$date1 = new \DateTime( $start_date );
		$date2 = new \DateTime( $end_date );

		$interval = $date1->diff($date2);
		
		return $interval->days;
	}

	/**
	 * [getDayName is method for to find out the day name of a given date]
	 * @param  string $date 		[date format]
	 * @return string  [name of the day]
	 */
	public function getDayName( string $date ) : string
	{
		return date('l', strtotime( $date ));
	}
	
	/**
	 * [getDayNum is a method for find out the day number of given day name]
	 * @param  string $weekday_name [name of day]
	 * @return int [day nmuber of the week]
	 */
	public function getDayNum(string $weekday_name) : int
	{
		return date('N', strtotime($weekday_name));
	}

}