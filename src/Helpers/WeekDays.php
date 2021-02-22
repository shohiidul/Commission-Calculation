<?php 

namespace App\Helpers;

class WeekDays
{

	public function date_diff(string $start_date, string $end_date) : int
	{
		$start_date = date('Y-m-d', strtotime($start_date));
		$end_date   = date('Y-m-d', strtotime($end_date));

		$date1 = new \DateTime( $start_date );
		$date2 = new \DateTime( $end_date );

		$interval = $date1->diff($date2);
		
		return $interval->days;
	}

	public function getDayName( string $weekday_name ) : string
	{
		return date('l', strtotime( $weekday_name ));
	}
	
	public function getDayNum(string $weekday_name) : int
	{
		return date('N', strtotime($weekday_name));
	}


}