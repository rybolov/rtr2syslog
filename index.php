<?php
//rtr2syslog main script
//Michael Smith rybolov@ryzhe.ath.cx
// http://www.guerilla-ciso.com

//require 'config.php';
if (!@include 'config.php') {
    die('Config not found.  Please read README.');
}

//This supposedly works with the PECL HTTP 
//$postbody = http_get_request_body();

//This works on most PHP installs
$postbody = @file_get_contents('php://input');
//echo ("$postbody");


$looper = 0;

while ($postbody as $logline)
{
    echo $looper;
    echo ("$logline\r");
    
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
