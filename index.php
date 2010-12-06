<?php
//rtr2syslog main script
//Michael Smith rybolov@ryzhe.ath.cx
// http://www.guerilla-ciso.com

//require 'config.php';
if (!@include 'config.php') {
    die('Config not found.  Please read README.');
}

$headersecret = $_SERVER["HTTP_X_RTR_SECRET"];

//This supposedly works with the PECL HTTP but requires an additional install 
//$postbody = http_get_request_body();

//This should work on most PHP installs
$postbody = file('php://input');
//echo ("$postbody");


//log the request, plenty of options here based on $debug
if ($debug !== "off")
{
    //check to see if $debugdirectory is writable
    
    if ($debug == "head")
    {
        $selflog = "";
        $selflog .= "\r";
        $selflog .= $postbody;
        $selflog .= "\r";   
    }
    elseif ($debug == "auth")
    {
        $selflog = "Request Auth Secret: ";
        $selflog .= $headersecret;
        $selflog .= "\r";
    }
    elseif ($debug == "body" || "all")
    {
        $selflog = "";
        $selflog .= "\r";
        $selflog .= $postbody;
        $selflog .= "\r";
    }
    if ($debug !== "error")
    {
        //log into $debugdirectory
        
        echo $selflog;
    }
}

if (!$headersecret)
{
   die('Malformed request.  No Authentication secret.');
   //TODO: log that auth has failed.
}

//Check that the secret matches.
//if (($headersecret !== $secretmain) || ($headersecret !== $secretsecondary))
if ($headersecret == $secretmain)
{
   //TODO: log success for debug
}
elseif ($headersecret == $secretsecondary)
{
   //TODO: log success for debug
}
else
{
   die('Malformed request.  Authentication secret failed validation.');
   //TODO: log that auth has failed.
}






//Step through the file line-by-line
foreach ($postbody as $linenumber => $logline)
{
    if ($debug !== "off")
    {
        //echo $linenumber;
        //echo ("$logline\r");
    }
    
    //Check for Version
    if ($linenumber == "0")
    {   //IE, We're at the first line, check to see if the body is valid, starting with this.
        if (preg_match("/^v 1\.0$/", $logline))
        {   //Only v 1.0 has been released.  If further versions are released, this is where we set the logic.
            die('Malformed request.  RTR version number not found.');
        }
        elseif ($logline !=="v 1.0")
        {
            $rtrversion = "1.0";
        }
    }
    //elseif (preg_match("/^W [0-9]{9,15}\.[0-9]{3} [0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3} {GET|POST|PUT|HEAD|TRACE} [\/[a-z,A-z]]* [0-9]{3} [0-9]* [0-9]{1,3} [0-9]{5} [0-1] .*$/", $logline))
    elseif (preg_match("/^W/", $logline))
    {   //IE, it's a well-formed line.  Not convinced that this regex will scale to a high processor load.
        
    }
    else
    {
        //IE, the line doesn't match a valid line...
        echo "Malformed line failed validation: ";
        echo $logline;
        echo "\r";
    }
    
    //"syslog" mode
    if ($mode == "syslog")
    {
        //syslog($syslogpriority | $syslogfacility, $syslogident.': '. $logmessage);
        
    }
    
    //"direct" mode
    elseif ($mode == "direct")
    {
        //check for "wait"
        //write to file
    }
}


?> 
