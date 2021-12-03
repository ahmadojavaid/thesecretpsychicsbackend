<?php 
namespace Modules\Client\Helper;

class APIHELPER {
	public $statusCode;
	public $statusMessage;
	public $result = null;

	public function __construct(){

	}

	public function responseParse(){
		$arr = [
			'statusCode' 	=> $this->statusCode,
			'statusMessage' => $this->statusMessage ,
			'data'			=> $this->result
		];
		return $arr;
	} 
}