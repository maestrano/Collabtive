ALTER TABLE  `milestones_assigned` ADD  `status` INT( 1 ) NOT NULL DEFAULT  '1';
ALTER TABLE  `tasks_assigned` ADD  `status` INT( 1 ) NOT NULL DEFAULT  '1';
ALTER TABLE  `projekte_assigned` ADD  `status` INT( 1 ) NOT NULL DEFAULT  '1';

ALTER TABLE  `projekte` ADD  `mno_status` VARCHAR( 255 ) DEFAULT NULL;
ALTER TABLE  `milestones` ADD  `mno_status` VARCHAR( 255 ) DEFAULT NULL;
ALTER TABLE  `tasklist` ADD  `mno_status` VARCHAR( 255 ) DEFAULT NULL;
ALTER TABLE  `tasks` ADD  `mno_status` VARCHAR( 255 ) DEFAULT NULL;