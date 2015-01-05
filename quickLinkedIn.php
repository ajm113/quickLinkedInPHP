<?php

class quickLinkedIn {
    private $m_key = "";    /* Your API KEY  */
    private $m_secret = ""; /* Your API SECRET  */
    
    /* EVERYTHING ELSE BELOW DOES NOT NEED TO BE CHANGED  */
    
    private $m_base_oauth_url = "https://www.linkedin.com/uas/";
    private $m_base_url = "https://api.linkedin.com/v1/";
    private $m_api_oath = "oauth2";
    private $m_curl_handler = NULL;
    private $m_agent_name = "Quick LinkedIn PHP";
    private $m_token = "";
    private $m_scope = "";
    private $m_redirect = "";
    private $m_use_array = TRUE;
    
    
    public $last_http_response = 0;
    
    function __construct($key = "", $secret = "") {
    
        if(!session_id())
        {
        	session_start();
        }
        
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
    
    	$this->m_scope = str_replace(' ', "%20", $scope);
    }
    
    public function setRedirect($url)
    {
    	$this->m_redirect = $url;
    }
    
    public function getAccessUrl()
    {
    	/* SETUP URL */
    	$code = uniqid('qli_', true);
    	 $direction_url = $this->m_base_oauth_url.$this->m_api_oath.'/authorization?response_type=code&client_id='.$this->m_key.'&scope='.$this->m_scope.'&state='.urlencode($code).'&redirect_uri='.urlencode($this->m_redirect);
        
        //Set session...
        $_SESSION['qli_csrf_code'] = $code;
        return $direction_url;
    }
    
    
    //If any arguments are NULL, we will automaticly '$_GET' them.
    public function auth_code($code = '', $state = '')
    {
    
    	//Check to make sure session is good...
    	if(!isset($_SESSION['qli_csrf_code']))
    	{	
    		return FALSE;
    	}
    	
    	$passedCode = $_SESSION['qli_csrf_code'];
    	unset($_SESSION['qli_csrf_code']);
    	
    	if(($code === '' && !isset($_GET['code'])) || ($state === '' &&  !isset($_GET['state'])))
    	{
    		return FALSE;
    	}
    	
    	if(empty($code))
    	{
    		$code = $_GET['code'];
    	}
    	
    	if(empty($state))
    	{
    		$state  = $_GET['state'];
    	}
    	
    	
    	if($state !== $passedCode)
    	{
    		return FALSE;
    	}
    	
    	//Generate URL to get token data...
    	 $direction_url = $this->m_base_oauth_url.$this->m_api_oath.'/accessToken?grant_type=authorization_code&code='.$code.'&client_id='.$this->m_key.'&client_secret='.$this->m_secret.'&redirect_uri='.urlencode($this->m_redirect);
    	curl_setopt($this->m_curl_handler, CURLOPT_URL, $direction_url);
    	
    	$result = curl_exec($this->m_curl_handler);
    	curl_setopt($this->m_curl_handler, CURLOPT_POST, 0);
        $this->last_http_response = curl_getinfo($this->m_curl_handler, CURLINFO_HTTP_CODE);
    
    	$response = json_decode($result, TRUE);
    	
    	if(!isset($response['access_token']))
    	{
    		return FALSE;
    	}
    	
    	$this->m_token = $response['access_token'];
    	return $this->m_token;
    }
    
    
    
    public function call($function)
    {
        $direction_url = $this->m_base_url.$function."?oauth2_access_token=".$this->m_token."&format=json";
        curl_setopt($this->m_curl_handler, CURLOPT_URL, $direction_url);    
        $result = curl_exec($this->m_curl_handler);
        $this->last_http_response = curl_getinfo($this->m_curl_handler, CURLINFO_HTTP_CODE);
        
        return $result;
    }
}
?>