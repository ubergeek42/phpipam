/* Update from v 1.02 to 1.03 **/
UPDATE `settings` set `version` = '1.03'; 	/* UPDATE version */
/*set switch to 0 to avoid changelog */
update `ipaddresses` set `switch` = 0 where `switch` is null;
/* add mail notification for changelog */
ALTER TABLE `users` ADD `mailChangelog` SET('Yes','No')  NULL  DEFAULT 'No'  AFTER `mailNotify`;