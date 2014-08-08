<?php
ini_set('display_errors',1);
require_once 'inc/classes/parser.class.php';
$parser   = new parser();

function getmodfilename($file) {
	//return substr(dirname($file),0,strrpos(dirname($file),'_'))."/".basename($file);
	$p=explode('/',$file);
	//print_r($p);
	if(count($p)>1) {
		$p[count($p)-2] = preg_replace('/_\d+$/','',$p[count($p)-2]);
	}
	return implode('/',$p);
}
/*function getmodfilename($file) {
		$arr=explode('_',dirname($file));
		if (count($arr)>3) {
			unset($arr[count($arr)-1]);
			return implode('_',$arr)."/".basename($file);
		} else
			return $file;		
	}*/

#$arr=parse_ini_file('nod32ms/eset_upd/update.ver',true,INI_SCANNER_RAW);
$arr=$parser->ParseSectionVar('nod32ms/eset_upd/update.ver');
//print_r($arr);
foreach($arr as $sn=>$sv) {
	foreach($sv as $key=>$value) {
		if ($key=='file') {
			$arr[$sn][$key]=getmodfilename($value);
			$arr[$sn][$key];
		}
	}
}
//print_r($arr);

//$parser->write_ini_file($arr,'nod32ms/eset_upd/update_.ver',true);

//$config = $parser->LoadParseFile('nod32ms.conf');			
$array  = $parser->ParseKeyVal('nod32ms.conf');
print_r($array);


?>