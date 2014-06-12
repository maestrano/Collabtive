<?php

function push_project_to_maestrano($project_id)
{
    ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting( E_ALL | E_STRICT);
    
    try {
        $maestrano = MaestranoService::getInstance();
        if (!$maestrano->isSoaEnabled() or !$maestrano->getSoaUrl()) { return; }

        $mno_proj=new MnoSoaProject();
        $mno_proj->setLocalEntityIdentifier($project_id);
        $mno_proj->send(null);
    } catch (Exception $ex) {
        // DO NOTHING
    }
}

?>

