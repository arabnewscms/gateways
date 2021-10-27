<?php

namespace Phpanonymous\Gateways\SMS;

use Illuminate\Support\Facades\Http;

//TODO:: implement interface to make easier for developer to add new gateways
//TODO:: handle errors methods

class ForJawaly {

	// Sandbox or Live Link
	private $link = 'http://www.4jawaly.net/api/';

	/**
	 * links method to collect and prepare all link with fawry
	 * @return array
	 */
	private $links = [];

	// Params  binding to Guzzel POST & GET
	private $params = [];


    public function __construct()
    {
        $this->config = (object) config('gateways.sms.for_jawaly');

        $this->params['sender']   = $this->config->sender;
		$this->params['username'] = $this->config->username;
		$this->params['password'] = $this->config->password;
		$this->params['unicode '] = 'E';
		$this->params['return']   = 'json';

        $this->links = [
			'SEND' => $this->link . 'sendsms.php',
			'BALANCE' => $this->link . 'getbalance.php',
		];
        
    }

    public function mobile($mobile): self{
		$this->params['numbers'] = is_array($mobile) ? implode(',', $mobile) : $mobile;
		return $this;
	}

	public function message($message): self{
		$this->params['message'] = $message;
		return $this;
	}

    public function send(){
        $response = Http::get( $this->links['SEND'] ,$this->params);  
        return $response->json();
    }
    
	public function balance() {
        $response = Http::get( $this->links['BALANCE'] ,$this->params);  
        return $response->json();
    }

}  