<?php 

namespace App\Helpers;

class ReadFile
{
	
	/**
	 * [isCSV method is for checking the mime type for uploaded calculation file]
	 * @param  string  $file_type   
	 * @return boolean         
	 */
	public function isCSV( string $file_type ) : bool
	{
		$mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');
		return in_array( $file_type ,$mimes );
	}

	/**
	 * [csvToArray method is for converting the csv data from array]
	 * @param  string $file_location [uploaded file locatin or file tamp path]
	 * @return [data]                [array]
	 */
	public function csvToArray( string $file_location ) : array
	{
		$data = array();
		$file = fopen( $file_location , 'r');
		while (($line = fgetcsv($file)) !== FALSE) 
		{
		  $data[] = $line;
		}
		fclose($file);

		return $data;
		
	}



}