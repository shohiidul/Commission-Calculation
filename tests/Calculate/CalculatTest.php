<?php 

namespace App\Tests\Calculate;

use App\Helpers\ReadFile;
use App\Helpers\ExchangeratesData;
use App\Helpers\Math;
use PHPUnit\Framework\TestCase;

class CalculatTest extends TestCase
{
    public function testAdd()
    {
    	$file_path  = __DIR__ . "/transactions02.csv";
    	$readFile 	= new ReadFile();

    	$data 		= $readFile->csvToArray( $file_path );
    	$assert 	= true;

		if( count($data)>0 )
		{

			$exchangeratesData 	= new ExchangeratesData();
			$rates 				= $exchangeratesData->getRates();
			$exchangerates 		= array();

			if( $rates['flag']==true )
			{
				$exchangerates = $rates['data'];
			}

			$mathData 	= new Math();
			$data 		= $mathData->calculateCharge( $data, $exchangerates );

			$comm = array( 0.61, 0, 0, 0.3, 0.9, 4.2, 0.06 , 1.5 , 0, 0, 0, 0.3 , 3 , 0 , 0.9 , 0 , 3 );

			foreach ( $data as $key => $row ) 
			{
				if( $row[6]!=$comm[$key] ) 
				{
					$assert = false;
				}	
			}

			if( count($data)!=17 )
			{
				$assert = false;
			}	

		}    	

        $this->assertEquals( true, $assert );
    }
}

// php bin/phpunit tests/Calculate/CalculatTest.php
