<?php

class c2mAPIBatch {

	private $url;
	private $username;
	private $password;
	private $xml;
	public  $batchId =0;
	private $pathToFile;
	private $batchUrlStage = "https://stage-batch.click2mail.com/v1";
	private $batchUrl = "https://batch.click2mail.com/v1";
	private $mode=0;
	private $filename;
	public function getBatchUrl()
	{
		if($this->mode == 0)
		{
		return $this->batchUrlStage;
		}
		else
		{
			return $this->batchUrl;
		}
	}
	
	public function c2mAPIBatch($username, $password,$live) {
		
		if($live =="live")
		{
			$this->mode = 1;
		}
		else
		{
			$this->mode = 0;
		}
		$this->url = $this->getBatchUrl();
		
		
		$this -> username = $username;
		$this -> password = $password;
		$this->newBatch();

	}
	public function newBatch()
	{
		$this -> xml = new SimpleXMLElement('<batch/>');
		$this -> xml -> addChild('username', $this -> username);
		$this -> xml -> addChild('password', $this -> password);
		$this -> xml -> addChild('filename', $this ->filename);
		$res = $this->HttpClient("POST", "/batches");
		$this->batchId = $res->id;
	}
	public function addFile($filename, $path) {
		$this->filename= $filename;
		$this -> pathToFile = $path;			
	}

	public function addJob($startPage, $endPage, $printOptions, $returnAddress, $recipients) {
		$job = $this -> xml -> addChild('job');
		$job -> addChild('startingPage', $startPage);
		$job -> addChild('endingPage', $endPage);

		$printProductionOptions = $job -> addChild('printProductionOptions');
		$printProductionOptions -> addChild("documentClass", $printOptions -> documentClass);
		$printProductionOptions -> addChild("layout", $printOptions -> layout);
		$printProductionOptions -> addChild("productionTime", $printOptions -> productionTime);
		$printProductionOptions -> addChild("envelope", $printOptions -> envelope);
		$printProductionOptions -> addChild("color", $printOptions -> color);
		$printProductionOptions -> addChild("paperType", $printOptions -> paperType);
		$printProductionOptions -> addChild("printOption", $printOptions -> printOption);
		$printProductionOptions -> addChild("mailClass", $printOptions -> mailClass);

		$returnAddressXml = $job -> addChild('returnAddress');
		$returnAddressXml -> addChild("name", $returnAddress -> name);
		$returnAddressXml -> addChild("organization", $returnAddress -> organization);
		$returnAddressXml -> addChild("address1", $returnAddress -> address1);
		$returnAddressXml -> addChild("address2", $returnAddress -> address2);
		$returnAddressXml -> addChild("city", $returnAddress -> city);
		$returnAddressXml -> addChild("state", $returnAddress -> state);
		$returnAddressXml -> addChild("postalCode", $returnAddress -> postalCode);

		$recipientsXml = $job -> addChild('recipients');

		foreach ($recipients->addresses as $address) {
			$addressXml = $recipientsXml -> addChild('address');
			$addressXml -> addChild('name', $address -> name);
			$addressXml -> addChild('organization', $address -> organization);
			$addressXml -> addChild('address1', $address -> address1);
			$addressXml -> addChild('address2', $address -> address2);
			$addressXml -> addChild('address3', $address -> address3);
			$addressXml -> addChild('city', $address -> city);
			$addressXml -> addChild('state', $address -> state);
			$addressXml -> addChild('postalCode', $address -> postalCode);
			$addressXml -> addChild('country', $address -> country);
		}

	}


	public function submitBatch() {
		$batchId = (String)$this->batchId;
		echo "UPLOADING XML\n\n";
		$this->HttpClient("PUT", "/batches/{$batchId}", $this->xml->asXML());
		//echo $this->batch_UploadXML("test.xml");
		//return;
		echo "UPLOADING PDF\n\n";
		$this->HttpClient("FILE", "/batches/{$batchId}", $this->pathToFile);
		echo "UPLOADING SUBMITTING\n\n";
		$res = $this->HttpClient("POST", "/batches/{$batchId}");
		return $res;
	}

	public function getStatus() {
		$batchId = (String)$this->batchId;
		$res = $this->HttpClient("GET", "/batches/{$batchId}");
		return $res;
	}

	public function getXml() {
		return $this -> xml -> asXML();
	}

	public function HttpClient($method, $path, $data = null) {

		$curl_url = $this -> url . $path;
		$ch = curl_init();
		//curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		//curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
		curl_setopt($ch, CURLOPT_URL, $curl_url);

		if ($method == "POST") {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}

$bypass=false;
		if ($method == "FILE") {

			$post_data = file_get_contents($data);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/pdf'));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
			//curl_setopt($ch, CURLOPT_INFILESIZE, filesize($this->pathToFile));
			//curl_setopt($ch, CURLOPT_INFILE, ($in = fopen($this->pathToFile, 'r')));
			$bypass=true;
		}
		if ($method == "PUT") {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		
		}

		if ($method == "GET") {
			//Curl automatically defaults to a GET request if no other request is defined
			// Do nothing.
		}

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);
		if (curl_errno($ch)) {
			print curl_error($ch);
			print "<br>Unable to complete request.";
			exit();
		}
		curl_close($ch);

		try {
			
			if($bypass)
			{
				echo $response;
			//	exit;
			}
			
			return simplexml_load_string($response);
			
		} catch(Exception $e) {
			return $response;
			
		}
	}
		function batch_UploadXML($tmpfile)
	{
		$url = $this->getBatchUrl() . "/batches/" . $this->batchId;
			echo $url;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
		curl_setopt($ch, CURLOPT_PUT, 1);
		curl_setopt($ch, CURLOPT_INFILESIZE, filesize($tmpfile));
		curl_setopt($ch, CURLOPT_INFILE, ($in = fopen($tmpfile, 'r')));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/xml']);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
		fclose($in);
		
		if ($output === false)
		{
			curl_close($ch);
			return 'Curl error: ' . curl_error($ch);
		}
		else
		{
			curl_close($ch);
			return $output;
		}

		// Close the handle

		
	}

}

class printProductionOptions {
	public $documentClass;
	public $productionTime;
	public $layout;
	public $envelope;
	public $paperType;
	public $printOption;
	public $mailClass;
}

class returnAddress {
	public $name;
	public $organization;
	public $address1;
	public $address2;
	public $city;
	public $state;
	public $postalCode;
}

class recipients {
	public $addresses = array();

	public function addAddress($address) {
		$this -> addresses[] = $address;
	}

}

class address {
	public $name;
	public $organization;
	public $address1;
	public $address2;
	public $address3;
	public $city;
	public $state;
	public $postalCode;
	public $country;
}