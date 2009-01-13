<?php 
/*
  Class: GoogleCalendarWrapper
  Author: Skakunov Alex (i1t2b3@gmail.com)
  Date: 26.11.06
  Description: provides a simple tool to work with Google Calendar (add events currenly)
    You must define login and password.
    
    Class adds events into your main calendar by default.
    If you want to add events in other calendar, write its XML URL into "feed_url" property like this:

      $gc = new GoogleCalendarWrapper("email@gmail.com", "password");

      $gc->feed_url =
            "http://www.google.com/calendar/feeds/pcafiuntiuro1rs%40group.calendar.google.com/private-586fa023b6a7151779f99b/basic";
    Feel free to provide "basic" URL, it will be automatically converted to "full" one (prepare_feed_url() method)..
    How to get the XML URL: http://code.google.com/apis/gdata/calendar.html#get_feed
*/

class GoogleCalendarWrapper extends netHttp
{
  public $email;
  public $password;
  public $feed_host = "www.google.com";
  public $feed_url = "http://www.google.com/calendar/feeds/default/private/full";
  public $feed_path = "/calendar/feeds/default/private/full";
  public $host = "www.google.com";

  private $fAuth;
  private $isLogged = false;
  private $feed_path_prepared;
  
  function GoogleCalendarWrapper($email, $password)
  {
    $this->email = $email;
    $this->password = $password;
    $this->feed_path_prepared = $this->feed_path;
    //$this->setDebug(true);
    $this->useSSL(true);
    parent::__construct($this->host,443);
  }
  
  function get_parsed($result, $bef, $aft="")
  {
    $line=1;
    $len = strlen($bef);
    $pos_bef = strpos($result, $bef);
    if($pos_bef===false)
      return "";
    $pos_bef+=$len;
    
    if(empty($aft))
    { //try to search up to the end of line
      $pos_aft = strpos($result, "\n", $pos_bef);
      if($pos_aft===false)
        $pos_aft = strpos($result, "\r\n", $pos_bef);
    }
    else
      $pos_aft = strpos($result, $aft, $pos_bef);
    
    if($pos_aft!==false)
      $rez = substr($result, $pos_bef, $pos_aft-$pos_bef);
    else
      $rez = substr($result, $pos_bef);
    
    return $rez;
  }

  //login with Google's technology of "ClientLogin"
  //check here: http://code.google.com/apis/accounts/AuthForInstalledApps.html
  function login()
  {
    $post_data = array();
    $post_data['Email']  = $this->email;
    $post_data['Passwd'] = $this->password;
    $post_data['source'] = "exampleCo-exampleApp-1";
    $post_data['service'] = "cl";
    $post_data['accountType'] = "GOOGLE";

    $response = $this->post("/accounts/ClientLogin", $post_data, null);

    if(200==$this->status)
    {
      $this->fAuth = $this->get_parsed($this->content, "Auth=");
      $this->isLogged = true;

      return 1;
    }
    $this->isLogged = false;
    return 0;
  }
  
  //to make the feed URL writable, it should be ended with "private/full"
  //check this: http://code.google.com/apis/gdata/calendar.html#get_feed
  function prepare_feed_path()
  {
    $url = parse_url($this->feed_url);
    $path = explode("/", $url["path"]);
    $size = sizeof($path);
    if($size>4)
    {
      $path[$size-1] = "full";
      $path[$size-2] = "private";
      $path = implode("/", $path);
    }
    $this->feed_path_prepared = $path;
  }
  
  //adds new event into calendar
  //filled $settings array should be provided
  function add_event($settings)
  {
    if(!$this->isLogged)
      $this->login();
    
    if($this->isLogged)
    {
      $_entry = "<entry xmlns='http://www.w3.org/2005/Atom' xmlns:gd='http://schemas.google.com/g/2005'>
        <category scheme='http://schemas.google.com/g/2005#kind' term='http://schemas.google.com/g/2005#event'></category>
        <title type='text'>".$settings["title"]."</title>
        <content type='text'>".$settings["content"]."</content>
        <author>
          <name>".$this->email."</name>
          <email>".$this->email."</email>
        </author>
        <gd:transparency
          value='http://schemas.google.com/g/2005#event.opaque'>
        </gd:transparency>
        <gd:eventStatus
          value='http://schemas.google.com/g/2005#event.confirmed'>
        </gd:eventStatus>
        <gd:where valueString='".$settings["where"]."'></gd:where>
        <gd:when startTime='".$settings["startDay"]."T".$settings["startTime"].".000Z'
          endTime='".$settings["endDay"]."T".$settings["endTime"].".000Z'>
	<gd:reminder minutes='5' /></gd:when>
      </entry>";
      $this->prepare_feed_path();
      
      $header = array();
      $this->setMoreHeader("MIME-Version: 1.0");
      //$this->setMoreHeader("Accept: text/xml");
      $this->setMoreHeader("Authorization: GoogleLogin auth=".$this->fAuth);
      $this->setMoreHeader("Content-length: ".strlen($_entry));
      $this->setMoreHeader("Content-type: application/atom+xml");
      $this->setMoreHeader("Cache-Control: no-cache");
      $this->setMoreHeader("Connection: close \r\n");
      $this->setMoreHeader($_entry);

      
      $this->setHost($this->feed_host,80);
      $this->useSSL(false);
      $this->setHandleRedirects(false);
      $status = $this->post($this->feed_path_prepared, null ,null);
      if (302==$status) {
      	$h = $this->getHeaders();
	$new_uri = parse_url($h['location']);
	$status =$this->post($new_uri['path'].'?'.$new_uri['query'], null ,null) ;
	if(201==$status)
	  return true;
	}
      }
      return false;
  }
}

?>
