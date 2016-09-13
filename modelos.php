<?php
Class puesto
{
	private $tps = 0;
	private $nroPuesto;
	private $tiempoOcioso = 0;
	private $inicioTO = 0;
	
	function __construct($nroPuesto,$hv)
	{
		$this->tps = $hv;
		$this->nroPuesto = $nroPuesto;
	}
	
	public function tps()
	{
		return $this->tps;
	}
	
	public function setTps($tps)
	{
		$this->tps = $tps;
	}

	public function tiempoOcioso()
	{
		return $this->tiempoOcioso;
	}

	public function setTiempoOcioso($time)
	{
		$this->tiempoOcioso = $time;
	}
	
	public function inicioTO()
	{
		return $this->inicioTO;
	}
	
	public function setInicioTO($time)
	{
		$this->inicioTO = $time;
	}
	
	public function nroPuesto()
	{
		return $this->nroPuesto;
	}
}


Class modelo
{
	private $varEstado = 0;
	private $varControl = 0;
	private $puestos;
	private $stll = 0;
	private $sts = 0;
	
	function __construct($cantPuestos,$hv)
	{
		for($i=0 ; $i < $cantPuestos ; $i++)
		{
				$this->puestos[] = new puesto($i,$hv);
		}
		$this->varControl = $cantPuestos;
		$this->varEstado = 0;
		$this->stll = 0;
	}
	
	public function varControl()
	{
		return $this->varControl;
	}
	
	public function varEstado()
	{
		return $this->varEstado;
	}

	public function sumarEstado()
	{
		$this->varEstado++;
	}
	
	public function restarEstado()
	{
		$this->varEstado--;
	}
	

	public function stll()
	{
		return $this->stll;
	}
	
	public function setSTLL($stll)
	{
		$this->stll = $stll;
	}
	
	public function sts()
	{
		return $this->sts;
	}
	
	public function setSTS($sts)
	{
		$this->sts = $sts;
	}

	
	public function puestos()
	{
		return $this->puestos;
	}

	public function buscarMenorTPS()
	{
		
		$minVal = null;
		$puestoElegido = null;
		foreach($this->puestos as $puesto)
		{
			if($minVal == null)
			{
				$minVal = $puesto->tps();
				$puestoElegido = $puesto;
			}
			else
			{
				if( $puesto->tps() < $minVal)
				{
					$minVal = $puesto->tps();
					$puestoElegido = $puesto;
				}
			}
		}
		
		return $puestoElegido;
	}

	public function buscarPuestoLibre($hv)
	{
		$puestosLibres = array();
		foreach($this->puestos as $puesto)
		{
			if($puesto->tps() == $hv)
				$puestosLibres[] = $puesto;
		}
		
		$index = rand(0,count($puestosLibres) -1);
		return $this->puestos[$index]->nroPuesto(); 
	}
	
	public function resultados($NT,$time)
	{
		foreach($this->puestos() as $puesto)
		{
			$nroPuesto = $puesto->nroPuesto() + 1;
			echo "<br> PTO puesto numero $nroPuesto : ".  ( ($puesto->tiempoOcioso() / $time) * 100 ). "</br>";
		}
		//echo "<br>STS : ".$this->sts."</br>";
		//echo "<br>STL : ".$this->stll."</br>";
		 
		//echo "Promedio de permanencia en el sistema: " . var_dump( ($this->sts - $this->stll ) / $NT ). "\n";
	}
	
}


?>