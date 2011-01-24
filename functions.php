<?php

function WriteSyslog($syslogmessage)
{
    global $syslogpriority, $syslogfacility, $syslogident;
    
    //openlog() takes an integer for the facility so I have to use an associative array
    $syslogfacilitycode = array
    (
        "LOG_KERN" => 0,
        "LOG_USER" => 1,
        "LOG_MAIL" => 2,
        "LOG_DAEMON" => 3,
        "LOG_AUTH" => 4,
        "LOG_SYSLOG" => 5,
        "LOG_LPR" => 6,
        "LOG_NEWS" => 7,
        "LOG_UUCP" => 8,
        "LOG_CRON" => 9,
        "LOG_AUTHPRIV" => 10,
        "LOG_FTP" => 11,
        "LOG_NTP" => 12,
        "LOG_LOGAUDIT" => 13,
        "LOG_LOGALERT" => 14,
        "LOG_CRON2" => 15,
        "LOG_LOCAL0" => 16,
        "LOG_LOCAL1" => 17,
        "LOG_LOCAL2" => 18,
        "LOG_LOCAL3" => 19,
        "LOG_LOCAL4" => 20,
        "LOG_LOCAL5" => 21,
        "LOG_LOCAL6" => 22,
        "LOG_LOCAL7" => 23,
    );
    
    //syslog() takes an integer for the level so I have to use an associative array
    $syslogprioritycode = array
    (
        "LOG_EMERGemerg" => 0,
        "LOG_ALERT" => 1,
        "LOG_CRIT" => 2,
        "LOG_ERR" => 3,
        "LOG_WARNING" => 4,
        "LOG_NOTICE" => 5,
        "LOG_INFO" => 6,
        "LOG_DEBUG" => 7
    );
    
    
    //Need to use openlog to use RTR2Syslog as the program identity in the log line
    $has_open_syslog = openlog($syslogident, LOG_ODELAY | LOG_PERROR | LOG_PID, $syslogfacilitycode[$syslogfacility]);
    
    if (!$has_open_syslog)
    {
        closelog();
        die('Unable to open syslog.  Please read README.');
    }
    
    
    $has_write_syslog = syslog($syslogprioritycode[$syslogpriority], $syslogmessage);
    if (!$has_write_syslog)
    {
        closelog();
        die('Unable to write to syslog.  Please read README.');
    }
    
    closelog(); 
    
}






function WriteDirectLog($logmessage)
{
    global $logfile;
    
    $logmessage .= "\n";
    
    $logfilehandler = fopen ($logfile , "a");
    if (!$logfilehandler)
    {
        die('Unable to write to direct log.  Check that it\'s writable by the web server UserID.');
    }
    fwrite($logfilehandler, $logmessage);
    fclose($logfilehandler);
    
}






function WriteDebugLog($logmessage)
{
    global $debuglog, $debugresponse;
    
    if ($debugresponse == "true")
    {
        echo $logmessage;
    }
    
    $logmessage = date("M j G:i:s") . "\n" . $logmessage . "\n\n";
    

    $debugloghandler = fopen ($debuglog , "a");
    if (!$debugloghandler)
    {
        die('Unable to write to debug log.  Check that it\'s writable by the web server UserID.');
    }
    fwrite($debugloghandler, $logmessage);
    fclose($debugloghandler);
}


?>