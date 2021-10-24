<?php


class TaqnyatSms {
    private $base;
    public $auth;
    public $result;
    private $method;
    private $json = array();
    public $error = "";

	/**
	 * Check if user information is it API or mobile and password
     * and set curl as default send  method
     *
     * @param string $auth The Authentication from taqnyat account
	 **/
    function __construct($auth=NULL) {
        if (!empty($auth)){
            $this->auth = $auth;
        }
        $this->method = 'curl';
        $this->base = 'https://api.taqnyat.sa';
    }

    /**
     * Check if user information is it API or mobile and password And if
     * this information is not empty set in variables for all api function other return error
     *
     * @param string $auth The Authentication from  taqnyat account
     * @return string $this->error If there is no error, it doesn't return anything
     **/
    public function setInfo($auth=NULL) {
		if(empty($auth)) {
			$this->error = 'Please Insert Authentication';	
		} elseif (!empty($auth)) {
			$this->auth = $auth;
		}
		return $this->error;
    }

    /**
     * Check if user information is not empty and
     * prepare information in array to Merge with another message data
     * you can call this function just in api function because it's private
     *
     **/
    private function checkUserInfo() {
		$this->json = array();
		$this->error = "";
        if (!empty($this->auth)) {
            $this->json=array("auth"=>$this->auth);
        } else {
            $this->error = 'Add Authentication';
        }
    }

    /**
     * Using  send method you'r selected in api function and
     * if doesn't match with any cases return error
     *
     * @param string $data Message data
     * @return string $this->error If any error found
     **/
    private function run($host,$path,$data='',$reqestType="POST") {
        switch ($this->method) {
            case 'curl':
                $ch = curl_init();
                $header = array('content-type: application/json', 'Authorization: Bearer '.$this->auth);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_URL, $host.$path);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch,  CURLOPT_CUSTOMREQUEST, $reqestType);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $this->result = curl_exec($ch);
                break;
            case 'fsockopen':
				$host=str_replace('https://','',$host);
                $host=str_replace('http://','',$host);
                $length = strlen($data);
				$fsockParameter = "$reqestType $path HTTP/1.0\r\n";
                $fsockParameter.= "Host: www.$host \r\n";
				$fsockParameter .= "Authorization: Bearer ".$this->auth."\r\n";
                $fsockParameter.= "Content-type: application/json \r\n";
                $fsockParameter.= "Content-length: $length \r\n\r\n";
                $fsockParameter .= "$data";
                $fsockConn = fsockopen('ssl://'. "www.$host", 443, $errno, $errstr, 30);
                fputs($fsockConn,$fsockParameter);
                $result = ""; 
                $clearResult = false; 
                while(!feof($fsockConn))
                {
                    $line = fgets($fsockConn, 10240);
                    if($line == "\r\n" && !$clearResult)
                    $clearResult = true;
                    
                    if($clearResult)
                        $result .= trim($line); 
                }
                break;
            case 'fopen':
                $contextOptions['http'] = array( 'method' => $reqestType, 'header'=>'Authorization: Bearer '.$this->auth."\r\n".'Content-type: application/x-www-form-urlencoded', 'content'=> $data, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $handle = fopen($host.$path, 'r', false, $contextResouce);
                $this->result = stream_get_contents($handle);
                break;
            case 'file':
                $contextOptions['http'] = array('method' => $reqestType, 'header'=>'Authorization: Bearer '.$this->auth."\r\n".'Content-type: application/x-www-form-urlencoded', 'content'=> $data, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $arrayResult = file($host.$path, FILE_IGNORE_NEW_LINES, $contextResouce);
                $this->result = $arrayResult[0];
                break;
            case 'file_get_contents':
                $contextOptions['http'] = array('method' => $reqestType, 'header'=>'Authorization: Bearer '.$this->auth."\r\n".'Content-type: application/x-www-form-urlencoded', 'content'=> $data, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $this->result = file_get_contents($host.$path, false, $contextResouce);
                break;
            default:
                $this->error = 'active one of the following portals (curl,fopen,fsockopen,file,file_get_contents) on server';
                return $this->error;
        }
        return $this->result;
    }


    /**
     * Send  message directly without separate message data
     * you can use to call function (sendMsg Or sendMsgWK).
     *
     * @param string $functionName Name of the function (required)
     * @param string $data Message data (required)
     * @return string $this->error If any error found
     **/
    public function callAPI ($functionName, $data,$port=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($port);
        if(empty($this->error)) {
            $this->json=array_merge($this->json,$data);
            $this->json['recipients']=explode(',',$this->json['recipients']);
            $this->json['lang']='3';
            $this->json=json_encode($this->json);
            switch ($functionName) {
                case 'sendMsg':
                        return $this->run('https://api.taqnyat.sa', '/v1/messages', $this->json);
                    break;
                default:
                    $this->error[] = 'method name not found You can select either (sendMsg).';
                    return $this->error;
            }
        }else{
            return $this->error;
        }
    }

    /**
     * Check if send method selected in function and
     * test send method if work or if method doesn't selected
     * test method  and choose which works
     *
     * @param string $method Send method
     * @return string $this->error If not empty method
     **/
	private function getSendMethod($method=NULL) {
		//Change Deafult Method
		if(!empty($method)){
			$this->method = strtolower($method);
		}
		//Check CURL
		if($this->method == 'curl') {
			if(function_exists("curl_init") && function_exists("curl_setopt") && function_exists("curl_exec") && function_exists("curl_close") && function_exists("curl_errno")) {
				return 1;
			} else {
				if(!empty($method)) {
					return $this->error = 'CURL is not supported';
				} else {
					$this->method = 'fsockopen';
				}
			}			
		}
		//Check fSockOpen
		if($this->method == 'fsockopen') {
			if(function_exists("fsockopen") && function_exists("fputs") && function_exists("feof") && function_exists("fread") && function_exists("fclose")) {
				return 1;
			} else {
				if(!empty($method)) {
					return $this->error = 'fSockOpen is not supported';
				} else {
					$this->method = 'fopen';
				}
			}			
		}
		//Check fOpen
		if($this->method == 'fopen') {
			if(function_exists("fopen") && function_exists("fclose") && function_exists("fread")) {
				return 1;
			} else {
				if(!empty($method)) {
					return $this->error = 'fOpen is not supported';
				} else {
					$this->method = 'file_get_contents';
				}
			}			
		}
		//Check File
		if($this->method == 'file') {
			if(function_exists("file") && function_exists("http_build_query") && function_exists("stream_context_create")) {
				return 1;
			} else {
				if(!empty($method)) {
					return $this->error = 'File is not supported';
				} else {
					$this->method = 'file_get_contents';
				}
			}			
		}
		//Check file_get_contents
		if($this->method == 'file_get_contents') {
			if(function_exists("file_get_contents") && function_exists("http_build_query") && function_exists("stream_context_create")) {
				return 1;
			} else {
				if(!empty($method)) {
					return $this->error = 'file_get_contents is not supported';
				} else {
					$this->method=NULL;
				}
			}			
		}				
    }

    /**
     * Send message
     *
     * @param string $body (required)
     * @param string $recipients Numbers to send (between each number comma ",")(required)
     * @param string $sender Name of message sender (required)
     * @param integer $scheduled Date to send message like this 6/30/2017 17:30:00
     * @param string $method Send method
     * @return string $this->error If any error found
     */
    public function sendMsg($body, $recipients, $sender,$smsId='',$scheduled='',$deleteId='',$method=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($method);
        if(empty($this->error)) {
            $data = array(
		        'recipients'=>$recipients,
                	'sender'=>$sender,
                	'body'=>$body,
		    	'smsId'=>$smsId,
                	'scheduledDatetime'=>$scheduled,
		    	'deleteId'=>$deleteId,
            );
            $this->json =  $data;
            $this->json = json_encode($this->json);
			
            return $this->run($this->base,'/v1/messages',$this->json);
        }
        return $this->error;
    }

    /**
     * Get send status
     *
     * @param string $method Send method
     * @return string $this->result
     **/
    public function sendStatus($method=NULL) {
        $this->getSendMethod($method);
        $data=array(
           
        );
        $this->json=array_merge($this->json,$data);
        $this->json=json_encode($this->json);
        return $this->run($this->base,'/system/status',$this->json,"GET");
    }

    /**
     * Get balance of taqnyat account
     *
     * @param string $method Send method
     * @return string $this->error If any error found
     * @return string $this->result If there is no error
     **/
    public function balance($method=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($method);
        if(empty($this->error)) {
            $this->json=json_encode($this->json);
            return $this->run($this->base,'/account/balance',$this->json,"GET");
        }
        return $this->error;
    }
	
	/**
     * Get senders of taqnyat account
     *
     * @param string $method Send method
     * @return string $this->error If any error found
     * @return string $this->result If there is no error
     **/
	
	public function senders($method=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($method);
        if(empty($this->error)) {
            $this->json=json_encode($this->json);
            return $this->run($this->base,'/v1/messages/senders',$this->json,"GET");
        }
        return $this->error;
    }

    /**
     * Delete message Using message deleteKey
     *
     * @param string $method Send method
     * @return string $this->error If any error found
     * @return string $this->result If there is no error
     **/
    public function deleteMsg($deleteKey,$method=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($method);
        if(empty($this->error)) {
            $data=array('');
            $this->json=array_merge($this->json,$data);
            $this->json=json_encode($this->json);
            return $this->run($this->base,'/v1/messages',$this->json,"DELETE");
        }
        return $this->error;
    }


}
