<?PHP
require_once('c2mAPIRest.php');
$c2m = new c2mAPIRest("username","password","stage"); //Change stage to live for production
$c2m->addAddressCorrection("John Smith","1234 Test Street","Apt 2","Oak Brook","IL","60523");
$c2m->addAddressCorrection("John Smith2","1234 Test Street","Apt 2","Oak Brook","IL","60523");
$xml = $c2m->createAddressCorrectionList();
print_r($xml);
$url = $c2m->get_restUrl()."/molpro/addressCorrection";
print_r($url);
echo "\n\n";
$output = $c2m->rest_UploadXML($url,$xml);
print_r($output);
echo "\n\n";
?>

