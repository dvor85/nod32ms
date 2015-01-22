<?php
class files
{
    public function CopyFile($source, $dest) //Копирование файла 
    {
        if(is_file($dest)){$this->CreateDir(dirname($dest));}else{$this->CreateDir($dest);}
        
        switch(PHP_OS)
        {
            case "Linux":   system("cp ".$source." ".$dest." > /dev/null"); break;
            case "FreeBSD": system("cp ".$source." ".$dest." > /dev/null"); break;
            case "WINNT":   shell_exec("copy ".$source." ".$dest); break;    
        } 
    }
    
    public function CopyDir($source, $dest) //Копирование содержимого из директории в директорию 
    {
        if($this->CheckDir($source))
        {
            switch(PHP_OS)
            {
                case "Linux":   system("cp -R ".$source."* ".$dest." > /dev/null"); break;
                case "FreeBSD": system("cp -R ".$source."* ".$dest." > /dev/null"); break;
                case "WINNT":   shell_exec("xcopy ".$source." ".$dest." /s /e /i /h"); break;    
            } 
        }       
    }
    
    public function DeleteFile($filename) //Удаление файла
    {
        if($this->CheckFile($filename))
        {
            switch(PHP_OS)
            {
                case "Linux":   system("rm ".$filename." > /dev/null"); break;
                case "FreeBSD": system("rm ".$filename." > /dev/null"); break;
                case "WINNT":   shell_exec("del ".$filename); break;    
            }
        }    
    }
    
    public function DeleteFileLine($filename, $text) //Удаление строки из файла
    {
        if($this->CheckFile($filename))
        {
            $file = @file_get_contents($filename);
            
            $file = preg_replace("/$text\\r*\\n+/", "", $file);
            file_put_contents($filename, $file);
        }    
    }
    
    public function DeleteDir($path) //Удаление директории
    {
        if($this->CheckDir($path))
        {
            switch(PHP_OS)
            {
                case "Linux":   system("rm ".$path." > /dev/null"); break;
                case "FreeBSD": system("rm ".$path." > /dev/null"); break;
                case "WINNT":   shell_exec("rmdir ".$path); break;    
            }
        }     
    }
    
    public function CreateFile($filename, $text='') //Создание файла 
    {
        $file = fopen($filename, "a+");
        
        if(!feof($file)){fwrite($file, $text);}
        
        fflush($file);
        fclose($file);         
    }
    
    public function CreateDir($path) //Создание директории 
    {
        if(!$this->CheckDir($path))
        {
            switch(PHP_OS)
            {
                case "Linux":   
                system("mkdir -p ".$path." > /dev/null"); 
                $this->SetDirAccess($path, '664'); 
                break;
                
                case "FreeBSD": 
                system("mkdir -p ".$path." > /dev/null"); 
                $this->SetDirAccess($path, '664'); 
                break;
                
                case "WINNT":   
                shell_exec("mkdir ".$path); 
                break;    
            }     
        } 
        else
        {
            if(!is_readable($path)){$this->SetDirAccess($path, '664');}   
            if(!is_writable($path)){$this->SetDirAccess($path, '664');}   
        }   
    }
    
    public function CheckFile($filename) //Проверяет наличие файла
    {
        if(file_exists($filename))
        {
            return true;
        }    
    }
    
    public function SetFileAccess($filename, $order='777', $username='nod32ms') //Проверяет права доступа к файлу
    {
        if($this->CheckFile($filename))
        {
            switch(PHP_OS)
            {
                case "Linux":   
                system("chown ".$username.":".$username." ".$filename." > /dev/null");
                system("chmod ".$order." ".$filename." > /dev/null"); 
                break;
                
                case "FreeBSD": 
                system("chown ".$username.":".$username." ".$filename." > /dev/null");
                system("chmod ".$order." ".$filename." > /dev/null");  
                break;  
            }     
        }       
    }
    
    public function SetDirAccess($path, $order='777', $username='nod32ms') //Проверяет права доступа к файлу
    {
        if($this->CheckDir($path))
        {
            switch(PHP_OS)
            {
                case "Linux":   
                system("chown ".$username.":".$username." -R ".$path." > /dev/null");
                system("chmod -R ".$order." ".$path." > /dev/null"); 
                break;
                
                case "FreeBSD": 
                system("chown ".$username.":".$username." -R ".$path." > /dev/null");
                system("chmod -R ".$order." ".$path." > /dev/null");  
                break;  
            }     
        }
    }
    
    public function CheckDir($path) //Проверяет наличие директории
    {
        if(file_exists(is_dir($path)))
        {
            return true;    
        }     
    }
}  
?>
