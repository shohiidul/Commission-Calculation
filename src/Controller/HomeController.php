<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface; // services
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Controller\CommonController;
use App\Helpers\ReadFile;
use App\Helpers\ExchangeratesData;
use App\Helpers\Math;

/**
 * @Route("/",  name="home_")
 */
class HomeController extends CommonController
{

    /**
     * @Route("/", name="index", methods={"GET"})
     */
	public function index( LoggerInterface $logger ) : response
	{

	    $logger->info('I just got the logger');

        $html_contents = $this->renderView( 'home_index.html.twig', [] );

        return new Response($html_contents);
	}

    /**
     * @Route("/calculation", name="calculation", methods={"POST"})
     */
	public function calculation( Request $request ) : response
	{	   

		$html_contents = "Invalid try";

		if( $request->isMethod('POST') )
		{
			$token 		= $request->get('_csrf_token');
			$readFile 	= new ReadFile();

			if ($this->isCsrfTokenValid('calculation', $token) && (isset($_FILES['transaction']['size']) && $_FILES['transaction']['size']>0) && $readFile->isCSV( $_FILES['transaction']['type'] ) ) 
			{

				$data = $readFile->csvToArray( $_FILES['transaction']['tmp_name'] );

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

				}

				$html_contents = $this->renderView( 'home_calculation.html.twig', [ 'data' => $data ] );
			}
		}	

        return new Response($html_contents);
	}

}
