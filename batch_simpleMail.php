<?PHP
	    require_once('c2mAPIBatch.php');
		$c2m = new c2mAPIBatch("username","password","stage"); //change stage to live for production
		$c2m->addFile("MyFileName", "test.pdf");
		$printOptions = new printProductionOptions();
		$printOptions->documentClass = "Letter 8.5 x 11";
		$printOptions->productionTime = "Next Day";
		$printOptions->layout = "Address on Separate Page";
		$printOptions->color = "Full Color";
		$printOptions->paperType = "White 24#";
		$printOptions->printOption = "Printing both sides";
		$printOptions->mailClass = "First Class";
		$printOptions->envelope = "#10 Double Window";
		
		$returnAddress = new returnAddress();
		$returnAddress->name = "John Doe";
		$returnAddress->organization = "My Company";
		$returnAddress->address1 = "100 Street St";
		$returnAddress->city = "Arlington";
		$returnAddress->state = "VA";
		$returnAddress->postalCode = "22202";
		
		$address = new address();
		$address->name = "Vincent Senese";
		$address->organization = "My Company";
		$address->address1 = "1420 Kensington rd";
		$address->address2 = "ste 335";
		$address->city = "Oak Brook";
		$address->state = "IL";
		$address->postalCode = "60532";
		$address->country = "US";
		
		
		$rescipients = new recipients();
		$rescipients->addAddress($address);
		
		
		$c2m->addJob(1, 5, $printOptions, $returnAddress, $rescipients);
		$c2m->addJob(5, 10, $printOptions, $returnAddress, $rescipients);
		$c2m->submitBatch();
		$result = $c2m->getStatus();
		echo "batchId: ".$c2m->batchId."\n\n";
		print_r($result);
		
	
	