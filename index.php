<?php
//rtr2syslog main script
//Michael Smith rybolov@ryzhe.ath.cx
// http://www.guerilla-ciso.com

//require 'config.php';
if (!@include 'config.php') {
    die('Config not found.  Please read README.');
}







//echo ("$logline");

//"syslog" mode
if ($mode == "syslog")
{
    //syslog($syslogpriority | LOG_LOCAL1, WAF.' '."Oh Hai Thar");
    
}




//"direct" mode
if ($mode == "direct")
{
    //check for "wait"
    //write to file
}



?> 
