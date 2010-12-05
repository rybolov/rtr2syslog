<?php
//rtr2syslog main script
//Michael Smith rybolov@ryzhe.ath.cx
// http://www.guerilla-ciso.com

//require 'config.php';
if (!@include 'config.php') {
    die('Config not found.  Please read README.');
}

$postbody = http_get_request_body();
echo ("$postbody");





//echo ("$logline");

//"syslog" mode
if ($mode == "syslog")
{
    //syslog($syslogpriority | $syslogfacility, $syslogident.': '. $logmessage);
    
}




//"direct" mode
if ($mode == "direct")
{
    //check for "wait"
    //write to file
}



?> 
