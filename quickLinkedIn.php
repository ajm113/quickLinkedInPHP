<?php

class quickLinkedIn {
    private $m_key = "";    /* Your API KEY  */
    private $m_secret = ""; /* Your API SECRET  */
    
    /* EVERYTHING ELSE BELOW DOES NOT NEED TO BE CHANGED  */
    
    private $m_base_url = "https://www.linkedin.com/uas/";
    private $m_api_oath = "oauth2";
    private $m_curl_handler = NULL;
    private $m_agent_name = "Quick LinkedIn PHP";
    private $m_token = "";
    private $m_scope = "";
    private $m_redirect = "";
    private $m_use_array = TRUE;
    
    
    public $last_http_response = 0;
    
    function __construct($key = "", $secret = "") {
        $this->init($key, $secret);
    }
    
    function __destruct() {
        $this->clearAll();
    }
    
    protected function init($key = "", $secret = "")
    {
        if(!$this->_is_curl_installed())
        {
            throw new Exception('Curl is not installed! Please read the url: http://curl.haxx.se/libcurl/php/');
        }
        
        if(!empty($key))
        {
            $this->m_key = $key;
        }
        
        if(!empty($secret))
        {
            $this->m_secret = $secret;
        }
        
        //Ensure they key and secret isn't empty...
         if(empty($this->m_key) || empty($this->m_secret))
        {
            throw new Exception('Please enter in your key and secret into init or class. https://www.linkedin.com/secure/developer');
        }       
        
        
        //Initalize curl...
        if(!$this->m_curl_handler)
        {
            $this->m_curl_handler = curl_init();
        }
        
        curl_setopt($this->m_curl_handler, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->m_curl_handler, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($this->m_curl_handler, CURLOPT_USERAGENT, $this->m_agent_name);
        curl_setopt($this->m_curl_handler, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($this->m_curl_handler, CURLOPT_HEADER, 0);
    }
    
    //Ensure we have curl enabled on the server.
    private function _is_curl_installed() 
    {
        if  (in_array  ('curl', get_loaded_extensions())) 
        {
            return TRUE;
        }
        
        return FALSE;
    }
    
    //Dont need to call this since this auto destructs...
    public function clearAll()
    {        
        //Can we delete?
        if($this->m_curl_handler)
        {
            curl_close($this->m_curl_handler);
            $this->m_curl_handler = NULL;
        }
    }
    
    
    public function use_stdobject($enabled = FALSE)
    {
        $this->m_use_array = $enabled;
    }
    
    public function setAccessToken($token)
    {
    	$this->m_token = $token;
    }
    
    public function getAccessToken()
    {
    	return $this->m_token;
    }
    
    public function setScope($scope)
    {
    	$this->m_scope = $scope;
    }
    
    public function setRedirect($url)
    {
    	$this->m_redirect = $url;
    }
    
    public function getAccessUrl()
    {
    	/* SETUP URL */
    	 $direction_url = $this->m_base_url.$this->m_api_oath.'/authorization/?response_type=code
    	   &client_id='.$this->m_key.'
		   &scope='.urlencode($this->m_scope).'
		   &state='.urlencode(uniqid('qli_', true)).'
		   &redirect_uri='.urlencode($this->m_redirect);
		   
    	 curl_setopt($this->m_curl_handler, CURLOPT_URL, $direction_url);
 		$result = curl_exec($this->m_curl_handler);
        $this->last_http_response = curl_getinfo($this->m_curl_handler, CURLINFO_HTTP_CODE);
        
        return $result;
    }
    
    
    
    public function call($function, $data = NULL)
    {
        $direction_url = $this->m_base_url.$this->m_api_ver.'/'.$function;
        curl_setopt($this->m_curl_handler, CURLOPT_URL, $direction_url);
        
        $idata = $this->generate_auth();
        
        if(is_array($data))
        {   
           $idata = array_merge($idata, $data);
        }
        
        $fields_string = "";
        foreach($idata as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');
        
        if(count($idata) > 0)
        {
            curl_setopt($this->m_curl_handler, CURLOPT_POST, count($idata));
            curl_setopt($this->m_curl_handler, CURLOPT_POSTFIELDS, $fields_string);
        }
        
        $result = curl_exec($this->m_curl_handler);
        $this->last_http_response = curl_getinfo($this->m_curl_handler, CURLINFO_HTTP_CODE);
        
        return $this->xml_to_object($result);
    }
}
?>