<?PHP
require_once('c2mAPIRest.php');
$c2m = new c2mAPIRest("username","password","stage"); //Change stage to live for production
$c2m->addAddress("John","Smith","MyCompany","1234 Test Street","Apt 2","Oak Brook","IL","60523","USA");
$c2m->addAddress("John","Smith2","MyCompany","1234 Test Street","Apt 2","Oak Brook","IL","60523","USA");
$file = 'test.pdf';
$output = $c2m->runAll("Letter 8.5 x 11","Address on Separate Page","Next Day","#10 Double Window","Black and White","White 24#","Printing Both sides",$file,$c2m->createAddressList());
print_r($output);
echo "\n\n";
echo "documentId: ".$c2m->documentId."\n\n";
echo "AddressListId: ".$c2m->addressListId."\n\n";
echo "jobId: ".$c2m->jobId."\n\n";
$c2m->clearJob(); //call this before you create your next job;
?>