<?php

if (!@include 'config.php')
{
    die('Config not found.  Please read README.');
}

require 'functions.php';

if ($mode == "syslog")
{
    WriteSyslog("Test log for rtr2syslog using logtest.php");
    WriteDebugLog("Sent test log to syslog via logtest.php.\n");
}
elseif ($mode == "direct")
{
    WriteDirectLog("Test log for rtr2syslog using logtest.php");
    WriteDebugLog("Sent test log to logfile via logtest.php.\n");
}

?>