<?php
class tools 
{
    public function Wget($source, $dest)
    {
        switch(PHP_OS)
        {
            case "Linux":   system("wget -P ".$dest." ".$source); break;
            case "FreeBSD": system("wget -P ".$dest." ".$source); break;
            case "WINNT":   shell_exec(TOOLS . "wget.exe -P ".$dest." ".$source); break;    
        }
    }
    
    public function Unrar($source, $dest)
    {
        switch(PHP_OS)
        {
            case "Linux":   system("unrar x -y ".$source." ".$dest); break;
            case "FreeBSD": system("unrar x -y ".$source." ".$dest); break;
            case "WINNT":   shell_exec(TOOLS . "unrar.exe e -y ".$source." ".$dest); break;    
        }    
    }   
    
    public function TimeZone($timezone)
    { 
        switch(PHP_OS)
        {
            case "Linux":   
            date_default_timezone_set($timezone);
            ini_set('date.timezone', $timezone); 
            break;
            
            case "FreeBSD": 
            date_default_timezone_set($timezone);
            ini_set('date.timezone', $timezone); 
            break;  
        }   
    }
    
    public function CLI($command)
    { 
        switch(PHP_OS)
        {
            case "Linux":   return shell_exec($command); break;
            case "FreeBSD": return shell_exec($command); break;  
            case "WINNT":   return shell_exec($command); break;
        }   
    }
}   
?>
