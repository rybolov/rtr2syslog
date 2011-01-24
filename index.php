<?php
//rtr2syslog main script
//Michael Smith rybolov@ryzhe.ath.cx
// http://www.guerilla-ciso.com

//require 'config.php';
if (!@include 'config.php') {
    die('Config not found.  Please read README.');
}

require 'functions.php';

$headersecret = $_SERVER["HTTP_X_RTR_SECRET"];

//This supposedly works with the PECL HTTP but requires an additional install 
//$postbody = http_get_request_body();

//This should work on most PHP installs
$postbody = file('php://input');
//echo ("$postbody");


//log the request, plenty of options here based on $debug
if ($debug == "auth")
{
    $selflog = "New RTR Post.\nDebug is set to \"auth\"\n";
    $selflog .= "Request Auth Secret: " . $headersecret . "\n";
}
elseif ($debug == "head")
{
    $selflog = "New RTR Post.\nDebug is set to \"head\"\n";
    
    $headers = apache_request_headers();
    foreach ($headers as $header => $value)
    {
        $selflog .= $header . ": " . $value . "\n";
    }
}
elseif ($debug == "body")
{
    $selflog = "New RTR Post.\nDebug is set to \"body\"\n";
    $headers = apache_request_headers();
    foreach ($headers as $header => $value)
    {
        $selflog .= $header . ": " . $value . "\n";
    }
    $selflog .= "\n" . implode("", $postbody);
}
elseif ($debug == "all")
{
    $selflog = "New RTR Post.\nDebug is set to \"all\"\n";
    $headers = apache_request_headers();
    foreach ($headers as $header => $value)
    {
        $selflog .= $header . ": " . $value . "\n";
    }
    $selflog .= "\n" . implode("", $postbody);
}
elseif ($debug == "error")
{
    $selflog = "New RTR Post.\nDebug is set to \"error\"\n";
}

if ($selflog)
{
    WriteDebugLog($selflog);
    $selflog = "";
}

//check to see if we have an authentication secret
if (!$headersecret)
{

   //TODO: log that auth has failed.
   if (($debug == "auth") || ($debug == "head") || ($debug == "body") || ($debug == "all") || ($debug == "error"))
    {
        WriteDebugLog("Authentication Fail.  No authentication secret in RTR header.\n");
    }
    
    die('Malformed request.  No Authentication secret.');
}

//Check that the secret matches.
if ($headersecret == $secretmain)
{
    if (($debug == "auth") || ($debug == "head") || ($debug == "body") || ($debug == "all"))
    {
        WriteDebugLog("Authentication Success.  Authentication secret validated against the main secret.\n");
    } 
}
elseif ($headersecret == $secretsecondary)
{
    if (($debug == "auth") || ($debug == "head") || ($debug == "body") || ($debug == "all"))
    {
        WriteDebugLog("Authentication Success.  Authentication secret validated against the secondary secret.\n");
    } 
}
else
{
      if (($debug == "auth") || ($debug == "head") || ($debug == "body") || ($debug == "all") || ($debug == "error"))
    {
        WriteDebugLog("Authentication Fail.  Authentication secret failed validation.\n");
    } 
   die('Malformed request.  Authentication secret failed validation.');
}



//Set up the validation regexes for later use.  Moved up here so we only have to set them once.
$linevalidationregex = array
    (
        0 => "/^W$/",  //W
        1 => "/^[0-9]{9,15}\.[0-9]{3}$/",  //1236205695.625
        2 => "/^[a-z,A-Z,0-9]{4}_[0-9]{3}$/",  //lb01_736
        3 => "/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/",  //127.0.0.1
        4 => "/^GET|POST|PUT|HEAD|TRACE|DELETE|CONNECT$/",  //GET
        //5 => "/^(\/([a-z,A-Z,0-9]|\.|\_|\?|\=\&|\+|-)*)*\?.*$/",  // /L/1/16399/10s/labamba.akamai.com/index.html
        //5 above has a cheat for the url parameters with a wildcard (.*) because the url can have parameters or attack payloads in the get url.
        5 => "/^(\/([a-z,A-Z,0-9]|\.|\_|\?|\=|\&|\+|-)*)*$/",  // /L/1/16399/10s/labamba.akamai.com/index.html
        6 => "/^[0-9]{3}$/",  //400
        7 => "/^[0-9]*$/",  //15
        8 => "/^[0-9]{1,3}$/",  //1
        9 => "/^[0-9]{6}$/",  //950012
        10 => "/^[0-1]$/",  //1
        11 => "/^([a-z,A-Z,0-9]|%|\.|-)*$/", //HTTP%20Request%20Smuggling%20Attack.
        12 => "/^([a-z,A-Z,0-9]|\/|\_|\:|-)*$/", //WEB_ATTACK/REQUEST_SMUGGLING
        13 => "/^([a-z,A-Z,0-9]|-)*$/", //-
        14 => "/^([a-z,A-Z,0-9]|\/|\_|\:|-)*$/" //REQUEST_HEADERS:Content-Length
    );
    
    

//Step through the body line-by-line
foreach ($postbody as $linenumber => $logline)
{
    $loglinearray = explode(" ", $logline);
    
    if ($debug == "all")
    {
        $selflog = "Processing Line: " . $linenumber . "\nContents: " . $logline . "\n";
        WriteDebugLog($selflog);
        $selflog = "";
    } 
    
    //Check for Version Number on first line, fail if it's not there.
    if ($linenumber == "0")
    {
        if (preg_match("/^v 1\.0$/", trim($logline)))
        {   //Only v 1.0 has been released.
            $rtrversion = "1.0";
            if ($debug == "all")
            {
                WriteDebugLog("Found Version 1.0 of RTR.\n");
            } 
        }
        else
        {
            if (($debug == "auth") || ($debug == "head") || ($debug == "body") || ($debug == "all") || ($debug == "error"))
            {
                WriteDebugLog("Malformed request.  RTR version number not found.\n");
            } 
            die('Malformed request.  RTR version number not found.\n');
        }
        
    }
    elseif (preg_match("/^[ \t\r\n\v\f]*$/", $logline)) 
    {   //blank lines
            if ($debug == "all")
            {
                WriteDebugLog("Blank line.  No action taken\n");
            } 
    }
    else
    {   //loop through all the elements of the line, do a regex match against each of them.
        $lineerror = "";
        
        //9 elements plus 6 for each rule match.
        $elementscount = ((count($loglinearray) -9) - (6 * $loglinearray[8]) );
        if ($elementscount == 0)
        {
            if ($debug == "all")
            {
                $selflog = "Line has correct number of elements.  Expected 9 + (x*6) . Received " . count($loglinearray) . "\n" ;
                WriteDebugLog($selflog);
                $selflog = "";
            }
        }
        else
        {
            if (($debug == "auth") || ($debug == "head") || ($debug == "body") || ($debug == "all") || ($debug == "error"))
            {
                $selflog = "Malformed line.  Incorrect number of elements.  Expected 9 + (x*6) . Received " . count($loglinearray) . "\n" ;
                WriteDebugLog($selflog);
                $selflog = "";
            }
        }
        
        foreach ($loglinearray as $logelementnumber => $logelement)
        {
            //Get an element we can pull from the regex array.
            //Use a loop so that they can put an infinite number of matches on the end of the  line.
            $logelementnumbereffective = $logelementnumber;
            while ($logelementnumbereffective > 14)
            {
                $logelementnumbereffective = $logelementnumbereffective -6;
                
            }
            
            if ($debug == "all")
            {
                $selflog = "Testing line element " . $logelementnumber . " Effective: " . $logelementnumbereffective . ". Regex: " . $linevalidationregex[$logelementnumbereffective] . " Element: " . $logelement . "\n";
                WriteDebugLog($selflog);
                $selflog = "";
            }

            if (preg_match($linevalidationregex[$logelementnumbereffective], rtrim($logelement)))
            {
                if ($debug == "all")
                {
                    WriteDebugLog("Element matches regex.\n");
                }
            }
            else
            {
                if (($debug == "auth") || ($debug == "head") || ($debug == "body") || ($debug == "all") || ($debug == "error"))
                {
                    $selflog = "Element fails regex.\n";
                    WriteDebugLog("Element fails regex.\n");
                }
            }
            
            
            
        }
        if ($lineerror)
        {
            //TODO: be more verbose based on errors from the main line validation loops
            WriteDebugLog("Line failed validation.\n");
        }
        else
        {
            if ($debug == "all")
            {
                WriteDebugLog("Line passed validation.\n");
            }
            for($creatlogloop = 1;  $creatlogloop <= $loglinearray[8]; $creatlogloop++)
            {
                $newlogline = $loglinearray[0] . " ";
                $newlogline .= $loglinearray[1] . " ";
                $newlogline .= $loglinearray[2] . " ";
                $newlogline .= $loglinearray[3] . " ";
                $newlogline .= $loglinearray[4] . " ";
                $newlogline .= $loglinearray[5] . " ";
                $newlogline .= $loglinearray[6] . " ";
                $newlogline .= $loglinearray[7] . " ";
                $newlogline .= $loglinearray[8] . " ";
                $newlogline .= $loglinearray[((($creatlogloop -1)*6) +9)] . " ";
                $newlogline .= $loglinearray[((($creatlogloop -1)*6) +10)] . " ";
                $newlogline .= $loglinearray[((($creatlogloop -1)*6) +11)] . " ";
                $newlogline .= $loglinearray[((($creatlogloop -1)*6) +12)] . " ";
                $newlogline .= $loglinearray[((($creatlogloop -1)*6) +13)] . " ";
                $newlogline .= $loglinearray[((($creatlogloop -1)*6) +14)];
                
                if ($mode == "syslog")
                {
                    if ($debug == "all")
                    {
                        $selflog = "Ready to write log line to syslog: " . $newlogline . "\n";
                        WriteDebugLog($selflog);
                        $selflog = "";
                    }
                    WriteSyslog(rtrim($newlogline));
                }
                //"direct" mode
                elseif ($mode == "direct")
                {
                    if ($debug == "all")
                    {
                        $selflog = "Ready to write log line directly to file: " . $newlogline . "\n";
                        WriteDebugLog($selflog);
                        $selflog = "";
                    }
                    $newlogline = date("M j G:i:s") . " rtr2syslog: " . $newlogline; 
                    WriteDirectLog(rtrim($newlogline));
                }
            } 
        }    
    }
}


?>