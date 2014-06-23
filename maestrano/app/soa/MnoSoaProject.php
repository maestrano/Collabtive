<?php

/**
 * Mno Organization Class
 */
class MnoSoaProject extends MnoSoaBaseProject
{
    protected $_local_entity_name = "PROJECTS";
    protected $_local_project_id = null;
    
    protected function pushProject() 
    {
        global $conn;
        
        // FETCH PROJECT
        $project_query =   "SELECT ID as id,name,'desc' as description,start as start_date, end as due_date,status,budget,mno_status
                            FROM    projekte
                            WHERE   id = '$this->_local_project_id'";
        $sel = $conn->query($project_query);
        $project = $sel->fetch(PDO::FETCH_ASSOC);
        if (!$project) { return null; }
        $project = (object) $project;
        
        // CAST DATES
        $this->_name = $this->push_set_or_delete_value($project->name);
        $this->_description = $this->push_set_or_delete_value($project->description);
        $this->_start_date = $this->map_date_to_mno_format($project->start_date);
        $this->_due_date = $this->map_date_to_mno_format($project->due_date);
        $this->_status = $this->map_project_status_to_mno_format($project->status,$project->mno_status);

        $mno_project_id_obj = MnoSoaDB::getMnoIdByLocalId($this->_local_project_id, "PROJECTS", "PROJECTS");
        
        $this->_id = (MnoSoaDB::isValidIdentifier($mno_project_id_obj)) ? $mno_project_id_obj->_id : null;
        
        // IF INITIAL PUSH
        if (empty($this->_id)) {
            if (empty($_SESSION['userid'])) { return; }
            $mno_project_owner_id = MnoSoaDB::getMnoUserIdByLocalUserId($_SESSION['userid']);
            if (empty($mno_project_owner_id)) { return; }
            $this->_project_owner = $mno_project_owner_id;
        }
    }
    
    protected function pullProject() 
    {
        global $conn;
        
        MnoSoaLogger::debug("start");
        
        $local_project_id_obj = MnoSoaDB::getLocalIdByMnoId($this->_id, $this->getMnoEntityName(), $this->getLocalEntityName());
        
        MnoSoaLogger::debug("after getLocalIdByMnoId");
        
        $name = $this->pull_set_or_delete_value($this->_name);
        $description = $this->pull_set_or_delete_value($this->_description);
        $start_date = $this->map_date_to_local_format($this->_start_date);
        $end_date = $this->map_date_to_local_format($this->_due_date);
        $status = $this->map_project_status_to_local_format($this->_status);
        $mno_status = $this->pull_set_or_delete_value($this->_status, null);
        
        MnoSoaLogger::debug("before isValidIdentifier");
        
        if (MnoSoaDB::isValidIdentifier($local_project_id_obj)) {
            $this->_local_project_id = $local_project_id_obj->_id;
            
            MnoSoaLogger::debug("before db connection");
            
            $ins1Stmt = $conn->query("UPDATE projekte 
                                        SET `name`='$name', `desc`='$description', `end`='$end_date', `start`='$start_date', `status`='$status', `mno_status`='$mno_status'
                                        WHERE `ID`='{$this->_local_project_id}' ");
            MnoSoaLogger::debug("after db connection");
            
            return true;
        } else if (!MnoSoaDB::isDeletedIdentifier($local_project_id_obj)) {
            $ins1Stmt = $conn->prepare("INSERT INTO projekte (`name`, `desc`, `end`, `start`, `status`, `mno_status`) VALUES (?,?,?,?,?,?)");
            $ins1 = $ins1Stmt->execute(array($name, $description, $end_date, $start_date, $status, $mno_status));
            
            if ($ins1) {
                $this->_local_project_id = $conn->lastInsertId();
                MnoSoaDB::addIdMapEntry($this->_local_project_id, $this->getLocalEntityName(), $this->_id, $this->getMnoEntityName());
                return true;
            } 
            
            return false;
        }
    }    
    
    protected function pushParent() {
        // DO NOTHING
    }
    
    protected function pullParent() {
        // DO NOTHING
    }
    
    protected function pushProjectOwner() {
        // DO NOTHING
    }
    
    protected function pullProjectOwner() {
        // DO NOTHING
    }
    
    protected function pushStakeholders() 
    {       
        global $conn;
        
        $assigned_query =   "SELECT user as id, status
                             FROM projekte_assigned
                             WHERE projekt = '$this->_local_project_id'";
        $sel = $conn->query($assigned_query);
        $stakeholders = (object) array();

        while ($stakeholder = $sel->fetch(PDO::FETCH_ASSOC)) {
            $local_stakeholder_id = $stakeholder['id'];
            $mno_user_id = MnoSoaDB::getMnoUserIdByLocalUserId($local_stakeholder_id);
            MnoSoaLogger::debug(__CLASS__ . ' ' . __FUNCTION__ . " mno_user_id=".$mno_user_id);
            $status = $this->map_user_status_to_mno_format($stakeholder['status']);
            if (empty($mno_user_id) || $status === null) { continue; }
            $stakeholders->{$mno_user_id} = $status;
        }
        
        if (!empty($stakeholders)) {
            $this->_stakeholders = $stakeholders;
        }
    }
    
    protected function pullStakeholders() 
    {        
        global $conn;
    
        // UPSERT STAKEHOLDERS
        $assigned_query =   "START TRANSACTION; ";
        if (!empty($this->_stakeholders)) {
            foreach ($this->_stakeholders as $mno_user_id => $mno_status) {
                $local_user_id = MnoSoaDB::getLocalUserIdByMnoUserId($mno_user_id);
                $local_status = $this->map_user_status_to_local_format($mno_status);
                if (empty($local_user_id) || $local_status===null) { continue; }
                
                $select_query =   " SELECT  ID
                                    FROM    projekte_assigned 
                                    WHERE   user='$local_user_id' AND projekt='$this->_local_project_id'";
                $sel = $conn->query($select_query);
                $select_query_obj = $sel->fetch(PDO::FETCH_ASSOC);
                
                if (!$select_query_obj) {  
                    $assigned_query .=   "  INSERT INTO projekte_assigned(user,projekt,status) VALUES ('$local_user_id','$this->_local_project_id','$local_status'); ";
                } else {
                    $assigned_query .=   "  UPDATE projekte_assigned SET status='$local_status' WHERE user='$local_user_id' and projekt='$this->_local_project_id'; ";
                }
            }
        }
        $assigned_query .=  "COMMIT;";
        MnoSoaLogger::debug("stakeholders query=".$assigned_query);
        $conn->query($assigned_query);
    }
    
    protected function pushMilestones() 
    {
        global $conn;
        
        // FETCH MILESTONES
        $milestones_query = "SELECT ID as id,name,`desc` as description,start as start_date,end as due_date,status,mno_status
                            FROM milestones
                            WHERE project = '$this->_local_project_id'";
        $sel = $conn->query($milestones_query);
        $milestones = (object) array();

        while ($milestone = $sel->fetch(PDO::FETCH_ASSOC)) {
            $milestone = (object) $milestone;
            // TRANSLATE TASKLIST LOCAL ID TO MNO ID
            $milestone_id = MnoSoaDB::getOrCreateMnoId($milestone->id, "MILESTONES", "MILESTONES");
            if (!MnoSoaDB::isValidIdentifier($milestone_id)) { continue; }
            // BUILD MNO OBJECT
            $mno_milestone = (object) array();
            $mno_milestone->name = $this->push_set_or_delete_value($milestone->name);
            $mno_milestone->description = $this->push_set_or_delete_value($milestone->description);
            $mno_milestone->startDate = $this->map_date_to_mno_format($milestone->start_date);
            $mno_milestone->dueDate = $this->map_date_to_mno_format($milestone->due_date);
            $mno_milestone->status = $this->map_project_status_to_mno_format($milestone->status, $milestone->mno_status);
            
            $milestones->{$milestone_id->_id} = $mno_milestone;
        }
        
        if (!empty($milestones)) {
            $this->_milestones = $milestones;
        }
    }
    
    protected function pullMilestones() 
    {
        global $conn;
        
        // UPSERT MILESTONES
        if (!empty($this->_milestones)) {
            foreach ($this->_milestones as $mno_milestone_id => $milestone) {
                $local_milestone_id_obj = MnoSoaDB::getLocalIdByMnoId($mno_milestone_id, "MILESTONES", "MILESTONES");
                $local_milestone_id = null;
                
                $name = $this->pull_set_or_delete_value($milestone->name);
                $description = $this->pull_set_or_delete_value($milestone->description);
                $start_date = $this->map_date_to_local_format($milestone->startDate);
                $due_date = $this->map_date_to_local_format($milestone->dueDate);
                $status = $this->map_project_status_to_local_format($milestone->status);
                $mno_status = $this->pull_set_or_delete_value($milestone->status, null);

                if (MnoSoaDB::isValidIdentifier($local_milestone_id_obj)) {
                    $local_milestone_id = $local_milestone_id_obj->_id;
                    $ins1Stmt = $conn->query("UPDATE milestones 
                                                SET `name`='$name', `desc`='$description', `end`='$due_date', `start`='$start_date', `status`='$status', `mno_status`='$mno_status'
                                                WHERE `ID`='{$local_milestone_id}' ");
                } else if (!MnoSoaDB::isDeletedIdentifier($local_milestone_id_obj)) {
                    $ins1Stmt = $conn->prepare("INSERT INTO milestones (`project`, `name`, `desc`, `end`, `start`, `status`, `mno_status`) VALUES (?,?,?,?,?,?,?)");
                    $ins1 = $ins1Stmt->execute(array($this->_local_project_id, $name, $description, $due_date, $start_date, $status, $mno_status));
                    
                    if ($ins1) {
                        $local_milestone_id = $conn->lastInsertId();
                        MnoSoaDB::addIdMapEntry($local_milestone_id, "MILESTONES", $mno_milestone_id, "MILESTONES");
                    }
                }
            }
        }
    }
    
    protected function pushTasklists() 
    {
        global $conn;
        
        $tasklists_query = "SELECT ID as id,name,`desc` as description,start as start_date,milestone as milestone_id,status,mno_status
                            FROM tasklist
                            WHERE project = '$this->_local_project_id'";
        $sel = $conn->query($tasklists_query);
        $tasklists = (object) array();

        while ($tasklist = $sel->fetch(PDO::FETCH_ASSOC)) {
            $tasklist = (object) $tasklist;
            
            // TRANSLATE TASKLIST LOCAL ID TO MNO ID
            $tasklist_id = MnoSoaDB::getOrCreateMnoId($tasklist->id, "TASKLISTS", "TASKLISTS");
            if (!MnoSoaDB::isValidIdentifier($tasklist_id)) { continue; }
            // TRANSLATE MILESTONE LOCAL ID TO MNO ID
            if (!empty($tasklist->milestone_id)) {
                $milestone_id = MnoSoaDB::getOrCreateMnoId($tasklist->milestone_id, "MILESTONES", "MILESTONES");
                $milestone_id = (MnoSoaDB::isValidIdentifier($milestone_id)) ? $milestone_id->_id : "";
            } else {
                $milestone_id = "";
            }
            
            $mno_tasklist = (object) array();
            
            $mno_tasklist->name = $this->push_set_or_delete_value($tasklist->name);
            $mno_tasklist->description = $this->push_set_or_delete_value($tasklist->description);
            $mno_tasklist->startDate = $this->map_date_to_mno_format($tasklist->start_date);
            $mno_tasklist->status = $this->map_project_status_to_mno_format($tasklist->status, $tasklist->mno_status);
            $mno_tasklist->milestone = $milestone_id;
            
            $tasklists->{$tasklist_id->_id} = $mno_tasklist;
        }
        
        if (!empty($tasklists)) {
            $this->_tasklists = $tasklists;
        }
    }
    
    protected function pullTasklists() 
    {
        global $conn;
        
        // UPSERT TASKLISTS - DONE!
        if (!empty($this->_tasklists)) {
            foreach($this->_tasklists as $mno_tasklist_id => $tasklist) {
                $local_tasklist_id_obj = MnoSoaDB::getLocalIdByMnoId($mno_tasklist_id, "TASKLISTS", "TASKLISTS");
                $local_tasklist_id = null;

                $name = $this->pull_set_or_delete_value($tasklist->name);
                $description = $this->pull_set_or_delete_value($tasklist->description);
                $start = $this->map_date_to_local_format($tasklist->startDate);
                $status = $this->map_project_status_to_local_format($tasklist->status);
                $mno_status = $this->pull_set_or_delete_value($tasklist->status, null);
                if (!empty($tasklist->milestone)) {
                    $milestone = MnoSoaDB::getLocalIdByMnoId($tasklist->milestone, "MILESTONES", "MILESTONES");
                    $milestone_id = (MnoSoaDB::isValidIdentifier($milestone)) ? $milestone->_id : 0;
                } else {
                    $milestone_id = 0;
                }
                $local_project_id = $this->_local_project_id;

                if (MnoSoaDB::isValidIdentifier($local_tasklist_id_obj)) {
                    $local_tasklist_id = $local_tasklist_id_obj->_id;
                    
                    $ins1Stmt = $conn->query("  UPDATE tasklist 
                                                SET `name`='$name', `desc`='$description', `start`='$start', `status`='$status', `milestone`='$milestone_id', `mno_status`='$mno_status'
                                                WHERE `ID`='{$local_tasklist_id}' ");
                } else if (!MnoSoaDB::isDeletedIdentifier($local_tasklist_id_obj)) {
                    $ins1Stmt = $conn->prepare("INSERT INTO tasklist (`project`, `name`, `desc`, `start`, `status`, `milestone`, `mno_status`) VALUES (?,?,?,?,?,?,?)");
                    $ins1 = $ins1Stmt->execute(array($this->_local_project_id, $name, $description, $start, $status, $milestone_id, $mno_status));

                    if ($ins1) {
                        $local_tasklist_id = $conn->lastInsertId();
                        MnoSoaDB::addIdMapEntry($local_tasklist_id, "TASKLISTS", $mno_tasklist_id, "TASKLISTS");
                    }
                }
            }
        }
    }
    
    protected function pushTasks() 
    {
        global $conn;
        
        $tasks_query =  "SELECT ID as id,title as name,text as description,start as start_date,end as due_date,status,liste as tasklist_id,mno_status
                        FROM tasks
                        WHERE project = '$this->_local_project_id'";
        $sel = $conn->query($tasks_query);
        $tasks = (object) array();

        while ($task = $sel->fetch(PDO::FETCH_ASSOC)) {
            $task = (object) $task;
            // TRANSLATE TASK LOCAL ID TO MNO ID
            $task_id = MnoSoaDB::getOrCreateMnoId($task->id, "TASKS", "TASKS");
            if (!MnoSoaDB::isValidIdentifier($task_id)) { continue; }
            // TRANSLATE TASKLIST LOCAL ID TO MNO ID
            if (!empty($task->tasklist_id)) {
                $tasklist_id = MnoSoaDB::getOrCreateMnoId($task->tasklist_id, "TASKLISTS", "TASKLISTS");
                $tasklist_id = (MnoSoaDB::isValidIdentifier($tasklist_id)) ? $tasklist_id->_id : "";
            } else {
                $tasklist_id = "";
            }
            
            $mno_task = (object) array();
            
            $mno_task->name = $this->push_set_or_delete_value($task->name);
            $mno_task->description = $this->push_set_or_delete_value($task->description);
            $mno_task->status = $this->map_project_status_to_mno_format($task->status, $task->mno_status);
            $mno_task->startDate = $this->map_date_to_mno_format($task->start_date);
            $mno_task->dueDate = $this->map_date_to_mno_format($task->due_date);
            $mno_task->tasklist = $tasklist_id;
            
            // FETCH ASSIGNEES
            $task_assignees_query =  "SELECT user as id, status FROM tasks_assigned WHERE task = '$task->id'";
            $task_sel = $conn->query($task_assignees_query);
            $task_assignees = (object) array();

            while ($task_assignee = $task_sel->fetch(PDO::FETCH_ASSOC)) {
                $task_assignee_id = $task_assignee['id'];
                $mno_status = $this->map_user_status_to_mno_format($task_assignee['status']);
                
                $mno_task_assignee_id = MnoSoaDB::getMnoUserIdByLocalUserId($task_assignee_id);
                if (empty($mno_task_assignee_id) || $mno_status === null) { continue; }
                $task_assignees->{$mno_task_assignee_id} = $mno_status;
            }
            
            $mno_task->assignedTo = $task_assignees;

            $tasks->{$task_id->_id} = $mno_task;
        }
        
        if (!empty($tasks)) {
            $this->_tasks = $tasks;
        }
    }
    
    protected function pullTasks() 
    {
        global $conn;
        
        $local_task_ids = array();
        $local_tasks_assigned_user_ids = array();
    
        // UPSERT TASKS
        if (!empty($this->_tasks)) {
            foreach($this->_tasks as $mno_task_id => $task) {
                $local_task_id_obj = MnoSoaDB::getLocalIdByMnoId($mno_task_id, "TASKS", "TASKS");
                $local_task_id = null;

                $start = $this->map_date_to_local_format($task->startDate);
                $end = $this->map_date_to_local_format($task->dueDate);
                $title = $this->pull_set_or_delete_value($task->name);
                $title = (empty($title)) ? "No title" : $title;
                $text = $this->pull_set_or_delete_value($task->description);
                $local_tasklist_id_obj = MnoSoaDB::getLocalIdByMnoId($task->tasklist, "TASKLISTS", "TASKLISTS");
                $local_tasklist_id = (MnoSoaDB::isValidIdentifier($local_tasklist_id_obj)) ? $local_tasklist_id_obj->_id : 0;
                $status = $this->map_project_status_to_local_format($task->status);
                $mno_status = $this->pull_set_or_delete_value($task->status, null);
                $local_project_id = $this->_local_project_id;

                if (MnoSoaDB::isValidIdentifier($local_task_id_obj)) {
                    $local_task_id = $local_task_id_obj->_id;
      
                    $ins1Stmt = $conn->query("UPDATE tasks 
                                                SET `start`='$start', `end`='$end', `title`='$title', `text`='$text', `liste`='$local_tasklist_id', `status`='$status', `project`='$local_project_id', `mno_status`='$mno_status'
                                                WHERE `ID`='{$local_task_id}' ");
                } else if (!MnoSoaDB::isDeletedIdentifier($local_task_id_obj)) {
                    $ins1Stmt = $conn->prepare("INSERT INTO tasks (`start`, `end`, `title`, `text`, `liste`, `status`, `project`, `mno_status`) VALUES (?,?,?,?,?,?,?,?)");
                    $ins1 = $ins1Stmt->execute(array($start, $end, $title, $text, $local_tasklist_id, $status, $local_project_id, $mno_status));

                    if ($ins1) {
                        $local_task_id = $conn->lastInsertId();
                        MnoSoaDB::addIdMapEntry($local_task_id, "TASKS", $mno_task_id, "TASKS");
                    }
                }
                
                if (!empty($task->assignedTo)) {
                    foreach($task->assignedTo as $mno_user_id => $status) {
                        $local_user_id = MnoSoaDB::getLocalUserIdByMnoUserId($mno_user_id);
                        MnoSoaLogger::debug("local_user_id=".$local_user_id." mno_user_id=".$mno_user_id);
                        $local_status = $this->map_user_status_to_local_format($status);
                        if (empty($local_user_id) || $local_status===null) { continue; }
                        $assigned_array = array();
                        $assigned_array['user'] = $local_user_id;
                        $assigned_array['task'] = $local_task_id;
                        $assigned_array['status'] = $local_status;
                        array_push( $local_tasks_assigned_user_ids, $assigned_array);
                    }
                }
                
                array_push($local_task_ids, $local_task_id);
            }
        }
        
        // UPDATE ASSIGNED USERS TO PROJECT TASKS
        $assigned_users_stmt =    "START TRANSACTION; ";
        if (!empty($local_tasks_assigned_user_ids)) {
            
            foreach($local_tasks_assigned_user_ids as $local_task_user_record) {
                $user = $local_task_user_record['user'];
                $task = $local_task_user_record['task'];
                $status = $local_task_user_record['status'];
                
                
                $assigned_users_sel_stmt = "SELECT ID FROM tasks_assigned WHERE user='$user' and task='$task'";
                $assigned_users_sel = $conn->query($assigned_users_sel_stmt);
                $assigned_users_sel_obj = $assigned_users_sel->fetch(PDO::FETCH_ASSOC);
                
                if (!$assigned_users_sel_obj) {
                    $assigned_users_stmt .= " INSERT INTO tasks_assigned (`user`, `task`, `status`) VALUES ('$user','$task','$status'); ";
                } else {
                    $assigned_users_stmt .= " UPDATE tasks_assigned SET `status`='$status' WHERE `user`='$user' and `task`='$task';  ";
                }
            }
        }
        $assigned_users_stmt .= " COMMIT; ";
        MnoSoaLogger::debug("assigned_users_stmt=".$assigned_users_stmt);
        $conn->query($assigned_users_stmt);
    }
        
    protected function saveLocalEntity($push_to_maestrano, $status) 
    {
        //$this->_local_entity->save();
    }
    
    public function getLocalEntityIdentifier() 
    {
        return $this->_local_project_id;
    }
    
    public function setLocalEntityIdentifier($local_identifier)
    {
        $this->_local_project_id = $local_identifier;
    }
    
    public function getLocalEntityByLocalIdentifier($local_id)
    {
        return get_project_object($local_id);
    }
    
    public function createLocalEntity()
    {
        return (object) array();
    }
    
    public function map_date_to_local_format($date)
    {
        $date_format = $this->pull_set_or_delete_value($date);
        return (!empty($date_format) && ctype_digit($date_format)) ? (string) ((int) round(intval($date_format)/1000)) : "0";
    }
    
    public function map_date_to_mno_format($date)
    {
        $date_format = $this->push_set_or_delete_value($date);
        return (!empty($date_format) && ctype_digit($date_format)) ? (string) ((int) (intval($date_format)*1000)) : "0";
    }
    
    public function map_project_status_to_local_format($status)
    {
        if (empty($status)) { return 1; }

        switch ($status) {
            case "TODO": return 1;
            case "INPROGRESS": return 1;
            case "COMPLETED": return 0;
            case "ABANDONED": return 2;
        }
        return 1;
    }
    
    public function map_project_status_to_mno_format($status, $mno_status)
    {
        if (empty($status) && $status != 0) { return null; }
        
        switch ($status) {
            case "1": 
                if ($mno_status === null || $mno_status == "INPROGRESS") { return "INPROGRESS"; }
                else if ($mno_status == "TODO") { return "TODO"; }
                return "INPROGRESS";
            case "0": return "COMPLETED";
            case "2": return "ABANDONED";
        }
        return "INPROGRESS";
    }
    
    public function map_user_status_to_local_format($status)
    {
        if (empty($status)) { return 2; }
        
        switch ($status) {
            case "ACTIVE": return 1;
            case "INACTIVE": return 2;
        }
        
        return 1;
    }
    
    public function map_user_status_to_mno_format($status)
    {
        if (empty($status) && $status != 0) { return null; }
        
        switch ($status) {
            case "2": return "INACTIVE";
            case "1": return "ACTIVE";
            case "0": return "INACTIVE";
        }
        
        return null;
    }
}

?>