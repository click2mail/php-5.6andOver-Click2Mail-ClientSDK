<?PHP
	    require_once('c2mAPIBatch.php');
		$c2m = new c2mAPIBatch("username","password","stage"); //Change stage to live for production
		$c2m->batchId = 12345;
		$result = $c2m->getStatus();
		echo "batchId: ".$c2m->batchId."\n\n";
		print_r($result);
?>
	
	