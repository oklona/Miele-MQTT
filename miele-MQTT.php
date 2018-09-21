<?php
################################################################################################################################################
######
######		Miele-MQTT.php
######		Script by Ole Kristian Lona, to read data from Miele@home, and transfer through MQTT.
######		Version 0.1
######
################################################################################################################################################


################################################################################################################################################
######		getRESTData - Function used to retrieve REST data from server.
################################################################################################################################################

function getRESTData($url,$postdata,$method,$content,$authorization='')
{
	$ch = curl_init($url);                                                                      
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);                                                                     
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$headers=array();
	if(strlen($authorization)>> 0 ) {
		array_push($headers, 'Authorization: ' . $authorization);
	}
	
	if(strlen($content) >> 0 ) {
		array_push($headers, 'Content-Type: ' . $content);
	}
	
	
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	if ( strcmp($method,"POST" ) == 0 ) {
		curl_setopt($ch,CURLOPT_POSTFIELDS, $postdata);
	}
	$result = curl_exec($ch);
	if (curl_getinfo($ch,CURLINFO_RESPONSE_CODE) == 302 ) {
		$returndata=curl_getinfo($ch,CURLINFO_REDIRECT_URL);
	}
	else {
		$returndata=json_decode($result,true);
	}
	
 return $returndata;
}

################################################################################################################################################
######		prompt_silent - Function "borrowed" from https://www.sitepoint.com/interactive-cli-password-prompt-in-php/
######		Written by: Troels Knak-Nielsen
################################################################################################################################################
function prompt_silent($prompt = "Enter Password:") {
  if (preg_match('/^win/i', PHP_OS)) {
    $vbscript = sys_get_temp_dir() . 'prompt_password.vbs';
    file_put_contents(
      $vbscript, 'wscript.echo(InputBox("'
      . addslashes($prompt)
      . '", "", "password here"))');
    $command = "cscript //nologo " . escapeshellarg($vbscript);
    $password = rtrim(shell_exec($command));
    unlink($vbscript);
    return $password;
  } else {
    $command = "/usr/bin/env bash -c 'echo OK'";
    if (rtrim(shell_exec($command)) !== 'OK') {
      trigger_error("Can't invoke bash");
      return;
    }
    $command = "/usr/bin/env bash -c 'read -s -p \""
      . addslashes($prompt)
      . "\" mypassword && echo \$mypassword'";
    $password = rtrim(shell_exec($command));
    echo "\n";
    return $password;
  }
}

################################################################################################################################################
######		publish - Function to publish data to Mosquitto
################################################################################################################################################

function publish($mosquitto_command,$mosquitto_host,$topic, $pubdata) {
	if (strlen($mosquitto_host) >> 0 ) {
		$command = $mosquitto_command . " -t " . $topic . " -h " . $mosquitto_host . " -m " . $pubdata;
	}
	else {
		$command = $mosquitto_command . " -t " . $topic . " -m " . $pubdata;
	}
    $output = rtrim(shell_exec($command));
    return $output;
}

################################################################################################################################################
######		createconfig - Function to prompt for config data, and create config file.
################################################################################################################################################
function createconfig() {	
	$configcreated=false;
	$tokenscreated=false;
	
	$userid=readline("Username (email) to connect with: ");
	$password=prompt_silent("Please type your password: ");
	$country=readline('Please state country in the form of "no-no, en-en, etc.": ');

	$client_id=readline('Please input the client ID assigned to you by Miele API administrators: ');
	$client_secret=readline('Please input the Client Secret assigned to you by Miele: ');
	
	$mosquitto_command=readline('Type the full path to your mosquitto_pub binary: ');
	$mosquitto_host=readline("Type the name of your mosquitto host (leave blank if localhost): ");
	$topicbase=readline('Type the base topic name to use for Mosquitto (default: "/miele/": ');
	if (strlen($topicbase) == 0) {
		$topicbase="/miele/";
	}
	if (substr($topicbase,-1) <> "/") {
		$topicbase = $topicbase . "/";
	}

	$authorization='';
	$url="https://api.mcs3.miele.com/thirdparty/auth";
	$postdata='email=' . urlencode($userid) . '&password=' . urlencode($password) . '&redirect_uri=%2Fv1%2Fdevices&state=login&response_type=code&client_id=' . $client_id . '&vgInformationSelector=' . $country;

	$method="POST";
	$content="application/x-www-form-urlencoded";

	$data=getRESTData($url,$postdata,$method,$content);

	if (is_array($data) == FALSE){
		$params=(explode('?',$data))[1];
		foreach (explode('&', $params) as $part) {
			$param=explode("=",$part);
			
			if(strstr($param[0],'code') <> FALSE ) {
				$code=$param[1];
			}
		}
	}
	else {
		return $configcreated;
	}

	if (strlen($code) >> 0 ) {
		$url='https://api.mcs3.miele.com/thirdparty/token?client_id=' . urlencode($client_id) . '&client_secret=' . $client_secret . '&code=' . $code . '&redirect_uri=%2Fv1%2Fdevices&grant_type=authorization_code&state=token';
		$postdata="";
		$method='POST';
		$data=getRESTData($url,$postdata,$method,$content);
		$access_token = $data["access_token"];
		$refresh_token = $data["refresh_token"];
		$tokenscreated = true;
	}

	if($tokenscreated == true ) {
		$config="<?php" . PHP_EOL . "return array(" . PHP_EOL . "        'access_token'=> '" . $access_token . "'," . PHP_EOL . "        'refresh_token'=> '" . $refresh_token . "'," . PHP_EOL;
		$config = $config . "	'mosquitto_command'=> '" . $mosquitto_command . "'," . PHP_EOL;
		$config = $config . "	'mosquitto_host'=> '" . $mosquitto_host . "'," . PHP_EOL;
		$config = $config . "	'topicbase'=> '" . $topicbase . "'" . PHP_EOL;
		$config = $config . ");" . PHP_EOL . "?>" . PHP_EOL . PHP_EOL;

		if (file_put_contents("miele-config.php", $config) <> false ) {
			echo "Configuration file created!" . PHP_EOL;
			$configcreated=true;
		}
	}

	return $configcreated;
}

################################################################################################################################################
######
######		This is the main script block
######
################################################################################################################################################

if(count($argv) >> 1 ) {
	if ($argv[1] == '-d') {
		$dump=true;
	}
}
else {
	$dump=false;
}

if (file_exists('miele-config.php') == false ) {
	$configcreated=createconfig();
	if($configcreated == false) {
		exit("Failed to create config! Did you type the correct credentials?" . PHP_EOL);
	}
}

$config = include('miele-config.php');



$authorization='';

if (strlen($config['access_token']) >> 0 ) {
	$url='https://api.mcs3.miele.com/v1/devices/';
	$authorization='Bearer ' . $config['access_token'];
	$topicbase=$config['topicbase'];
	$mosquitto_command= $config['mosquitto_command'];
	$mosquitto_host= $config['mosquitto_host'];
	$method='GET';
	$data=getRESTData($url,'',$method,'',$authorization);
	if ($dump == true ) {
		var_dump($data);
	}
}

if ($dump == false) {
	foreach ($data as $appliance) {
		$appliance_id=$appliance['ident']['deviceIdentLabel']['fabNumber'];
		echo "Data for appliance: " . $appliance_id . PHP_EOL;
		$appliance_type=$appliance['ident']['type']['value_localized'];
		switch ($appliance_type) {
			case "Dishwasher":
				$programStatus=$appliance['state']['status']['value_localized'];
				$programType=$appliance['state']['programType']['value_raw'];
				$programPhaseRaw=$appliance['state']['programPhase']['value_raw'];
				switch ($programPhaseRaw) {
					case "1792":
						// Purpose unknown, observed when programmed (without phase) and off.
						$programPhase="'Not running'";
						break;
					case "1794":
						$programPhase="'Pre-wash'";
						break;
					case "1795":
						$programPhase="'Main wash'";
						break;
					case "1796":
						$programPhase="'Rinse'";
						break;
					case "1798":
						$programPhase="'Final rinse'";
						break;
					case "1799":
						$programPhase="'Drying'";
						break;
					default:
						$programPhase="'Unknown: " . $programPhaseRaw . "'";
						break;
				}
				$timeleft=sprintf("%'.02d:%'.02d",$appliance['state']['remainingTime'][0],$appliance['state']['remainingTime'][1]);
				$timerunning=sprintf("%'.02d:%'.02d",$appliance['state']['elapsedTime'][0],$appliance['state']['elapsedTime'][1]);
				$topicbase = $topicbase . $appliance_id . '/';
				publish($mosquitto_command,$mosquitto_host,$topicbase . "ApplianceType", $appliance_type );
				publish($mosquitto_command,$mosquitto_host,$topicbase . "ProgramStatus", $programStatus);
				publish($mosquitto_command,$mosquitto_host,$topicbase . "ProgramType", $programType);
				publish($mosquitto_command,$mosquitto_host,$topicbase . "ProgramPhase", $programPhase);
				publish($mosquitto_command,$mosquitto_host,$topicbase . "TimeLeft", $timeleft);
				publish($mosquitto_command,$mosquitto_host,$topicbase . "TimeRunning", $timerunning);
				echo "Appliance type: " . $appliance_type . PHP_EOL;
				echo "Program status: " . $programStatus . PHP_EOL;
				echo "Program type: " . $programType . PHP_EOL;
				echo "Program phase: " . $programPhase . PHP_EOL;
				echo "Time left: " . $timeleft . PHP_EOL;
				echo "Time elapsed: " . $timerunning . PHP_EOL . PHP_EOL;
				break;
			default:
				echo "Appliance type " . $appliance_type . " is not defined. Please define it, or send information to have it added." . PHP_EOL;
				break;
		}
	}
}

?>

