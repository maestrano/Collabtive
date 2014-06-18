<?php

/**
 * Maestrano map table functions
 *
 * @author root
 */

class MnoSoaEntity extends MnoSoaBaseEntity {    
    public function getUpdates($timestamp)
    {
        MnoSoaLogger::info(__FUNCTION__ .  " start getUpdates (timestamp=" . $timestamp . ")");
        $msg = $this->callMaestrano("GET", "updates" . '/' . $timestamp);
        if (empty($msg)) { return false; }
        MnoSoaLogger::debug(__FUNCTION__ .  " after maestrano call");
        if (!empty($msg->projects) && class_exists('MnoSoaProject')) {
            MnoSoaLogger::debug(__FUNCTION__ .  " has projects");
            foreach ($msg->projects as $project) {
                MnoSoaLogger::debug(__FUNCTION__ .  " project id = " . $project->id);
                try {
                    $mno_org = new MnoSoaProject();
                    $mno_org->receive($project);
                } catch (Exception $e) {
                }
            }
        }
        
        MnoSoaLogger::info(__FUNCTION__ .  " getUpdates successful (timestamp=" . $timestamp . ")");
        return true;
    }
    
    public function process_notification($notification)
    {
        $status = false;
        $notification_entity = strtoupper(trim($notification->entity));

        MnoSoaLogger::debug("Notification = ". json_encode($notification));

        switch ($notification_entity) {
            case "PROJECTS":
                if (class_exists('MnoSoaProject')) {
                    $mno_org = new MnoSoaProject();
                    $status = $mno_org->receiveNotification($notification);
                }
                break;
        }
        
        return $status;
    }
}
