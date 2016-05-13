<?php
class nod32ms //Базовый класс программы
{
    private $files;
    private $parser;
    private $tools;
    private $mail;
    
    public $CONFIG;
    public $KEYS;
    public $FILE;
    
    private $error;
    
    public function __construct() //Обработчик создания класса
    {
        $this->files    = new files();
        $this->parser   = new parser();   
        $this->tools    = new tools();
        $this->mail     = new PHPMailer();

        $this->ReadConfig('nod32ms.conf');
        
        //$this->HOST[0] = $this->CONFIG['mirror'];

        define("VERSION",   $this->GetSelfUpdateVersion());
 
        $this->WriteToLog("[RUN NOD32MS] - nod32 mirror script ver. ".VERSION." by ".AUTHOR_ALIAS); 
        $this->WriteToLog("SYSTEM: ".PHP_OS);

        if(isset($this->CONFIG['php_error_reporting']))
        {
            error_reporting($this->CONFIG['php_error_reporting']);
        }  
        
        if(isset($this->CONFIG['timezone']))
        {
            $this->tools->TimeZone($this->CONFIG['timezone']);
        }

        //$this->ReadKeys();
    }
    
    public function __destruct()  //Обработчик завершения класса
    {
        $this->SendInfo();
        //$this->SetError(__METHOD__, "TEST ERROR!");
        $this->SendError();
         
        $this->WriteToLog("[STOP NOD32MS]");
    }
    

    private function SendInfo() //Функция отправки информации на сервер разработчика
    {
        $server = @fsockopen(MASTERHOST, 80, $errno, $errstr, 1);
        
        if(PHP_OS !== "WINNT")
        {
            $core   = str_replace("\n","",$this->tools->CLI("uname -a"));
            $uptime = str_replace("\n","",$this->tools->CLI("uptime"));
        }
        else
        {
            $core = $_SERVER['PROCESSOR_IDENTIFIER'];
        }
        
        $version_script = VERSION;
        $version_signature = $this->GetUpdateVersion();

        if($server)
        {
            $this->WriteToLog("[MASTER SERVER ONLINE]"); 
            @fputs($server, "GET /getdata.php HTTP/1.0\n");
            @fputs($server, "Cookie: system=".PHP_OS."; uptime=".$uptime."; version_signature=".$version_signature."; version_script=".$version_script."; core=".$core.";\n\n");
            @fclose($server);
        }
        else
        {
            $this->WriteToLog("[MASTER SERVER OFFLINE]");
        }      
    }
    
    private function SendError() //Функция отправки сообщения об ошибке через mail сервис
    {
        if($this->CONFIG['mail_enabled'] == true AND is_array($this->error))
        {
            $body  = $this->error[0];

            $this->mail->IsSMTP(); 
            $this->mail->Host       = $this->CONFIG['mail_smtp_host'];

            $this->mail->SMTPAuth   = $this->CONFIG['mail_smtp_auth'];
            $this->mail->Host       = $this->CONFIG['mail_smtp_host'];
            $this->mail->Port       = $this->CONFIG['mail_smtp_port'];
            $this->mail->Username   = $this->CONFIG['mail_smtp_username'];
            $this->mail->Password   = $this->CONFIG['mail_smtp_password'];

            $this->mail->SetFrom($this->CONFIG['mail_smtp_username'], 'nod32 mirror script');

            $this->mail->AddReplyTo($this->CONFIG['mail_admin_address'], $this->CONFIG['mail_admin_name']);

            $this->mail->Subject    = "NOD32MS - ERROR MESSAGE";

            $this->mail->MsgHTML($body);

            $address = $this->CONFIG['mail_admin_address'];
            $this->mail->AddAddress($address, $this->CONFIG['mail_admin_name']);

            $this->mail->Send();
        }
    }
    
    private function GetUpdateVersion() //Вывод последней версии сигнатур антивируса
    {
        return $this->FILE['versionid'][0];
    }
    
    private function GetSelfUpdateVersion() //Вывод последней версии программы
    {
        $temp_file  = $this->CONFIG['temp_dir'].DS.'nod32ms.ver';
        
        //$old_file = $this->parser->LoadParseFile($temp_file); 
        
        $old['version_number']      = $this->parser->ParseValueOne($temp_file, 'version_number');
        $old['version_title']       = $this->parser->ParseValueOne($temp_file, 'version_title');
        
        return $old['version_title'].' ('.$old['version_number'].')';
    }

    private function GetUpdateSize() //Вывод бщего размера текущих обновлений
    {
        $summ = 0;
        if(count($this->FILE['size']) > 0)
        {
            foreach($this->FILE['size'] as $value)
            {
                $summ = $summ + $value;    
            }       
        }
        return ceil(($summ/1024)/1024)." Mb";
    }
    
    private function GetUpdateLanguage($lang) //Вывод используемой локолизации
    {
        if(!empty($lang))
        {
            $language = explode(",", $lang);
                    
            if(count($language) > 1)
            {
                for($a=0; $a < count($language); $a++)
                {
                    $language_line .= "|".str_replace(" ", "" ,$language[$a]);        
                }  
                        
                $language_line = substr($language_line, 1);  
            }
            else
            {
                $language_line = $language[0];
            }
                    
            return $language_line;
        }
        else
        {
            return "rus";
        }      
    }
    
    private function CheckKey($login, $password) //Проверка пары логин:пароль
    {
		//$url="http://".$login.":".$password."@".$this->CONFIG['mirror']."/v3-rel-sta/mod_002_engine_19504/em002_32_n2.nup";
		
		//print_r($this->FILE['file'][0]);
		//if (empty($this->FILE['file'][0])) {
		//	return false;
		//}
		//$url  = "http://".$login.":".$password."@".$this->CONFIG['mirror'].'/eset_upd/update.ver';
		$url="http://".$login.":".$password."@".$this->CONFIG['mirror'].$this->FILE['file'][0];
		//var_dump($url);
        if(file_get_contents($url)) 
        { 
            return true;      
        }
        else
        {
            return false;
        }
    }
    
    private function WriteToLog($text) //Запись информации в журнал
    {
        if($this->CONFIG['write_to_log'] == true)
        { 
            if(isset($this->CONFIG['log_dir']))
            {
                $this->files->createdir($this->CONFIG['log_dir'].DS);
                $this->files->CreateFile($this->CONFIG['log_dir'].DS.'work.log', "[".date("d/m/Y")." ".date("H:i:s")."]".$text."\r\n");
            }   
        }   
    }
    
    private function WriteTextDB($param, $value) //Запись текста в файл
    {
        $this->files->CreateFile(SYSTEM.'db.txt', $param."=".$value."\r\n");
    }
    
    private function WriteKey($login, $password) //Запись ключа в файл
    {
        $this->files->CreateFile(SYSTEM.'keys.txt', $login.":".$password."\r\n");
    }
    
    private function SetError($method, $text, $duplicate_to_log=true) // Установка ошибки в конкретном методе
    {
        if($duplicate_to_log)
        {
            $this->WriteToLog("ERROR [".$method."] ".$text);   
        }
        
        $this->error[] = "[".$method."] ".$text;   
        
        if($this->CONFIG['show_last_error'] == true)
        {
            print_r($this->error);   
        }
        
        //$this->SendError($method, $text);
        exit; 
    }

    public function ReadConfig($filename='nod32ms.conf') //Чтение параметров из конфигурационного файла
    {
        if($this->files->CheckFile($filename))
        {
            //$config = $this->parser->LoadParseFile($filename);			
            $this->CONFIG = $this->parser->ParseKeyVal($filename); 

            /*for($i=0; $i < count($array[1]); $i++)
            {
                $array[1][$i] = str_replace(" ", "", $array[1][$i]);
                $array[2][$i] = str_replace(" ", "", $array[2][$i]);
                $array[2][$i] = str_replace("\r", "", $array[2][$i]);
                $this->CONFIG[$array[1][$i]] = $array[2][$i];        
            }*/
        }
    }
    
    public function ReadKeys() //Чтение ключей из соответствующего файла
    {
        $this_file = SYSTEM.'keys.txt';
        
        if($this->files->CheckFile($this_file))
        {
            if(filesize($this_file) > 0)
            {
                //$keys   = $this->parser->LoadParseFile($this_file);
                $array  = $this->parser->ParseKeyVal($this_file);

                foreach($array as $login => $pass)
                {

                    if($this->CheckKey($login, $pass) == True)
                    {
                        $this->KEYS['login'][]    = $login; 
                        $this->KEYS['password'][] = $pass; 
                    }
                    else
                    {
                        $this->files->DeleteFileLine($this_file, $login.":".$pass);
                        $this->WriteToLog("REMOVE INVALID KEY [".$login.":".$pass."]");          
                    }    
                } 
                
                if(count($this->KEYS['login']) == 0)
                {
                    $this->WriteToLog("NOT FOUND VALID KEYS IN keys.txt");
                    $this->WriteToLog("RUN FINDER KEYS");
                    $this->FindKeys();    
                }     
            }
            else
            {
                $this->WriteToLog("FILE keys.txt EMPTY!");
                $this->WriteToLog("RUN FINDER KEYS");
                $this->FindKeys();      
            }
        }
        else
        {
            $this->WriteToLog("FILE keys.txt NOT FOUND!");
            $this->WriteToLog("RUN FINDER KEYS");
            $this->FindKeys();  
        }
    }
    
    public function FindKeys() //Поиск рабочих ключей в интернете
    {
        if($this->CONFIG['keys_autofind'])
        {
            
            //$tag_array = array("eset", "nod32", "keys", "login", "password", "username", "eav-", "trial-");
            
            $date       = date("Y");
            $keyword    = trim("nod32+username+eav-+trial-");
            
            $count      = 0;
            $max_count  = $this->CONFIG['keys_autofind_page'] * 10;

            //while($count<$max_count)
            {
                //$url = "http://ajax.googleapis.com/ajax/services/search/web?v=1.0&hl=ru&rsz=large&start=".$count."&q=".urlencode($keyword); 
                $url = "http://duckduckgo.com/?q=".urlencode($keyword)."&no_html=1";
                //var_dump($url);
                $ch = curl_init(); 

                $headers = array();
                $headers[] = 'GET '.$url.' HTTP/1.1';
                $headers[] = 'Host: duckduckgo.com';
                $headers[] = 'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; ru; rv:1.9.2.24) Gecko/20111103 Firefox/3.6.24'; 
                $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'; 
                $headers[] = 'Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3'; 
                $headers[] = 'Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7'; 
                $headers[] = 'Keep-Alive: 115'; 
                $headers[] = 'Connection: keep-alive'; 
                $headers[] = 'Referer: '.$url; 

                curl_setopt($ch, CURLOPT_URL, $url); 
                curl_setopt($ch, CURLOPT_FAILONERROR, 1);  
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);   
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 

                //$json = json_decode(curl_exec($ch)); 
                $result=curl_exec($ch);
                //var_dump($result);

                curl_close($ch);  
                //exit;

                //foreach($json->responseData->results as $value)
                $results=explode("\n", $result);
                //var_dump($results);
                foreach($results as $value)
                {
                    //$value->content = strip_tags($value->content);
                    $value = strip_tags(trim($value));
                    //var_dump($value);
                    
                    $preg_res = preg_match("/Username[\s]*:[\s]*((EAV|TRIAL)-[0-9]{8,10})[\s\.]*Password[\s]*:[\s]*([A-Za-z0-9]{10})/", $value, $res);
                    //var_dump($preg_res);
                    if($preg_res)
                    {
                        for($a=0; $a < count($res[1]); $a++)
                        {
                            echo $keys['login'][]    = $res[1];
                            echo "\n";
                            echo $keys['password'][] = $res[3];  
                        }
                    }              
                }
                $count+=10;
            } 
            
            if(count($keys['login']) > 0)
            {
                $keys['login']    = array_unique($keys['login']);
                $keys['password'] = array_unique($keys['password']);
                    
                $this->WriteToLog("TOTAL FOUND KEYS: ".count($keys['login'])." IN ".$this->CONFIG['keys_autofind_pattern']);
                for($b=0; $b < count($keys['login']); $b++)
                { 
                    if($this->CheckKey($keys['login'][$b], $keys['password'][$b]))
                    {
                        $this->KEYS['login'][]    = $keys['login'][$b];
                        $this->KEYS['password'][] = $keys['password'][$b];  
                        $this->WriteKey($keys['login'][$b], $keys['password'][$b]);
                        $this->WriteToLog("Found valid key [".$keys['login'][$b].":".$keys['password'][$b]."]"); 
                    }
                    else {
                	$this->WriteToLog("Invalid key [".$keys['login'][$b].":".$keys['password'][$b]."]"); 
                    }
                }
            }
            else
            {
                $this->SetError(__METHOD__, "VALID KEYS NOT FOUND, TRY CHANGE keys_autofind_page ++ PARAM");    
            } 

        }  
        else
        {
            if(isset($this->CONFIG['username']) AND isset($this->CONFIG['password']))
            {
                $this->KEYS['login'][]    = $this->CONFIG['username']; 
                $this->KEYS['password'][] = $this->CONFIG['password'];
                $this->WriteToLog("USED KEY FROM CONFIG FILE, AUTO SEARCH DISABLED");    
            }
        }
    }
       
       
    public function DownloadUpdateVer($version_folder, $alias=false) //Загрузка файла update.ver содержащего информацию о сигнатурах
    {
		$random_numeric = mt_rand(0, count($this->KEYS['login'])-1);
		//$url  = "http://".$this->KEYS['login'][$random_numeric].":".$this->KEYS['password'][$random_numeric]."@".$this->CONFIG['mirror'].'/'.$version_folder.'/update.ver';
		$url  = "http://".$this->CONFIG['mirror'].'/'.$version_folder.'/update.ver';
        $version_folder = str_replace("/", DS, $version_folder);
        
        
        $dir_temp   = $this->CONFIG['temp_dir'].DS;
        $dir_work   = $this->CONFIG['work_dir'].DS;
        
        $dir_alias  = $dir_work.$version_folder.DS;
        $dir_orig   = $dir_temp.$version_folder.DS.'original'.DS;
        $dir        = $dir_temp.$version_folder.DS;
        
        $file       = $dir.'update.ver';
        $file_temp  = $dir_temp.'update.ver';
        $file_empty = $dir_temp.'update_empty.ver';
        $file_orig  = $dir_orig.'update.ver';
        $file_alias = $dir_alias.'update.ver';

        $this->files->DeleteFile($file_temp);
        $this->tools->Wget($url, $dir_temp);
        
        if($this->files->CheckFile($file_temp))
        {
            $this->WriteToLog("DOWNLOAD NEW [".$file_temp."] FILE");
            $this->files->DeleteFile($file); 
            $this->files->DeleteFile($file_orig);
            $this->files->CopyFile($file_temp, $dir_orig);
            $this->tools->Unrar($file_temp, $dir);
            $this->WriteToLog("UNRAR FILE [".$file_temp."] TO [".$dir."]");
            $this->files->DeleteFile($file_temp);
            
            if($alias == true)
            {
				if($this->files->CheckFile($file_orig))
				{
					$this->files->CopyFile($file_orig, $dir_alias);
					$this->WriteToLog("COPY ALIAS FILE [".$file_orig."] TO [".$dir_alias."]");
				}
				else
				{
					$this->SetError(__METHOD__, "ALIAS FILE [".$file_orig."] NOT FOUND");
				}
            }
        }
        else
        {     
            if($this->files->CheckFile($file_empty))
            {
                $this->SetError(__METHOD__, "DOWNLOAD FILE [".$file_empty."] STOP SCRIPT"); 
            }
            else
            {
                $this->SetError(__METHOD__, "DOWNLOAD FAIL [".$file."]");    
            } 
        }
		$this->ParseUpdateVer($version_folder);
    }
	
	public function getmodfilename($file) {
		//return substr(dirname($file),0,strrpos(dirname($file),'_'))."/".basename($file);
		$p=explode('/',$file);
		if(count($p)>1) {
			$p[count($p)-2] = preg_replace('/_\d+$/','',$p[count($p)-2]);
		}
		return implode('/',$p);
	}
    
    public function ParseUpdateVer($version_folder) //Получение информации из файла update.ver
    {		
        $version_folder = str_replace("/", DS, $version_folder);
        
        $file       = $this->CONFIG['temp_dir'].DS.$version_folder.DS.'update.ver';
        $file_empty = $this->CONFIG['temp_dir'].DS.$version_folder.DS.'update_empty.ver';
        
        if($this->files->CheckFile($file))  
        {
        
		$section=$this->parser->ParseSectionVar($file);	
        //$section = $this->parser->ParseSectionVar($file);
        
        //unset($section['HOSTS'],$section['Expire'], $section['SETUP'], $section['PCUVER']);
        
        foreach($section as $sn=>$sv) {			
			if (($sn=='HOSTS')||($sn=='Expire')||($sn=='SETUP')||($sn=='PCUVER')) {
				continue;
			}
            foreach($sv as $key=>$value)
            {
				if($key=='file'){
            	    $array['file'][]=$value;					
                }                                                            
				if($key=='size'){
					$array['size'][]=$value;
				}
			}
		}
	
		if($this->CONFIG['update_version345_arch32'] == true)
        	{
                $language = $this->GetUpdateLanguage($this->CONFIG['update_version345_language']);
                $tpl[] = "em([0-9]{3})_32_(n|l)([0-9]{1,2}).nup"; 
                $tpl[] = "(nt32)_($language).nup";    
        	}
            
        	if($this->CONFIG['update_version345_arch64'] == true)
        	{
                $language = $this->GetUpdateLanguage($this->CONFIG['update_version345_language']);
                $tpl[] = "em([0-9]{3})_64_(n|l)([0-9]{1,2}).nup";
                $tpl[] = "(nt64)_($language).nup";     
        	}
        	
            if(count($array['file']) > 0)
            { 
                $this->FILE['VF'][] = $version_folder;
                
                for($i=0; $i < count($array['file']); $i++)
               {
                    if(count($tpl) > 0)
                    {
                        for($a=0; $a < count($tpl); $a++)
                        {
                            if(ereg($tpl[$a], $array['file'][$i]))
                            {
								$this->FILE['file'][]    = trim($array['file'][$i]);
                                $this->FILE['size'][]    = trim($array['size'][$i]); 
                            }
                        } 
                    }
                    else
                    {
                        $this->SetError(__METHOD__, "SEE update_* PARAM IN CONFIG!!!");    
                    }
                }     
            }
            else
            {
                $this->SetError(__METHOD__, "FILE PARSE ERROR [".$file."]");    
            }
              
        }
        else
        {
            if($this->files->CheckFile($file_empty))  
            {
                $this->SetError(__METHOD__, "FILE EMPTY [".$file_empty."] FOUND!");
            }
            else
            {
                $this->SetError(__METHOD__, "FILE [".$file."] NOT FOUND!");    
            } 
        
        } 
    }
    
    
    public function DownloadSelfUpdate() //Загрузка обновлений программы
    {
        if($this->CONFIG['selfupdate'] == true)
        {
            $server     = @fsockopen(MASTERHOST, 80, $errno, $errstr, 1);
            
            $file       = "http://".MASTERHOST."/nod32ms.ver";
            $temp_file  = $this->CONFIG['temp_dir'].DS.'nod32ms.ver';
            $temp_dir   = $this->CONFIG['temp_dir'].DS;
        
            if($server)
            {
                if($this->files->CheckFile($temp_file))
                {
                    $new_file = $this->parser->LoadParseFile($file);  
                    $old_file = $this->parser->LoadParseFile($temp_file); 

                    $new['version_number']      = $this->parser->ParseValueOne($file, 'version_number'); 
                    $old['version_number']      = $this->parser->ParseValueOne($temp_file, 'version_number');

                    if($new['version_number'] > $old['version_number'])
                    {
                        $this->files->DeleteFile($temp_file);
                        $this->tools->Wget($file, $temp_dir);
                        
                        $new['version_title']   = $this->parser->ParseValueOne($file, 'version_title'); 
                        $new['update_date']     = $this->parser->ParseValueOne($file, 'update_date');
                        $new['update_file']     = $this->parser->ParseValueOne($file, 'update_file');
                        $new['update_size']     = $this->parser->ParseValueOne($file, 'update_size');
                        
                        $this->tools->Wget('http://'.MASTERHOST.'/files/'.$new['update_file'], SELF);

                        if(filesize(SELF.$new['update_file']) == $new['update_size'])
                        {     
                            $this->tools->CLI('cd '.SELF.' && tar -xvjpf '.SELF.$new['update_file']);
                            $this->files->DeleteFile(SELF.$new['update_file']);
                            $this->WriteToLog("[SELF UPDATE .... OK ... NEW VERSION ... ".$new['version_title']."] "); 
                        }
                        else
                        {
                            $this->files->DeleteFile(SELF.$new['update_file']);
                            $this->WriteToLog("[SELF UPDATE ERROR / DIFFERENCE FILE SIZE]");    
                        }
                    }
                }
                else
                {
                    $this->tools->Wget($file, $temp_dir);
                }

                @fclose($server);
            }        
        }   
    }
	
	
    
    public function DownloadSignature() //Загрузка файлов сигнатуры
    {  
        if((count($this->FILE['file']) > 0) AND (count($this->KEYS['login']) > 0) AND (count($this->FILE['VF']) > 0))
        {
            $this->WriteToLog("SELECTED TO UPDATE '".count($this->FILE['file'])."' FILES TOTAL SIZE OF '".$this->GetUpdateSize()."'");
            $this->WriteToLog("VERSION SIGNATURE '".$this->GetUpdateVersion()."'");
            
            for($i=0; $i < count($this->FILE['file']); $i++)
            {
            $file = $this->CONFIG['work_dir'].str_replace("/", DS, $this->FILE['file'][$i]);
			$modfile=$this->getmodfilename($file);
            $size = $this->FILE['size'][$i];
            
                        
                $random_numeric = mt_rand(0, count($this->KEYS['login'])-1);
                //$random_mirror	= mt_rand(0, count($this->HOST);
                $url  = "http://".$this->KEYS['login'][$random_numeric].":".$this->KEYS['password'][$random_numeric]."@".$this->CONFIG['mirror'];
                
                if($this->files->CheckFile($modfile))
                { 
                    if((filesize($modfile) + 5) == $size OR 
                	filesize($modfile) == ($size + 22) OR 
                	filesize($modfile) == ($size + 1) OR 
                	empty($size))
                    {
                        continue(1);    
                    }
                
            	    $old_size = filesize($modfile);

                    if($old_size <> $size)
                    {
                        $this->files->DeleteFile($modfile); 
                        $this->tools->Wget($url.$this->FILE['file'][$i], dirname($modfile).DS);
                        $old_size?$old_size:$old_size=0;
                        $this->WriteToLog("REPLACE OLD SIGNATURE FILE [".$this->CONFIG['mirror']." [".$file."] [".$old_size."/".$size."]");    
                    }   
                }
                else
                {   
                    $this->files->CreateDir(dirname($modfile));
                    
                    $this->tools->Wget($url.$this->FILE['file'][$i], dirname($modfile).DS);
                    $this->tools->CLI("chmod +x -R ".$modfile);
                    if($this->files->CheckFile($modfile))
                    {
                        $this->WriteToLog("DOWNLOAD NEW SIGNATURE FILE [".$this->CONFIG['mirror']."] [".$file."] [".$size."]");    
                    }
                    else
                    {
                        $this->files->DeleteFile(SYSTEM."keys.txt");
                    }      
                }    
    	}
             
            foreach($this->FILE['VF'] as $value)
            {				
				$section=$this->parser->ParseSectionVar($this->CONFIG['temp_dir'].DS.$value.DS.'update.ver');
				foreach($section as $sn=>$sv) {
					foreach($sv as $key=>$val) {
						if ($key=='file') {
							$section[$sn][$key]=$this->getmodfilename($val);
							$section[$sn][$key];
						}
					}
				}
				
		$work_path = $this->CONFIG['work_dir'].DS.$value.DS;
                if (!is_dir($work_path)) {
                    mkdir($work_path,0777,true);
                }
                $this->files->DeleteFile($work_path.DS.'update.ver');
		$this->parser->write_ini_file($section,$work_path.'update.ver',true);
                
                if($this->files->CheckFile($work_path.'update.ver'))
                {
                    $this->WriteToLog("COPY TEMP [update.ver] TO [".$work_path."update.ver]");    
                }
                else
                {
                    $this->SetError(__METHOD__, "FILE [".$this->CONFIG['temp_dir'].DS.$value.DS.'update.ver'."] NOT COPY TO [".$work_path.DS."");
                }  
            }
        }    
    unset($this->FILE);
    }
    
}
?>
