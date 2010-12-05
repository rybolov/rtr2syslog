<?php
//rtr2syslog main script
//Michael Smith rybolov@ryzhe.ath.cx
// http://www.guerilla-ciso.com

//require 'config.php';
if (!@include 'config.php') {
    die('Config not found.  Please read README.');
}

if (!isset(_SERVER["HTTP_X_RTR_SECRET"]))
{
   die('Malformed request.  No Authentication secret.'); 
}

//Check that the secret matches.
if (($_SERVER["HTTP_X_RTR_SECRET"] !== $secretmain) || ($_SERVER["HTTP_X_RTR_SECRET"] !== $secretsecondary))
{
   die('Malformed request.  Authentication secret failed validation.'); 
}


//This supposedly works with the PECL HTTP but requires an additional install 
//$postbody = http_get_request_body();

//This should work on most PHP installs
$postbody = file('php://input');
//echo ("$postbody");

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
    {
        if ($logline !=="v 1.0")
        {
            die('Malformed request.  RTR version number not found.');
        }
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
