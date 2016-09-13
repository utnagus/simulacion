<?php
include 'modelos.php';

define("HV","9999999999999");

//$time = "2016-06-01 09:00:00";
$time = 0;
$timeFinal =  $_GET["dias"] ;

$TPLL = $time +  intervaloDeArribo() ;
$NT = 0;

$etl = new modelo($_GET["ETL"],HV);
$back = new modelo($_GET["BACK"],HV);
$dm = new modelo($_GET["DM"],HV);


main();

function main()
{
	global $etl,$back,$dm, $TPLL,$time;
	
	//Elijo proximo evento
	$resultado = buscarMenorTPS($etl,$back,$dm);
	
	$puesto = $resultado["puesto"];
	$modelo = $resultado["modelo"];
	
	//if ($TPLL == HV ) die(var_dump($resultado));
	//$evaluacionFinal = $TPLL == HV &&  $puesto->tps() == HV ? true : false; 
	//echo "------------".$TPLL."------------".$puesto->tps();
	/*if ($TPLL == HV && $puesto->tps() == HV) 
	{
		var_dump($etl);
		echo "---------------";
		var_dump($back);
		echo "---------------";
		var_dump($dm);
		
		echo "-------------------ELEGIDO";
		var_dump($modelo); die;
	}*/
	if( $TPLL <= $puesto->tps()  && $TPLL <> HV)//&& !$evaluacionFinal )
	{
		//echo "$TPLL ------ $minTPS----------------------------------\n";
		
		$random = rand(0,100);
		if($random < 60)
		{
			procesarLlegada($etl,$etl->buscarPuestoLibre(HV) );
		}
		elseif($random < 90)
		{
			procesarLlegada($back,$back->buscarPuestoLibre(HV));
		}
		else
		{
			procesarLlegada($dm,$dm->buscarPuestoLibre(HV));
		}
	}
	else
	{ 
		//if ($TPLL == HV ){var_dump($modelo); die();} 
		//echo "bbbbbbbbbbbbb"; 
		
		procesarSalida($modelo,$puesto->tps(),$puesto->nroPuesto());
	}
	
	procesarFinal($etl,$back,$dm);

}

function procesarLlegada($modelo,$nroPuesto)
{
	global $time,$TPLL,$NT;

	$diff = $TPLL - $time;

	$time = $TPLL;
	$NT++;
	$ia = intervaloDeArribo();
	$TPLL = $time + $ia ;
	
	$modelo->setSTLL( $modelo->stll() + $TPLL );
	
	
	$modelo->sumarEstado();
	$puestos = $modelo->puestos();
	
	//Puedo atender llegada
	if( $modelo->varEstado() <= $modelo->varControl() )
	{
		$ta = tiempoDeAtencion();
		$puestos[$nroPuesto]->setTps( $time + $ta  );
		
		$tiempoOcioso = $time  - $puestos[$nroPuesto]->inicioTO() ;
				
		$puestos[$nroPuesto]->setTiempoOcioso(  $puestos[$nroPuesto]->tiempoOcioso() +  $tiempoOcioso   );
	}

//	var_dump($modelo); exit;

}

function procesarSalida($modelo,$tps,$nroPuesto)
{
		global $time;
		
		$time = $tps;

		$modelo->setSTS(  $modelo->sts() + $tps  );
		
		$modelo->restarEstado();

		$puestos = $modelo->puestos();
		
		if( $modelo->varEstado() >= $modelo->varControl() )
		{
			$ta = tiempoDeAtencion();
			$puestos[$nroPuesto]->setTps( $time + $ta  );
//			$puesto->setTps( tiempo($time ,$ta ) );
		}
		else
		{
			$puestos[$nroPuesto]->setInicioTO( $time );
			$puestos[$nroPuesto]->setTps( HV );
		}

		//var_dump($modelo); exit;
}


function procesarFinal($etl,$back,$dm)
{
	global $time,$timeFinal,$TPLL,$NT;

//	if($TPLL == HV){ print $time." -----------".$timeFinal; die; }
	
//print $time." -----------".$timeFinal;
	if( $time <= $timeFinal )
	{
		main();
	}
	else
	{
		if($TPLL == HV ){
		var_dump($etl->varEstado()); 
		var_dump($back->varEstado());
		var_dump($dm->varEstado());
		
		echo "------------------------------\n";
					
			
		}
		/*var_dump($etl->varEstado()); 
		var_dump($back->varEstado());
		var_dump($dm->varEstado());
		
		echo "------------------------------\n";
		*/
		
		if( $etl->varEstado() == 0 && $back->varEstado() == 0 && $dm->varEstado() == 0)
		{
			//CALCULOS
			
			echo "<br>Tiempo ocioso ETL : </br>";
			$etl->resultados($NT,$time);
			
			echo "<br>Tiempo ocioso BACKEND : </br>";
			$back->resultados($NT,$time);
			
			echo "<br>Tiempo ocioso DATA MINING : </br>";
			$dm->resultados($NT,$time);
			
			exit;
		}
		else
		{
	//		if($TPLL == HV) die("PARAA");
			$TPLL = HV;
			main();
		}
	}
}


function buscarMenorTPS($etl,$back,$dm)
{
	
	$modelos[] = $etl;
	$modelos[] = $back;
	$modelos[] = $dm;
	
	$puestos = array();
	$puestos[] = $etl->buscarMenorTPS();
	$puestos[] = $back->buscarMenorTPS();
	$puestos[] = $dm->buscarMenorTPS();
	
	$valore = array();
	foreach($puestos as $puesto)
	{
		$valores[] = $puesto->tps();
	}
	$min = min($valores);
	
	$index = array_search($min,$valores);
	
	$result["puesto"] = $puestos[$index];
	$result["modelo"] = $modelos[$index];
	return $result;
	/*
	$minTPS = $tps_e->tps();
	$modelo = null;
	$nroPuesto = null;
	
	if($tps_b->tps() < $minTPS)
	{
		$modelo = $back;
		$minTPS = $tps_b->tps();
		$nroPuesto = $tps_b->nroPuesto();
	}
	if($tps_d->tps() < $minTPS)
	{
		$modelo = $dm;
		$minTPS = $tps_d->tps();
		$nroPuesto = $tps_d->nroPuesto();
	}
	else
	{
		$modelo = $etl;
		$minTPS = $tps_e->tps();
		$nroPuesto = $tps_e->nroPuesto();
	}*/
}

function intervaloDeArribo()
{
	return rand(9,16);
}

function tiempoDeAtencion()
{
	return rand( 40, 60);
}




?>