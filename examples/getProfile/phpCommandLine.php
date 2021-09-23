<?php

# Usage: php ./phpWebPage.php -k key [-f 'fields'] [-b bioFormat] [-r]
# -k	Give the "key" (WikiTree ID or user_id) of the profile to retrieve
# -f	Comma-separated list of fields to retrieve
# -b	bioFormat: "html", "wiki", or "both"
# -r	If present, resolveRedirect is set so redirected profiles are followed.
#

$options = getopt("k:f:b:r");
if (array_key_exists("k", $options)) { $key = $options['k']; } else { $key = ''; }
if (array_key_exists("f", $options)) { $fields = $options['f']; } else { $fields = ''; }
if (array_key_exists("b", $options)) { $bioFormat = $options['b']; } else { $bioFormat = ''; }
if (array_key_exists("r", $options)) { $resolveRedirect = 1; } else { $resolveRedirect = 0; }

$data = array(
	'action' => 'getProfile',
	'key' => $key,
	'fields' => $fields,
	'bioFormat' => $bioFormat,
	'resolveRedirect' => $resolveRedirect
);
	  
# Prepare new cURL resource and POST our data
$curl = curl_init('https://api.wikitree.com/api.php');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
$result = curl_exec($curl);
	  
# handle curl error
if ($result === false) {
	# throw new Exception('Curl error: ' . curl_error($curl));
	//print_r('Curl error: ' . curl_error($curl));
	$resultHTML = "Error POSTing to API:".curl_error($curl);
} else {
	$json = json_decode($result);
	print_r($json);
}
# Close cURL session handle
curl_close($curl);