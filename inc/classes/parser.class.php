<?php
class parser
{  
    public function ParseSectionVar($filename,$has_sections=true)
    {
		$ini = preg_split('/\r?\n/',file_get_contents($filename));
		$cats=array();
		if ($has_sections) {
			foreach ($ini as $i)
			{
				if (@preg_match('/^\s*\[\s*(.+?)\s*\]\s*$/i', $i, $matches)) {
					$last = $matches[1];
				} elseif (@preg_match('/^\s*(.+?)\s*=\s*(.+?)\s*$/i', $i, $matches)) {
					$cats[$last][$matches[1]] = $matches[2];
				}
			}
		} else {
			foreach ($ini as $i)
			{
				if (@preg_match('/^\s*(.+?)\s*[=:]\s*(.+?)\s*$/i', $i, $matches)) {
					$cats[$matches[1]] = $matches[2];
				}
			}
		}
		return $cats;
    }

    public function ParseKeyVal($filename)
    {		
		$section=$this->ParseSectionVar($filename,false);				    
		return $section;
    }
    
    public function ParseValueOne($filename, $key, $def='')
    {
		$res=$this->ParseKeyVal($filename);
		if (isset($res[$key])) {
			return $res[$key];
		}
		return $def;        
    }
    
     
//    public function ParseKey($filename)
//    {
//		if(preg_match("/^\s*(.+?)\s*[=:]\s*(.+?)\s*$/i"), $filename, $result) {
//			return $result;			
//		}
//    }
	
	public function write_ini_file($assoc_arr, $path, $has_sections=FALSE) { 		
		$content = ""; 
		if ($has_sections) { 
			foreach ($assoc_arr as $key=>$elem) { 
				$content .= "[".$key."]\r\n"; 
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
						$content .= $key."[]=".$elem[$i]."\r\n"; 
					} 
				} 
				else if($elem=="") $content .= $key."=\r\n"; 
				else $content .= $key."=".$elem."\r\n"; 
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
