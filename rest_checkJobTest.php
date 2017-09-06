<?PHP
require_once('c2mAPIRest.php');
$c2m = new c2mAPIRest("username","password","stage"); //Change stage to live for production
$c2m->jobId = 12345;
print_r($c2m->job_checkStatus());