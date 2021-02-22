<?php 
declare(strict_types=1);

namespace App\Helpers;

use App\Helpers\WeekDays;

class Math
{

	private $deposite_charge 			= 0.03;
	private $business_withdraw_charge 	= 0.5;
	private $private_withdraw_charge 	= 0.3;
	private $charge_free_amount 	    = 1000;

	/**
	 * [calculatePercent]
	 * @param  float  $amount [total amount to be calculate]
	 * @param  float  $rate   [percent rate]
	 * @return float         [calculate rate]
	 */
	public function calculatePercent( float $amount, float $rate )
	{
		return (( $amount/100 ) * $rate);
	}

	/**
	 * [roundUpPrice is for round up commission value upto 2 decimel]
	 * @param  float  $value [amount]
	 * @return [float]       [amount]
	 */
	public function roundUpPrice( float $value, $precision=2 ) : float
	{
	    $offset = 0.5;
	    if ($precision !== 0)
	        $offset /= pow(10, $precision);
	    $final = round($value + $offset, $precision, PHP_ROUND_HALF_DOWN);
	    return ($final == -0 ? 0 : $final);
	}


	public function calculateDepositeCharge( float $amount, string $currency, array $exchangerates=array() )
	{
		return $this->roundUpPrice( $this->calculatePercent( $amount, $this->deposite_charge ) );
	}

	public function calculateBusinessWithdraw( float $amount, string $currency, array $exchangerates=array() )
	{
		return $this->roundUpPrice( $this->calculatePercent( $amount, $this->business_withdraw_charge ) );
	}

	public function calculatePrivateWithdraw( float $amount, string $currency, array $exchangerates=array() )
	{
		return $this->roundUpPrice( $this->calculatePercent( $amount, $this->private_withdraw_charge ) );
	}

	/**
	 * [setUniUserArray is a method for setting array values for calculating private user withdraw]
	 * @param int    $index   [for to ser array index no]
	 * @param float  $amount  [amout for calculation]
	 * @param string $date    [transaction date]
	 * @param int    $day_num [day num of the week by given date]
	 */
	public function setUniUserArray( int $index, float $amount, string $date, int $day_num  ) : array
	{
		$data[ $index ]['amount'] = $amount;
		$data[ $index ]['date']   = $date;
		$data[ $index ]['day']    = $day_num;

		return $data;
	}

	/**
	 * [calculateCharge is a method for calculating all type charge]
	 * @param  array  $data          [CSV input data]
	 * @param  array  $exchangerates [exchange rate list]
	 * @return array [calculated array set]
	 */
	public function calculateCharge( array $data, array $exchangerates=array() ) : array
	{
		$week_days 		 = new WeekDays();
		$private_clients_free_limit = array();

		foreach ( $data as $key => $row ) 
		{

			$row[4] 		= $this->roundUpPrice( floatval(number_format(floatval($row[4]), 10, '.', '')) );
			$data[$key][4] 	= $row[4];

			$charge_free_amount = $this->charge_free_amount;
			if( $row[5]!='EUR' )
			{
				$charge_free_amount = $row[4] * $exchangerates['rates'][ $row[5] ];
			}

			if( $row[3]=='deposit'  )
			{				
				$data[$key][6] = $this->calculateDepositeCharge( $row[4], $row[5], $exchangerates );
			}
			else if( $row[3]=='withdraw' && $row[2]=='business' )
			{
				$data[$key][6] = $this->calculateBusinessWithdraw( $row[4], $row[5], $exchangerates );
			}
			else if( $row[3]=='withdraw' && $row[2]=='private' )
			{
				$data[$key][6] = 0;

				$day_name = $week_days->getDayName( $row[0] );
				$day_num  = $week_days->getDayNum( $day_name );

				if( !isset( $private_clients_free_limit[$row[1]] ) ) 
				{
					$private_clients_free_limit[$row[1]] = array();

					$private_clients_free_limit[$row[1]]['limit']  = 1;
					$private_clients_free_limit[$row[1]]['data']   = $this->setUniUserArray( $key, $row[4], $row[0], $day_num  );

				}
				else 
				{
					$private_clients_free_limit[$row[1]]['limit']  += 1;
					$private_clients_free_limit[$row[1]]['data']   = array_merge( $private_clients_free_limit[$row[1]]['data'] , 
						 														  $this->setUniUserArray( $key, $row[4], $row[0], $day_num  ) 
						 														);
				}

				$prev_row    = array();
				$current_row = array();

				if( $private_clients_free_limit[$row[1]]['limit']==1 )
				{
					$amount = floatval( $row[4] - $charge_free_amount );

					if( $amount>0 )
					{
						$data[$key][6] = $this->calculatePrivateWithdraw( $amount, $row[5], $exchangerates );
					}
				}
				else
				{
					$user_data  = array_values($private_clients_free_limit[$row[1]]['data']);
					$amount     = 0;
					$last_index = count( $user_data ) - 1;

					$prev_data     = $user_data[ $last_index - 1 ];
					$currrent_data = $user_data[ $last_index ];

					$date_diff     = $week_days->date_diff( $prev_data['date'], $currrent_data['date'] );

					if( $date_diff > 7 || ($prev_data['day'] > $currrent_data['day']) ) 
					{
						$private_clients_free_limit[$row[1]] = array();

						$private_clients_free_limit[$row[1]]['limit']  = 1;
						$private_clients_free_limit[$row[1]]['data']   = $this->setUniUserArray( $key, $row[4], $row[0], $day_num  );
						
						$amount = floatval( $row[4] - $charge_free_amount );

						if( $amount>0 )
						{
							$data[$key][6] = $this->calculatePrivateWithdraw( $amount, $row[5], $exchangerates );
						}						
					}
					else if( $private_clients_free_limit[$row[1]]['limit'] <= 3 )
					{
						$amount = floatval( $row[4] - $charge_free_amount );

						if( $amount>0 )
						{
							$data[$key][6] = $this->calculatePrivateWithdraw( $amount, $row[5], $exchangerates );
						}
					}
					else
					{
						$amount = $row[4];

						if( $amount>0 )
						{
							$data[$key][6] = $this->calculatePrivateWithdraw( $amount, $row[5], $exchangerates );
						}						
					}

				}

			}
			else
			{
				$data[$key][6] = 0;
			}
		}
				
		return $data;

	}

}