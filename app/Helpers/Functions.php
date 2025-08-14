<?php
use App\Models\ErroLog;
use App\Models\RecordLog;

function __convert_value_bd($valor){
	if(str_contains((string)$valor, ".") && str_contains((string)$valor, ",")){
		$valor = str_replace('.', '', $valor);
	}
	return str_replace(",", ".", $valor);
}

function __moeda($valor, $casas_decimais = 2){
	return number_format((float)$valor, $casas_decimais, ',', '.');
}

//  function __qtd_carga($valor, $casas_decimais = 4){
//  	return number_format($valor, $casas_decimais, ',', '.');
//  }

function __estoque($valor, $casas_decimais = 0){
	return number_format($valor, $casas_decimais, ',', '.');
}

function __data_pt($data, $hora = true){
	if($hora){
		return \Carbon\Carbon::parse($data)->format('d/m/Y H:i');
	}else{
		return \Carbon\Carbon::parse($data)->format('d/m/Y');
	}
}

function __valida_objeto($objeto){
	$usr = session('user_logged');
	if(!isset($objeto['empresa_id'])){
		return true;
	}
	if($objeto['empresa_id'] == $usr['empresa']){
		return true;
	}else{
		return false;
	}
}

function __array_select2($data){
	$r = [];
	foreach($data as $d){
		$r[$d] = $d;
	}
	return $r;
}

function __saveLogError($error, $empresa_id){
	ErroLog::create([
		'arquivo' => $error->getFile(),
		'linha' => $error->getLine(),
		'erro' => $error->getMessage(),
		'empresa_id' => $empresa_id
	]);
}

function __saveLog($record){
	RecordLog::create($record);
}

function erroFull($e){
	return [
		'file' => $e->getFile(),
		'line' => $e->getLine(),
		'message' => $e->getMessage(),
	];
}

