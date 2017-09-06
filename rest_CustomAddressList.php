<?PHP
require_once('c2mAPIRest.php');
$c2m = new c2mAPIRest("username","password","stage");//Change stage to live when you want to go to production
$arList = array();
$ar = array();
$ar['Name'] = "test";
$ar['Address1'] = "test";
$ar['Address2'] = "test2";
$ar['Address3'] ="test3";
$ar['CityStateZip'] ="test3";
$arList[] = $ar;
$ar = array();
$ar['Name'] = "testBlah";
$ar['Address1'] = "test";
$ar['Address2'] = "test2";
$ar['Address3'] ="test3";
$ar['CityStateZip'] ="test3";
$arList[] = $ar;
$file = 'test.pdf';
$xml = $c2m->createCustomAddressList($arList,5);//Set the arraylist and the address Mapping Id

$output = $c2m->runAll("Letter 8.5 x 11","Address on Separate Page","Next Day","#10 Double Window","Black and White","White 24#","Printing Both sides",$file,$xml);
print_r($output);
echo "\n\n";
echo "documentId: ".$c2m->documentId."\n\n";
echo "AddressListId: ".$c2m->addressListId."\n\n";
echo "jobId: ".$c2m->jobId."\n\n";
$c2m->clearJob();
?>