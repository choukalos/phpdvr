<?php

require_once(dirname(__FILE__) . '/../config.php');
$crontab = new crontab_manager();
$new_cronjobs = array(
    '0 0 1 * * myjob.sh',
    '30 8 * * 6 myjob.sh > /dev/null 2>&1'
);
$crontab->append_cronjob($new_cronjobs);
$crontab->append_cronjob(array('15 5 * * 2 newjob.sh'));
$crontab->remove_cronjob('/30 8 \* \* 6 myjob.sh/');


?>
