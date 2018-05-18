<?PHP
class c2mAPIRest
{
	function c2mAPIRest($un,$pw,$live)
	{
		$this->username = $un;
		$this->password = $pw;
		$this->addresses = new addresses();
		if(strtolower($live) =="live")
		{
			$this->mode = 1;
		}
		else
		{
			$this->mode = 0;
		}
	}
	
	public $LRestmainurl = "https://rest.click2mail.com";
	public $sRestmainurl = "https://stage-rest.click2mail.com";

	public $username = "";
	public $password = "";
	public $addresses;
	public $mode= 0;
	public $documentId = 0;
	public $addressListId = 0;
	public $addressListStatus = 0;
	public $jobId = 0;
		
    function mime_content_type($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }

	public function job_checkStatus()
	{
		$ar = array();
		$output =$this->rest_Call2($this->get_restUrl() . "/molpro/jobs/".$this->jobId,$ar,"GET");
		return $output;
	}
	public function addressList_GetStatus()
	{  
		
		$url = $this->get_restUrl(). "/molpro/addressLists/". $this->addressListId;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$output = curl_exec($ch);
		if ($output === false)
		{
			$response = 'Curl error: ' . curl_error($ch);
		}
		else
		{
			$xml = simplexml_load_string($output) or die("Error: Cannot create object");
			$response = $xml;
		}

		// Close the handle

		curl_close($ch);
		return $response;
	}
	public function document_create($file,$documentClass)
	{
	$docName = "PHP SDK ".substr( md5(rand()), 0, 7);
	$format =  strtoupper(pathinfo($file, PATHINFO_EXTENSION));
	$ar = array('documentName' => $docName, "documentClass" => $documentClass, "documentFormat" => $format, "file" => new CURLFile($file));
	$xmlDoc = $this->rest_Call($this->get_restUrl() . "/molpro/documents/",$ar,"POST");
	$this->documentId =  (string) $xmlDoc->id;
	}
	
	public function addressList_create($xml)
	{
		$xmlDoc = $this->rest_UploadXML($this->get_restUrl()."/molpro/addressLists/",$xml);

		$this->addressListStatus =  (string)$xmlDoc->status;
		
		if($this->addressListStatus == 9)
		{
			print_r($xmlDoc);
		}
		$this->addressListId =  (string)$xmlDoc->id;
		
		//print_r($this->addressList_GetStatus());
		//return;
		
					while ($this->addressListStatus != "3")
					{
						
						echo "Waiting Address List to finish processes.  Current Status is: " .$this->addressListStatus."\n\n";
						usleep(5000000);
						$xmlDoc = $this->addressList_GetStatus();
						$this->addressListStatus =  (string)$xmlDoc->status;			
					}
		return $xmlDoc;
	
	}
	public function job_create($documentClass,$layout,$productionTime,$envelope,$color,$paperType,$printOption)
	{
					$ar = array(
		 "documentClass" =>  $documentClass
		,"layout" => $layout
		,"productionTime"=> $productionTime
		,"envelope"=> $envelope
		,"color"=> $color
		,"paperType"=> $paperType
		,"printOption"=> $printOption
		,"documentId"=>$this->documentId
		,"addressId"=>$this->addressListId
		);

		$output =$this->rest_Call2($this->get_restUrl(). "/molpro/jobs/", $ar,"POST");
		$this->jobId = (string) $output->id;
		return $output;
	}
	public function job_Submit()
	{
		$ar = array(
		"billingType" => "User Credit"
		);
		$output =$this->rest_Call2($this->get_restUrl(). "/molpro/jobs/".$this->jobId."/submit/",$ar,"POST");
		return $output;
	}
	public function runAll($documentClass,$layout,$productionTime,$envelope,$color,$paperType,$printOption,$file,$xml)
	{
	echo "Document Uploading\n\n";
	$this->document_create($file,$documentClass);	
	echo "AddressList Uploading\n\n";
	$this->addressList_create($xml);
	echo "Job Create\n\n";
	$this->job_create($documentClass,$layout,$productionTime,$envelope,$color,$paperType,$printOption);
	echo "Job Submit\n\n";	
	$this->job_Submit();
	$output  = $this->job_checkStatus();
	return $output;
	}
	public function addAddress($first,$last,$org,$address1,$address2,$city,$state,$zip,$country)
	{
		$address = new address();
		$address->First_name = $first;
		$address->Last_name = $last;
		$address->organization = $org;
		$address->Address1 = $address1;
		$address->Address2 = $address2;
		$address->City = $city;
		$address->State = $state;
		$address->Zip = $zip;
		$address->Country_nonDASHUS = $country;
		$this->addresses->addAddress($address);
	}
	
	public function clearJob()
	{
		$this->addresses = new addresses();
		$this->jobId = 0;
		$this->addressListId = 0;
		$this->documentIdI= 0;
	}
	
	public function createAddressList(){
		$this -> addressListxml = new SimpleXMLElement('<addressList/>');
		$this -> addressListxml -> addChild('addressListName',"PHP SDK".substr( md5(rand()), 0, 7));
		$this -> addressListxml -> addChild('addressMappingId', '2');
		$addressesXml = $this -> addressListxml -> addChild('addresses');
		
		foreach ($this->addresses->addresses as $address) {
			$addressXml = $addressesXml -> addChild('address');
			foreach($address  as $key=>$value)
			{
							$addressXml -> addChild(str_ireplace("DASH","-",$key), $value);
			}
		}	
		return $this->addressListxml->asXML();
	}
	public function createCustomAddressList($addressListArray,$addressMappingId){
		$this -> addressListxml = new SimpleXMLElement('<addressList/>');
		$this -> addressListxml -> addChild('addressListName',"PHP SDK".substr( md5(rand()), 0, 7));
		$this -> addressListxml -> addChild('addressMappingId', $addressMappingId);
		$addressesXml = $this -> addressListxml -> addChild('addresses');
		foreach ($addressListArray as $aa) {
			$addressXml = $addressesXml -> addChild('address');
			foreach($aa as $key=>$value)
			{
							$addressXml -> addChild($key, $value);
			}
		}	
		return $this->addressListxml->asXML();
	}
	
	function get_restUrl()
	{
		if($this->mode == 0)
		{
			return $this->sRestmainurl;
		}
		else
		{
			return $this->LRestmainurl;
		}
	}
	
	function get_batchUrl()
	{
	
	}
	function rest_Call($url,$ar,$type)
	{
		    
            
			
			
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
			curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
			//curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $ar); 
			$output = curl_exec($ch);
			if ($output === false)
			{
				$response = 'Curl error: ' . curl_error($ch);
			}
			else
			{
			//	echo $output;
			if( strpos($output,"{")!== false)
				{
					$output = $this->json2xml($output);
				}
				
				$xml = simplexml_load_string($output) or die("Error: Cannot create object");
				
		//		$this->documentId = $xml->id;
				$response = $xml;
			}

			

			curl_close($ch);
			return $response;	
	}
	
	function rest_Call2($url,$ar,$type)
	{
      	    $fields_string = http_build_query($ar);
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
			curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string); 
			//curl_setopt($ch, CURLOPT_POST, 1);
			$output = curl_exec($ch);
			if ($output === false)
			{
				$response = 'Curl error: ' . curl_error($ch);
			}
			else
			{
				if( strpos($output,"{")!== false)
				{
					$output = $this->json2xml($output);
				}
				$xml = simplexml_load_string($output) or die("Error: Cannot create object");
		//		$this->documentId = $xml->id;
				$response = $xml;
			}

			

			curl_close($ch);
			return $response;	
	}
	function rest_UploadXML($url,$xml)
	{
		    
            
			
		
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/xml']);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml); 
			$output = curl_exec($ch);
			if ($output === false)
			{
				$response = 'Curl error: ' . curl_error($ch);
			}
			else
			{
				if( strpos($output,"{")!== false)
				{
					$output = $this->json2xml($output);
				}
				$xml = simplexml_load_string($output) or die("Error: Cannot create object");
				$response = $xml;
			}

			

			curl_close($ch);
			return $response;	
	}
	function json2xml($json) {
    $a = json_decode($json);
    $d = new DOMDocument();
    $c = $d->createElement("root");
    $d->appendChild($c);
    $t = function($v) {
        $type = gettype($v);
        switch($type) {
            case 'integer': return 'number';
            case 'double':  return 'number';
            default: return strtolower($type);
        }
    };
    $f = function($f,$c,$a,$s=false) use ($t,$d) {
        $c->setAttribute('type', $t($a));
        if ($t($a) != 'array' && $t($a) != 'object') {
            if ($t($a) == 'boolean') {
                $c->appendChild($d->createTextNode($a?'true':'false'));
            } else {
                $c->appendChild($d->createTextNode($a));
            }
        } else {
            foreach($a as $k=>$v) {
                if ($k == '__type' && $t($a) == 'object') {
                    $c->setAttribute('__type', $v);
                } else {
                    if ($t($v) == 'object') {
                        $ch = $c->appendChild($d->createElementNS(null, $s ? 'item' : $k));
                        $f($f, $ch, $v);
                    } else if ($t($v) == 'array') {
                        $ch = $c->appendChild($d->createElementNS(null, $s ? 'item' : $k));
                        $f($f, $ch, $v, true);
                    } else {
                        $va = $d->createElementNS(null, $s ? 'item' : $k);
                        if ($t($v) == 'boolean') {
                            $va->appendChild($d->createTextNode($v?'true':'false'));
                        } else {
                            $va->appendChild($d->createTextNode($v));
                        }
                        $ch = $c->appendChild($va);
                        $ch->setAttribute('type', $t($v));
                    }
                }
            }
        }
    };
    $f($f,$c,$a,$t($a)=='array');
    return $d->saveXML($d->documentElement);
}

}
class addresses {
		public $addresses = array();
		public function addAddress($address) {
			$this -> addresses[] = $address;
		}
	}

	class address {
	public $First_name;
	public $Last_name;
	public $Organization;
	public $Address1;
	public $Address2;
	public $Address3;
	public $City;
	public $State;
	public $Zip;
	public $Country_nonDASHUS;
}
?>
