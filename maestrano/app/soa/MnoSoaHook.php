<?php

$projects_list = (object) array();

register_shutdown_function("push_projects_to_maestrano_on_shutdown");

function push_project_to_maestrano($project_id)
{
    global $projects_list;
    
    ini_set('display_errors',1);
    ini_set('display_startup_errors',1);
    error_reporting( E_ALL | E_STRICT);
    
    if (empty($project_id)) { return; }
    
    $projects_list->{$project_id} = true;
}

function push_projects_to_maestrano_on_shutdown() 
{
    global $projects_list;
    
    $maestrano = MaestranoService::getInstance();
    if (!$maestrano->isSoaEnabled() or !$maestrano->getSoaUrl()) { return; }
    
    foreach ($projects_list as $project_id => $status) {
        try {
            $mno_proj=new MnoSoaProject();
            $mno_proj->setLocalEntityIdentifier($project_id);
            $mno_proj->send(null);
        } catch (Exception $ex) {
            // DO NOTHING
        }
    }
}

?>

