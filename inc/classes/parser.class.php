<?php
class parser
{  
    private function ParseCustom($handle, $pattern)
    {
        if(preg_match_all("/$pattern/", $handle, $result, PREG_PATTERN_ORDER))  
        {
            return $result;       
        }    
    } 
    
    public function ParseSectionVar($heandle)
    {
	$ini = file($heandle);
	
	foreach ($ini as $i)
	{
	    if (@preg_match('/\[(.+)\]/', $i, $matches)) 
	    {
		$last = $matches[1];
	    }
	    elseif (@preg_match('/(.+)=(.+)/', $i, $matches)) 
	    {
		$cats[$last][$matches[1]] = $matches[2];
	    }
	}
	return $cats;
    }

    public function LoadParseFile($filename)
    {
        return @file_get_contents($filename);
    }
    
    public function ParseVar($handle)
    {
        return $this->ParseCustom($handle, "(.+)=(.+)\s");    
    }
    
    public function ParseValueOne($handle, $value)
    {
        $result = $this->ParseCustom($handle, "$value=(.+)\s"); 
        return substr($result[1][0], 0, -1);   
    }
    
    public function ParseValue($handle, $value)
    {
        return $this->ParseCustom($handle, "$value=(.+)\s");    
    }
    
    public function ParseKey($handle)
    {
        return $this->ParseCustom($handle, "(.+):(.+)\s");   
    }
	
	public function write_ini_file($assoc_arr, $path, $has_sections=FALSE) { 		
		$content = ""; 
		if ($has_sections) { 
			foreach ($assoc_arr as $key=>$elem) { 
				$content .= "[".$key."]\n"; 
				foreach ($elem as $key2=>$elem2) { 
					if(is_array($elem2)) 
					{ 
						for($i=0;$i<count($elem2);$i++) 
						{ 
							$content .= $key2."[]=".$elem2[$i]."\r\n"; 
						} 
					} 
					else if($elem2=="") $content .= $key2."=\r\n"; 
					else $content .= $key2."=".$elem2."\r\n"; 
				} 
			} 
		} 
		else { 
			foreach ($assoc_arr as $key=>$elem) { 
				if(is_array($elem)) 
				{ 
					for($i=0;$i<count($elem);$i++) 
					{ 
						$content .= $key."[] = ".$elem[$i]."\n"; 
					} 
				} 
				else if($elem=="") $content .= $key." = \n"; 
				else $content .= $key." = ".$elem."\n"; 
			} 
		} 
	
		if (!$handle = fopen($path, 'w')) { 
			return false; 
		}
	
		$success = fwrite($handle, $content);
		fclose($handle); 
	
		return $success; 
	}
}  
?>
