<?php

$resultHTML = "";
$resultJSON = "";

# If the form was POSTed with a "key" to retrieve, try to get the data from the API.
if (isset($_POST['key'])) {
	$data = array(
		'action' => 'getPerson',
		'key' => $_POST['key'],
		'fields' => $_POST['fields'],
		'bioFormat' => $_POST['bioFormat'],
		'resolveRedirect' => $_POST['resolveRedirect']
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
		$resultHTML = renderResults($result);
		$resultJSON = "<pre>".json_encode(json_decode($result), JSON_PRETTY_PRINT)."</pre>";
	}
	# Close cURL session handle
	curl_close($curl);
  
}

# Convert our returned JSON into some HTML.
function renderResults($result) {
	$json = json_decode($result);
	$data = $json[0];
	if ($data->{'status'}) {
		# We had some sort of error. 
		return "WikiTree API Error: ".$data->{'status'};
	}
	else {
		# Put our profile information into some HTML to display.
		# The profile data we've retrieved is in the "profile" element.
		$html = 'WikiTree API Result<br>';
		$html .= 'Retrieved Profile for: data.page_name='.$data->{'page_name'}.'...<br>';
		if (isset($data->{'person'}->{'LongName'}) && isset($data->{'person'}->{'Name'})) {
			$html .= 'The derived "long" name, linked to the WikiTree profile page is: <a href="https://www.wikitree.com/wiki/'.$data->{'person'}->{'Name'}.'">'.$data->{'person'}->{'LongName'}.'</a><br>';
		}

		# Just use a simple table to display each field value.
		$html .= '<table><thead><tr><th>Field</th><th>Value</th></tr></thead><tbody>';
		foreach($data->{'person'} as $x => $value) {
			# DataStatus and PhotoData are themselves objects with a set of keys and values.
			if ($x == 'DataStatus' || $x == 'PhotoData') {
				$html .= '<tr><td>'.$x.'</td><td>';
				foreach($data->{'person'}->{$x} as $y => $yvalue) {
					$html .= $y . ' = ' . $yvalue .'<br>';
				}
				$html .= '</td></tr>';
			} 

			# If Children, Parents, Siblings, or Spouses are returned, these are arrays where the key
			# is the Id of the related profile and the value is another Profile.
			else if ($x == 'Children' || $x == 'Parents' || $x == 'Spouses' || $x == 'Siblings') {
				$html .= '<tr><td>'.$x.'</td><td>';
				foreach($data->{'person'}->{$x} as $id => $p) {
					$html .= $id . ': ' . $p->{'Name'} . '<br>';
				}
				$html .= '</td></tr>';
			}

			# Most profile fields are just profile[key] = value.
			else {
				$html .= '<tr><td>'.$x.'</td><td>'.$data->{'person'}->{$x}.'</td></tr>';
			}
		}
		$html .= '</tbody></table>';
		return $html;
	}
}

?>
<html>
<head><title>WikiTree API | getPerson</title></head>
<body>

<!-- Use a GitHub Markdown style -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/github-markdown-css/4.0.0/github-markdown.min.css" integrity="sha512-Oy18vBnbSJkXTndr2n6lDMO5NN31UljR8e/ICzVPrGpSud4Gkckb8yUpqhKuUNoE+o9gAb4O/rAxxw1ojyUVzg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
	.markdown-body {
		box-sizing: border-box;
		min-width: 200px;
		margin: 0 auto;
		padding: 45px;
	}

	@media (max-width: 767px) {
		.markdown-body {
			padding: 15px;
		}
	}
</style>

<article class="markdown-body">
	<h1> getPerson </h1>
	<form action="phpWebPage.php" method="POST">

	<p>
		We're doing a simple "getPerson" call here, with the results filled in below.
		The "key" for getPerson is a WikiTree ID (e.g., Shoshone-1) or a User ID (e.g. 27351134).
		We can also optionally specify a set of fields to return. If omitted, then  a default set is used.
		You can retrieve all fields with a value of "*".
		See (<a href="https://github.com/wikitree/wikitree-api/blob/main/getPerson.md">getPerson.md doc page</a>).
		<table>
		<tr><td>Key:</td><td><input type="text" id="key" name="key" value="Shoshone-1" size="20"></td></tr>
		<tr><td>Fields:</td><td><input type="text" id="fields" name="fields" value="Id,PageId,Name,Derived.LongName,BirthDate,DeathDate" size="80"></td></tr>
		<tr><td>bioFormat:</td>
			<td>
				<input type="radio" name="bioFormat" value="wiki"> wiki
				<input type="radio" name="bioFormat" value="html"> html
				<input type="radio" name="bioFormat" value="both"> both
				(only relevant if Fields includes "Bio")
			</td>
		</tr>
		<tr><td>resolveRedirect:</td>
			<td>
				<input type="checkbox" name="resolveRedirect" id="resolveRedirect" value="1"> Resolve/Follow redirections
				(e.g. try with Adams-24)
			</td>
		</tr>
		<tr><td colspan=3>
			<input type="submit" value="Get Profile">
		</td></tr>
		</table>
	</p>

	</form>

	<h2>Results</h2>
	<blockquote id="result"><?php print $resultHTML; ?></blockquote>

	<h2>JSON Results</h2>
	<blockquote id="json"><?php print $resultJSON; ?></blockquote>
</article>



</body>
</html>
