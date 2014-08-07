<?php
require_once "inc/init.php";

$nod32ms = new nod32ms();

$nod32ms->DownloadUpdateVer('eset_upd', false);
$nod32ms->DownloadUpdateVer('eset_upd/v4', false);
#$nod32ms->DownloadUpdateVer('eset_upd/v5', false);
#$nod32ms->DownloadUpdateVer('eset_upd/v6', false);

$nod32ms->ParseUpdateVer('eset_upd'); 
$nod32ms->ParseUpdateVer('eset_upd/v4');
#$nod32ms->ParseUpdateVer('eset_upd/v5');
#$nod32ms->ParseUpdateVer('eset_upd/v6');

//print_r($nod32ms->KEYS);
$nod32ms->ReadKeys();
$nod32ms->DownloadSignature();
//$nod32ms->DownloadSelfUpdate();
?>
