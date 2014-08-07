<?php
ini_set('display_errors',1);
require_once 'inc/classes/parser.class.php';
$parser   = new parser();
function write_ini_file($assoc_arr, $path, $has_sections=FALSE) { 
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
                    $content .= $key."[] = \"".$elem[$i]."\"\n"; 
                } 
            } 
            else if($elem=="") $content .= $key." = \n"; 
            else $content .= $key." = \"".$elem."\"\n"; 
        } 
    } 

    if (!$handle = fopen($path, 'w')) { 
        return false; 
    }

    $success = fwrite($handle, $content);
    fclose($handle); 

    return $success; 
}

/*function getmodfilename($file) {
	return substr(dirname($file),0,strrpos(dirname($file),'_'))."/".basename($file);
}*/
function getmodfilename($file) {
		$arr=explode('_',dirname($file));
		if (count($arr)>3) {
			unset($arr[count($arr)-1]);
			return implode('_',$arr)."/".basename($file);
		} else
			return $file;		
	}

#$arr=parse_ini_file('nod32ms/eset_upd/update.ver',true,INI_SCANNER_RAW);
$arr=$parser->ParseSectionVar('nod32ms/eset_upd/update.ver');
print_r($arr);
foreach($arr as $sn=>$sv) {
	foreach($sv as $key=>$value) {
		if ($key=='file') {
			$arr[$sn][$key]=getmodfilename($value);
			$arr[$sn][$key];
		}
	}
}

write_ini_file($arr,'nod32ms/eset_upd/update_.ver',true);


?>