<?php

$resultHTML = "";
$resultJSON = "";

$WTCmode = "N";
if (isset($_POST['WTCmode'])) {
	$WTCmode = $_POST['WTCmode'];
} else if (isset($_GET['WTCmode'])) {
	$WTCmode = $_GET['WTCmode'];
}

# If the form was POSTed with a "key" to retrieve, try to get the data from the API.
if (isset($_POST['keyFrom'])) {
	// echo "POST time " . $_POST['key'];
		

	if (isset($_POST['doReverse'])) { 
		$data = array(
			'action' => 'getConnections',
			'keys' => $_POST['keyTo'] . "," . $_POST['keyFrom'],
			'appId' => 'testGetCs',
			'relation' => $_POST['relation'],
			'fields' => 'Id,Name,Derived.BirthNamePrivate,RealName,Father,Mother,Parents,Children,Siblings,DataStatus,Photo,Gender,BirthDate,DeathDate,BirthLocation,DeathLocation,BirthDateDecade,DeathDateDecade,IsLiving,'. $_POST['fields']
		);		
	} else {
		$data = array(
			'action' => 'getConnections',
			'keys' => $_POST['keyFrom'] . "," . $_POST['keyTo'],
			'appId' => 'testGetCs',
			'relation' => $_POST['relation'],
			'fields' => 'Id,Name,Derived.BirthNamePrivate,RealName,Father,Mother,Parents,Children,Siblings,DataStatus,Photo,Gender,BirthDate,DeathDate,BirthLocation,DeathLocation,BirthDateDecade,DeathDateDecade,IsLiving,'. $_POST['fields']
		);

	}
	  
	  // var_dump($data);
	# Prepare new cURL resource and POST our data
	// $curl = curl_init('https://staging:iX7fiophahxo@staging.wikitree.com/api.php');
	// $curl = curl_init('https://staging:ieV7waekahpa@staging.wikitree.com/api.php');
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
		echo "<BR>";
		if (stripos($result, "Unauthorized") > -1) {
			// echo "<H1>Danger ... Danger Will Robinson!</H1>"; // '

			$resultArray = [];
			
			$whichNum = random_int(0, count($resultArray));
			$result = $resultArray[$whichNum];

		}
		// echo $result;
		$resultHTML = renderResults($result);
		$pathHTML = drawPathOfResults($result);
		$theButtonsHTML = getQuickChangeButtons($result);
		$fromButtonsHTML = $theButtonsHTML[0];
		$toButtonsHTML = $theButtonsHTML[1];

		$resultJSON = "<pre>".json_encode(json_decode($result), JSON_PRETTY_PRINT)."</pre>";
	}
	# Close cURL session handle
	curl_close($curl);
  
}

function calculatedRelationship ( $peep ) {
	// return "related";
	

	$relationshipMatrix = array (
		"parent" => array("Male"=>"father" , "Female"=>"mother") ,
		"bioparent" => array("Male"=>"bio-father" , "Female"=>"bio-mother") ,
		"child" => array("Male"=>"son" , "Female"=>"daughter") ,
		"biochild" => array("Male"=>"bio-son" , "Female"=>"bio-daughter") ,
		"sibling" => array("Male"=>"brother" , "Female"=>"sister") ,
		"spouse" => array("Male"=>"husband" , "Female"=>"wife") 	);

	if ($peep['Gender'] && ($peep['Gender'] == "Male" || $peep['Gender'] == "Female")) {
		if ($peep['pathType'] && $peep['pathType'] > "" && $relationshipMatrix[ $peep['pathType'] ]) {
			if ($relationshipMatrix[ $peep['pathType'] ][ $peep['Gender'] ]) {
				return $relationshipMatrix[ $peep['pathType'] ][ $peep['Gender'] ];
			} else {
				return $peep['pathType'] ;
			}
		} else {
			return "connected ...";
		}
	} else {
		if ($peep['pathType'] && $peep['pathType'] > "" ) {
			return $peep['pathType'];
		} else {
			return "?";
		}
	}

}

function calculatedLifeSpan ( $peep ) {
	if ($peep['IsLiving'] && $peep['IsLiving'] == 1) {
		return "Living";
	} else if ($peep['Id'] < 0) {
		return "";
	} else {
	// return "BBBB - DDDD";
		$lifespan = "";
		if ($peep['BirthDate'] && $peep['BirthDate'] > "") {
			$lifespan = substr($peep['BirthDate'], 0, 4) ;
			if ($lifespan == "0000") {$lifespan = "?";}
		} 
		$lifespan .=  " - ";

		// return $lifespan;

		if ($peep['DeathDate'] && $peep['DeathDate'] > "") {
			if (substr($peep['DeathDate'], 0, 4) == "0000") {
				$lifespan .= "?";
			} else {
				$lifespan .= substr($peep['DeathDate'], 0, 4) ;
			}
		}
		return $lifespan;
	}

	return "???";
}

function polyLineArrow($dir,$xSVG, $ySVG) {
	$basicArrowUp = [ [0,4], [3,0], [1,0], [1,-4],[-1,-4], [-1,0], [-3,0], [0,4] ];
	$width = 3;

	$svg = "<polyline fill='orange' stroke='orange' points='";

	for ($i=0; $i < count($basicArrowUp); $i++) { 
		// code...
		if ($i > 0) {
			$svg .=",";
		}
		$pt = $basicArrowUp[$i];
		if ($dir == "up") {
			$pt[1] *= -1;
		} else if ($dir == "right") {
			$tmp = $pt[0];
			$pt[0] = $pt[1];
			$pt[1] = $tmp;
		}

		$svg .= ($xSVG + $pt[0] * $width) . "," . ($ySVG + $pt[1] * $width) ;
	}
	$svg .= "'></polyline>";

	return $svg;
}

function connectionImage($how, $xSVG, $ySVG) {
	$svg = "";
	$iconURL = "https://www.wikitree.com/images/icons/";
	$DNAiconTable = array(
		"30" => "icon-dna-checked.svg" , 
		"20" => "icon-confident.svg" , 
		"10" => "icon-uncertain.svg" , 
		"5" => "icon-dna-none.svg" );

	if ($DNAiconTable[$how]) {
		$iconURL .= $DNAiconTable[$how];
		$svg = '<image  height="16" href="'.$iconURL.'" x=' . ($xSVG + 5) . ' y="' . ($ySVG + 2) . '" />';
	}

	return $svg;
}

function interpretRelationship ($type, $level, $gender) {
	$base = $type;
	$prefix = "";
	$suffix = "";

	if ($type == "parent") {
		if ($gender == "Male") {
			$base = "father";
		} else if ($gender == "Female") {
			$base = "mother";
		// } else {
		// 	$base = $type;
		}
	}
	else if ($type == "child") {
		if ($gender == "Male") {
			$base = "son";
		} else if ($gender == "Female") {
			$base = "daughter";
		// } else {
		// 	$base = $type;
		}
	}
	else if ($type == "biochild") {
		if ($gender == "Male") {
			$base = "son";
		} else if ($gender == "Female") {
			$base = "daughter";
		// } else {
		// 	$base = $type;
		}
		// $prefix = "adopted ";
		$type = "child";
	}
	else if ($type == "bioparent") {
		if ($gender == "Male") {
			$base = "father";
		} else if ($gender == "Female") {
			$base = "mother";
		// } else {
		// 	$base = $type;
		}
		// $prefix = "adopted ";
		$type = "parent";
	}
	else if ($type == "sibling") {
		if ($gender == "Male") {
			$base = "brother";
		} else if ($gender == "Female") {
			$base = "sister";
		} else {
			$base = $type;
		}
	}
	else if ($type == "pibling") {
		if ($gender == "Male") {
			$base = "uncle";
		} else if ($gender == "Female") {
			$base = "aunt";
		} else {
			$base = $type;
		}
	}
	else if ($type == "nibling") {
		if ($gender == "Male") {
			$base = "nephew";
		} else if ($gender == "Female") {
			$base = "niece";
		} else {
			$base = $type;
		}
	}
	else if ($type == "spouse") {
		if ($gender == "Male") {
			$base = "husband";
		} else if ($gender == "Female") {
			$base = "wife";
		// } else {
		// 	$base = $type;
		}
	}
	else if ($type == "cousin" || $type == "Pcousin" || $type == "Ncousin") {
		$base = "cousin";
		$gen1 = floor($level);
		$gen2 = round(($level - $gen1) * 100);
		$prefix = $gen1;
		$suffix = $gen2;
		if ($gen1 == $gen2 + 1) {
			$prefix = "" . $gen1;
			if ($gen1 == 1) {
				$prefix .= "st ";
			} else if ($gen1 == 2) {
				$prefix .= "nd ";
			} else if ($gen1 == 3) {
				$prefix .= "rd ";
			} else {
				$prefix .= "th ";
			}
			$suffix = "";
		} else if ($gen1 <= $gen2 ) {
			$prefix = "" . $gen1;
			if ($gen1 == 1) {
				$prefix .= "st ";
			} else if ($gen1 == 2) {
				$prefix .= "nd ";
			} else if ($gen1 == 3) {
				$prefix .= "rd ";
			} else {
				$prefix .= "th ";
			}

			if ($gen1 == $gen2 ) {
				$suffix = " once removed";
			} else {
				$suffix = " " . ($gen2 + 1 - $gen1) . "x removed";
			}

		} else if ($gen1 > $gen2 + 1 ) {

			$prefix = "" . $gen2 + 1;
			if ($gen2 + 1 == 1) {
				$prefix .= "st ";
			} else if ($gen2 + 1 == 2) {
				$prefix .= "nd ";
			} else if ($gen2 + 1 == 3) {
				$prefix .= "rd ";
			} else {
				$prefix .= "th ";
			}

			if ($gen1 == $gen2 + 2) {
				$suffix = " once removed";
			} else {
				$suffix = " " . ($gen1 - 1 - $gen2) . "x removed";
			}

		}
	}

	if ($type == "parent" || $type == "child" || $type == "pibling" || $type == "nibling") {
		if ($type == "nibling") {$level++;}
		if ($level == 2) {
			$prefix = "grand";
		} else if ($level == 3) {
			$prefix = "great grand";
		} else if ($level > 3) {
			$prefix = ($level - 2) . "x great grand";
		}
	}

	return $prefix . $base . $suffix;
}

function getQuickChangeButtons($result) {

	global $WTCmode;

	$html = "";
	$SVGhtml = "";
	$pathFoundHTML = "";
	$json = json_decode($result, true);
	// var_dump($json);
	$jsonOBJ = $json[0];
	$pathsArray = $jsonOBJ["path"];	
	$pathsLength = $jsonOBJ["pathLength"];	
	$count = count($pathsArray);

	$FromToButtons = array("","");

	$firstPeep = $pathsArray[0];
	$lastPeep = $pathsArray[$count - 1];

	if ($firstPeep['Parents']) {
		foreach ($firstPeep['Parents'] as $key => $value) {
			// code...
			$FromToButtons[0] .= " " . '<button class="button parent" name=keyFrom value="' . $firstPeep['Parents'][$key]['Name'] . '">' . $firstPeep['Parents'][$key]['RealName'] . "</button>";
		}
		foreach ($firstPeep['Siblings'] as $key => $value) {
			// code...
			$FromToButtons[0] .= " " . '<button class="button sibling" name=keyFrom value="' . $firstPeep['Siblings'][$key]['Name'] . '">' . $firstPeep['Siblings'][$key]['RealName'] . "</button>";
		}
		foreach ($firstPeep['Children'] as $key => $value) {
			// code...
			$FromToButtons[0] .= " " . '<button class="button child" name=keyFrom value="' . $firstPeep['Children'][$key]['Name'] . '">' . $firstPeep['Children'][$key]['RealName'] . "</button>";
		}
	}
	if ($lastPeep['Parents']) {
		foreach ($lastPeep['Parents'] as $key => $value) {
			// code...
			$FromToButtons[1] .= " " . '<button class="button parent" name=keyTo value="' . $lastPeep['Parents'][$key]['Name'] . '">' . $lastPeep['Parents'][$key]['RealName'] . "</button>";
		}
		foreach ($lastPeep['Siblings'] as $key => $value) {
			// code...
			$FromToButtons[1] .= " " . '<button class="button sibling" name=keyTo value="' . $lastPeep['Siblings'][$key]['Name'] . '">' . $lastPeep['Siblings'][$key]['RealName'] . "</button>";
		}
		foreach ($lastPeep['Children'] as $key => $value) {
			// code...
			$FromToButtons[1] .= " " . '<button class="button child" name=keyTo value="' . $lastPeep['Children'][$key]['Name'] . '">' . $lastPeep['Children'][$key]['RealName'] . "</button>";
		}
	}
	return $FromToButtons;

}

# Convert our returned JSON into some HTML.
function drawPathOfResults($result) {
	global $WTCmode;

	$html = "";
	$SVGhtml = "";
	$pathFoundHTML = "";
	$json = json_decode($result, true);
	// var_dump($json);
	$jsonOBJ = $json[0];
	$pathsArray = $jsonOBJ["path"];	
	$pathsLength = $jsonOBJ["pathLength"];	
	$count = count($pathsArray);

	$xSVG = 10;
	$ySVG = 10;
	$bubbleWidth = 200;
	$bubbleHeight = 45;

	$maxX = 10;
	$minX = 10;
	$maxY = 10;
	$minY = 10;
	
	$halfPrefix = "½ ";
	$nonBioPrefix = "non-bio ";

	if ($pathsLength == "") {
		$html = "<B>No Connection found</B>";
	} else {
		$html = "<B>Path found : " . ($pathsLength - 1) . " steps</B><BR>";
	}

	$pathFoundHTML = $html;
	$relationshipDescriptor = "";
	$rawDescriptor = "";

	if ($count > 0) {
		$bkgdColourNum = 0;
		$bkgdColours = ["lightgreen", "lightyellow"];
		$prevRelationship = "";
		$prevPrevRelationship = "";
		$prevPrevPeep = null;

		$firstPeep = $pathsArray[0];
		$lastPeep = $pathsArray[$count - 1];
		$relationshipDescriptor = "<B>" . $lastPeep['RealName'] . "</B> is " . "<SPAN style='background-color:" .  $bkgdColours[ $bkgdColourNum % 2] . ";'>" . "<B>" . $firstPeep['RealName'] . "</B>'s </SPAN>";

		$currentRelType = "";
		$currentRelLevel = 0.0;
		$currentRelDesc = "";

		$nextRelType = "";
		$lastI = 0;
		$isCurrentHalf = "";
		$halfPrefix = "½ ";

		for ($i = 0; $i < $count; $i++) {
		// 	// code...
			$thisPeep = $pathsArray[$i];
			if ($i > 0) {
					if ($currentRelType == "") {
						$currentRelType = $thisPeep["pathType"];//.replace("bio","");
						if ($i == 1 && ($currentRelType == "parent" || $currentRelType == "bioparent" || $currentRelType == "child" || $currentRelType == "biochild")) {$currentRelLevel = 1;} else {$currentRelLevel = 0;}	
						$isCurrentHalf = "";
						$rawDescriptor .= "<BR>(" . $currentRelType . ") ";
						$currentRelType = str_replace("bio","",$currentRelType);
						// if ($currentRelType == "bioparent") {
						// 	$currentRelType = "parent";
						// }
					}
				if ($i > 1) {
					$prevPrevPeep = $prevPeep;


					
						if ($currentRelType == "parent" && ($thisPeep["pathType"] == "parent" || $thisPeep["pathType"] == "bioparent")) {
							$currentRelLevel += 1;
						} else if ($currentRelType == "child" && ($thisPeep["pathType"] == "child" || $thisPeep["pathType"] == "biochild")) {
							$currentRelLevel += 1;
						} else if ($currentRelType == "nibling" && ($thisPeep["pathType"] == "child" || $thisPeep["pathType"] == "biochild")) {
							$currentRelLevel += 1;
						} else if ($currentRelType == "parent" && $thisPeep["pathType"] == "sibling") {
							$currentRelType = "pibling";
						} else if ($currentRelType == "child" && $thisPeep["pathType"] == "sibling") {
							$currentRelType = "nibling";
						} else if ($currentRelType == "sibling" && $thisPeep["pathType"] == "child") {
							$currentRelType = "nibling";
						} else if ($currentRelType == "pibling" && ($thisPeep["pathType"] == "child" || $thisPeep["pathType"] == "biochild")) {
							$currentRelType = "Pcousin";
						} else if ($currentRelType == "nibling" && ($thisPeep["pathType"] == "parent" || $thisPeep["pathType"] == "bioparent")) {
							$currentRelType = "Ncousin";
						} else if ($currentRelType == "Pcousin" && ($thisPeep["pathType"] == "child" || $thisPeep["pathType"] == "biochild")) {
							$currentRelLevel += 0.01;
						} else if ($currentRelType == "Ncousin" && ($thisPeep["pathType"] == "parent" || $thisPeep["pathType"] == "bioparent")) {
							$currentRelLevel += 0.01;
						}
					
				}

				$prevPeep = $pathsArray[$i - 1];
				$nextPeep = $pathsArray[min($i + 1, $count - 1)];
				$html .= " --" .  $thisPeep["pathType"]. "-> " ;

				$thisRelationship = calculatedRelationship($thisPeep);
				// $relationshipDescriptor .= "|" . $thisRelationship;
				$rawDescriptor .= "|" . $thisPeep["pathType"] . "~" . $currentRelLevel;
				if ($thisPeep["pathType"] == "sibling" && ($thisPeep['Father'] != $prevPeep['Father'] || $thisPeep['Mother'] != $prevPeep['Mother'])) {
					$thisRelationship = $halfPrefix . $thisRelationship;
					$isCurrentHalf = $halfPrefix;
				}

				if ( $thisPeep['Id'] < 0) {
					$thisPeep["BirthNamePrivate"] = 'Private';
				}

				if ($thisRelationship == "?"/* || $thisPeep['Id'] < 0*/) {
					$SVGhtml .= '<text  text-anchor="middle" x="' .   ($xSVG + 40 + $bubbleWidth + 10 + 20 - (60 ) / 2) . '" y="' . ($ySVG + 35) . '" ' . ' >' . $thisRelationship  .   '</text>';
					$xSVG += $bubbleWidth + 10 + 70;
					$SVGhtml .= polyLineArrow("right",$xSVG - 40, $ySVG + 10);
					// $thisPeep["BirthNamePrivate"] = 'Private';
					// $SVGhtml .= connectionImage($thisPeep["pathStatus"],$xSVG - 65, $ySVG + 40);

				} else if ($thisPeep["pathType"] == "parent" || $thisPeep["pathType"] == "bioparent") {
					$arrowDir = "up";
					if ($nextPeep["pathType"] == "child" || $prevPeep["pathType"] == "child" || $nextPeep["pathType"] == "biochild" || $prevPeep["pathType"] == "biochild") {
						$xSVG += $bubbleWidth/2 + 60;
						// $arrowDir = "right";
					}
					$SVGhtml .= '<text  text-anchor="middle" x="' .   ($xSVG + 40 + 10 + ($bubbleWidth - 60) / 2) . '" y="' . ($ySVG - 15) . '" ' . ' >' . $thisRelationship  .   '</text>';
					$SVGhtml .= polyLineArrow($arrowDir,$xSVG + 20, $ySVG - 15);
					$SVGhtml .= connectionImage($thisPeep["pathStatus"],$xSVG + 20 + 10, $ySVG - 30);
					$ySVG -= $bubbleHeight + 10 + 20;
				} else if ($thisPeep["pathType"] == "child" || $thisPeep["pathType"] == "biochild") {
					$arrowDir = "down";
					if ($nextPeep["pathType"] == "parent" || $prevPeep["pathType"] == "parent" || $nextPeep["pathType"] == "bioparent" || $prevPeep["pathType"] == "bioparent") {
						$xSVG += $bubbleWidth/2 + 60;
						// $arrowDir = "right";
					}
					 $SVGhtml .= '<text  text-anchor="middle" x="' .   ($xSVG + 40 + 10 + ($bubbleWidth - 60) / 2) . '" y="' . ($ySVG + 70) . '" ' . ' >' . $thisRelationship  .   '</text>';
					$ySVG += $bubbleHeight + 10 + 20;
					$SVGhtml .= polyLineArrow($arrowDir,$xSVG + 20, $ySVG - 15);
					$SVGhtml .= connectionImage($thisPeep["pathStatus"],$xSVG + 20 + 10, $ySVG - 20);
				} else if ($thisPeep["pathType"] == "sibling" || $thisPeep["pathType"] == "spouse") {
					$SVGhtml .= '<text  text-anchor="middle" x="' .   ($xSVG + 40 + $bubbleWidth + 10 + 20 - (60 ) / 2) . '" y="' . ($ySVG + 35) . '" ' . ' >' . $thisRelationship  .   '</text>';
					$xSVG += $bubbleWidth + 10 + 70;
					$SVGhtml .= polyLineArrow("right",$xSVG - 40, $ySVG + 10);
					$SVGhtml .= connectionImage($thisPeep["pathStatus"],$xSVG - 65, $ySVG + 40);
				} else {
					$ySVG += $bubbleHeight + 10 + 20;
				}

			}

			$threeInRow = "";
			$twoInRow = "";
			if ($i > 1) {
				$threeInRow = $prevPrevPeep["pathType"] . $prevPeep["pathType"] . $thisPeep["pathType"];
			}
			if ($i > 0) {
				$twoInRow =  $prevPeep["pathType"] . $thisPeep["pathType"];
			}

			$wasPrevPrevHalfSib = ( stripos ($prevPrevRelationship, "½") === 0);
			$wasPrevHalfSib = ( stripos ($prevRelationship, "½") === 0);
			$isCurrHalfSib = ( stripos ($thisRelationship, "½") === 0);

			// check for cases where bkgd colours should change because relationship is non biological or unknown 
			// case 1: spouse
			// case 2: private (unknown whether bio or not)
			// case 3: child parent / parent child (alternate way of getting to a spouse, or spouse-like person who is not married to the other)
			// case 4: parent of non bio child / child of non bio parent
			// case 5a: parent halfSib nonChild
			// case 5b: spouse halfSib nonChild
			// case 5c: fullSib halfSib nonChild
			// case 6a: halfSib halfSib
			// case 6b: child halfSib
			// case 7: sib parent (not in common) OR child sib (not common parent)

			// CASES 1 / 2 / 3
				// case 1: spouse
				// case 2: private (unknown whether bio or not)
				// case 3:  child parent /  parent child
			if ($thisPeep["pathType"] == "spouse" || $thisRelationship == "?" || $prevRelationship == "?"|| $twoInRow == "childparent" || $twoInRow == "parentchild") {
				if ($i > $lastI + 1 && $nextRelType > "") {
					$insertPrevType = $nextRelType . "'s " ;// . $i . " $lastI ";
				} else {
					$insertPrevType = "";// " A $i ";
					 
				}
				if ($i > 1){
					$relationshipDescriptor .= "<SPAN style='background-color:" .  $bkgdColours[ $bkgdColourNum % 2] . ";'>" .  $insertPrevType . $isCurrentHalf /*. " A "*/ . interpretRelationship($currentRelType , $currentRelLevel, $prevPeep['Gender']) . "</SPAN>'s ";
				} else {
					$relationshipDescriptor .= "&nbsp;";
				}
				$bkgdColourNum++;
				$rawDescriptor .= " [" . $prevPrevPeep['RealName'] .  "." . $currentRelType . "." .  $currentRelLevel . "." .  $prevPeep['RealName'] . "." . $thisPeep['pathType'] . "." . $thisPeep['RealName'] . "] "  ; //. "  // " . $currentRelType . "/" . $currentRelLevel ;
				$currentRelType = "";//$thisPeep["pathType"] ;
				$currentRelLevel = 0;
				$maybeHalf = "";
				if ($thisPeep["pathType"] == "sibling") {
						if ($prevPeep['Father'] && $thisPeep['Father'] && $prevPeep['Father'] != $thisPeep['Father']) {
							$maybeHalf =  $halfPrefix;
						} else if ($prevPeep['Mother'] && $thisPeep['Mother'] && $prevPeep['Mother'] != $thisPeep['Mother']) {
							$maybeHalf =  $halfPrefix;
						}
					}
				$nextRelType =  /*" Z " . */ $maybeHalf . interpretRelationship($thisPeep["pathType"] , 0, $thisPeep['Gender']);
				$isCurrentHalf = "";
				$lastI = $i;

			// CASE 4
				// case 4: parent of non bio child / child of non bio parent
			} else if ( 
				($thisRelationship == "father" && $prevPeep["DataStatus"]['Father'] && $prevPeep["DataStatus"]['Father'] == 5) 
				||  
				($thisRelationship == "mother" && $prevPeep["DataStatus"]['Mother'] && $prevPeep["DataStatus"]['Mother'] == 5) 
				||  
				(($thisPeep["pathType"] == "child" /*|| $thisPeep["pathType"] == "biochild"*/) && $thisPeep["DataStatus"]['Father'] && $thisPeep["DataStatus"]['Father'] == 5 && $prevPeep['Gender'] && $prevPeep['Gender'] == "Male") 
				||  
				(($thisPeep["pathType"] == "child" /*|| $thisPeep["pathType"] == "biochild"*/) && $thisPeep["DataStatus"]['Mother'] && $thisPeep["DataStatus"]['Mother'] == 5 && $prevPeep['Gender'] && $prevPeep['Gender'] == "Female") 
			) {

					if ($i > $lastI + 1 && $nextRelType > "") {
						$insertPrevType = $nextRelType . "'s " ;// . $i . " $lastI ";
					} else {
						$insertPrevType = "";
					}

					if ($thisPeep["pathType"] == "child" && $currentRelType == "child" && $currentRelLevel > 1 ) {
						$currentRelLevel -= 1;
					}

					if ($i > 1){
					$relationshipDescriptor .= "<SPAN style='background-color:" .  $bkgdColours[ $bkgdColourNum % 2] . ";'>" .  $insertPrevType . $isCurrentHalf /*.  " B " */. interpretRelationship($currentRelType , $currentRelLevel, $prevPeep['Gender']) . "</SPAN>'s ";
					} else {
						$relationshipDescriptor .= "&nbsp;";
					}
					$bkgdColourNum++;
				
					$rawDescriptor .= " [" . $prevPrevPeep['RealName'] .  "." . $currentRelType . "." .  $currentRelLevel . "." .  $prevPeep['RealName'] . "." . $thisPeep['pathType'] . "." . $thisPeep['RealName'] . "] "  ; //. "  // " . $currentRelType . "/" . $currentRelLevel ;
					$currentRelType = "";
					$currentRelLevel = 0;
					$maybeHalf = "";
					if ($thisPeep["pathType"] == "sibling") {
						if ($prevPeep['Father'] && $thisPeep['Father'] && $prevPeep['Father'] != $thisPeep['Father']) {
							$maybeHalf =  $halfPrefix;
						} else if ($prevPeep['Mother'] && $thisPeep['Mother'] && $prevPeep['Mother'] != $thisPeep['Mother']) {
							$maybeHalf =  $halfPrefix;
						}
					}
					$nextRelType =  /* " Y "  $thisPeep["pathType"] . .*/ " adopted " . interpretRelationship($thisPeep["pathType"] , 0, $thisPeep['Gender']);
					$isCurrentHalf = "";
					$lastI = $i;

			// CASES 5 a, b, c			
				// case 5a: parent halfSib nonChild
				// case 5b: spouse halfSib nonChild
				// case 5c: fullSib halfSib nonChild	
			} else if (
				($prevPrevPeep["pathType"] == "parent" || $prevPrevPeep["pathType"] == "spouse" || $prevPrevRelationship == "brother" || $prevPrevRelationship == "sister" ) 
				&& $wasPrevHalfSib == true 
				&& $thisPeep["pathType"] != "child" ) {

					if ($i > $lastI + 1 && $nextRelType > "") {
						$insertPrevType = $nextRelType . "'s " ;// . $i . " $lastI ";
					} else {
						$insertPrevType = "";
					}
					if ($i > 1){
					$relationshipDescriptor .= "<SPAN style='background-color:" .  $bkgdColours[ $bkgdColourNum % 2] . ";'>" .  $insertPrevType . $isCurrentHalf /*.  " C " */.  interpretRelationship($currentRelType , $currentRelLevel, $prevPeep['Gender']) . "</SPAN>'s ";
					} else {
						$relationshipDescriptor .= "&nbsp;";
					}
					$bkgdColourNum++;
				
					$rawDescriptor .= " [" . $prevPrevPeep['RealName'] .  "." . $currentRelType . "." .  $currentRelLevel . "." .  $prevPeep['RealName'] . "." . $thisPeep['pathType'] . "." . $thisPeep['RealName'] . "] "  ; //. "  // " . $currentRelType . "/" . $currentRelLevel ;
					$currentRelType = "";
					$currentRelLevel = 0;
					$maybeHalf = "";
					if ($thisPeep["pathType"] == "sibling") {
						if ($prevPeep['Father'] && $thisPeep['Father'] && $prevPeep['Father'] != $thisPeep['Father']) {
							$maybeHalf =  $halfPrefix;
						} else if ($prevPeep['Mother'] && $thisPeep['Mother'] && $prevPeep['Mother'] != $thisPeep['Mother']) {
							$maybeHalf =  $halfPrefix;
						}
					}

					$nextRelType =  /*" X " . */ $maybeHalf . interpretRelationship($thisPeep["pathType"] , 0, $thisPeep['Gender']);
					$isCurrentHalf = "";
					$lastI = $i;

			// CASES 6a , 6b
				// case 6a: halfSib halfSib
				// case 6b: child halfSib
			} else if ( ($wasPrevHalfSib == true || $prevPeep["pathType"] == "child") && $isCurrHalfSib == true ) {
					if ($i > $lastI + 1 && $nextRelType > "") {
						$insertPrevType = $nextRelType . "'s " ;// . $i . " $lastI ";
					} else {
						$insertPrevType = "";
					}
					if ($i > 1){
					$relationshipDescriptor .= "<SPAN style='background-color:" .  $bkgdColours[ $bkgdColourNum % 2] . ";'>" .  $insertPrevType . $isCurrentHalf /*. " D "*/ . interpretRelationship($currentRelType , $currentRelLevel, $prevPeep['Gender']) . "</SPAN>'s ";
					} else {
						$relationshipDescriptor .= "&nbsp;";
					}
					$bkgdColourNum++;
				
					$rawDescriptor .= " [" . $prevPrevPeep['RealName'] .  "." . $currentRelType . "." .  $currentRelLevel . "." .  $prevPeep['RealName'] . "." . $thisPeep['pathType'] . "." . $thisPeep['RealName'] . "] "  ; //. "  // " . $currentRelType . "/" . $currentRelLevel ;
					$currentRelType = "";
					$currentRelLevel = 0;
					$maybeHalf = "";
					if ($thisPeep["pathType"] == "sibling") {
						if ($prevPeep['Father'] && $thisPeep['Father'] && $prevPeep['Father'] != $thisPeep['Father']) {
							$maybeHalf =  $halfPrefix;
						} else if ($prevPeep['Mother'] && $thisPeep['Mother'] && $prevPeep['Mother'] != $thisPeep['Mother']) {
							$maybeHalf =  $halfPrefix;
						}
					}
					$nextRelType =  /*" W " .*/  $maybeHalf . interpretRelationship($thisPeep["pathType"] , 0, $thisPeep['Gender']);
					$isCurrentHalf = "";
					$lastI = $i;

			// CASE 7: sib parent (not in common with first sib) OR child sib (not common parent)

			} else if ( $twoInRow == "siblingparent" || $twoInRow == "siblingbioparent"  || $twoInRow == "childsibling"  || $twoInRow == "biochildsibling"  ) {
					if ($i > $lastI + 1 && $nextRelType > "") {
						$insertPrevType = $nextRelType . "'s " ;// . $i . " $lastI ";
					} else {
						$insertPrevType = "";
					}
					if ($i > 1){
					$relationshipDescriptor .= "<SPAN style='background-color:" .  $bkgdColours[ $bkgdColourNum % 2] . ";'>" .  $insertPrevType . $isCurrentHalf /*. " D "*/ . interpretRelationship($currentRelType , $currentRelLevel, $prevPeep['Gender']) . "</SPAN>'s ";
					} else {
						$relationshipDescriptor .= "&nbsp;";
					}
					$bkgdColourNum++;
				
					$rawDescriptor .= " [" . $prevPrevPeep['RealName'] .  "." . $currentRelType . "." .  $currentRelLevel . "." .  $prevPeep['RealName'] . "." . $thisPeep['pathType'] . "." . $thisPeep['RealName'] . "] "  ; //. "  // " . $currentRelType . "/" . $currentRelLevel ;
					$currentRelType = "";
					$currentRelLevel = 0;
					$maybeHalf = "";
					if ($thisPeep["pathType"] == "sibling") {
						if ($prevPeep['Father'] && $thisPeep['Father'] && $prevPeep['Father'] != $thisPeep['Father']) {
							$maybeHalf =  $halfPrefix;
						} else if ($prevPeep['Mother'] && $thisPeep['Mother'] && $prevPeep['Mother'] != $thisPeep['Mother']) {
							$maybeHalf =  $halfPrefix;
						}
					}
					$nextRelType =  /*" W " .*/  $maybeHalf . interpretRelationship($thisPeep["pathType"] , 0, $thisPeep['Gender']);
					$isCurrentHalf = "";
					$lastI = $i;
			} else {
				$rawDescriptor .= " { " . $i . " : $thisRelationship } ";
			}


			$html .= $thisPeep["BirthNamePrivate"] ;

			$borderColour = "black";
			$bkgdColour = $bkgdColours[ $bkgdColourNum % 2];

			if ($thisPeep["Gender"] && $thisPeep["Gender"] == "Male"){
				$borderColour = "blue";
				$photoUrl = "images/icons/male.gif";
			} else if ($thisPeep["Gender"] && $thisPeep["Gender"] == "Female"){
				$borderColour = "red";
				$photoUrl = "images/icons/female.gif";
			} else {
				$borderColour = "green";
				$photoUrl = "images/icons/no-gender.gif";
			} 

			if ($thisPeep["PhotoData"] && $thisPeep["PhotoData"]["path"] && $thisPeep["PhotoData"]["path"] > ""){
				$photoUrl = $thisPeep["PhotoData"]["path"];
			}


			$SVGhtml .= '<rect x="' . $xSVG . '" y="' . $ySVG . '" rx="10" ry="10" width="' . $bubbleWidth .'" height="' . $bubbleHeight .'" style="fill:' . $bkgdColour . ';stroke:' . $borderColour . ';stroke-width:2;opacity:1"></rect>';

			$photoHTML = '<image  height="40" href="https://www.wikitree.com/' . $photoUrl . ' " x=' . ($xSVG + 5) . ' y="' . ($ySVG + 2) . '" />'; 

			if ($thisPeep['Name'] == 'Jackson-44416') {
				$photoHTML = '<image  height="40" href="https://www.wikitree.com/photo.php/7/71/Jackson-44416-1.png" x=' . ($xSVG + 5) . ' y="' . ($ySVG + 2) . '" />';
	        }
			
	        $SVGhtml .= $photoHTML;

            $extraLengthStuff = "";
            if (strlen($thisPeep["BirthNamePrivate"]) > 20) {
            	$extraLengthStuff = ' textLength="' . ($bubbleWidth - 60) . '" lengthAdjust="spacingAndGlyphs"';
            }
            $SVGhtml .= '<A target=_blank href=https://www.wikitree.com/wiki/' . $thisPeep['Id'] . '>' . '<text  text-anchor="middle" x="' .   ($xSVG + 40 + 10 + ($bubbleWidth - 60) / 2) . '" y="' . ($ySVG + 18) . '" ' . $extraLengthStuff . ' >' . $thisPeep["BirthNamePrivate"]  .   '</text></A>';

            $SVGhtml .= '<text style="font-size:14px;" text-anchor="middle" x="' .   ($xSVG + 40 + 10 + ($bubbleWidth - 60) / 2) . '" y="' . ($ySVG + 35) . '" ' . ' >' . calculatedLifeSpan($thisPeep)  .   '</text>';




            $minX = min($minX, $xSVG);
            $maxX = max($maxX, $xSVG);
            $minY = min($minY, $ySVG);
            $maxY = max($maxY, $ySVG);

            $prevPrevRelationship = $prevRelationship;
            $prevRelationship = $thisRelationship;
		}

		$minX -= 10;
		$minY -= 10;
		$svgWidth = $maxX + $bubbleWidth - $minX + 10;
		$svgHeight = $maxY + $bubbleHeight - $minY + 10;

		$viewBox = 'viewBox="' . $minX . ',' . $minY . ',' . $svgWidth . ',' . $svgHeight . '"';

		if ($WTCmode == "Y") {
			$birthInfo1 = "";
			if ($firstPeep['BirthDate'] && $firstPeep['BirthDate'] >'') {
				$birthInfo1 = $firstPeep['BirthDate'];
			} else if ($firstPeep['BirthDateDecade'] && $firstPeep['BirthDateDecade'] >'') {
				$birthInfo1 = $firstPeep['BirthDateDecade'];
			}
			if ($firstPeep['BirthLocation'] && $firstPeep['BirthLocation'] >'') {
				if ($birthInfo1 > "") { $birthInfo1 .= ", ";}
				$birthInfo1 .= $firstPeep['BirthLocation'];
			}
			if ($birthInfo1 > "") {
				$birthInfo1 = "b. " . $birthInfo1;
			}

			$deathInfo1 = "";
			if ($firstPeep['DeathDate'] && $firstPeep['DeathDate'] >'') {
				$deathInfo1 = $firstPeep['DeathDate'];
			} else if ($firstPeep['DeathDateDecade'] && $firstPeep['DeathDateDecade'] >'') {
				$deathInfo1 = $firstPeep['DeathDateDecade'];
			}
			if ($firstPeep['DeathLocation'] && $firstPeep['DeathLocation'] >'') {
				if ($deathInfo1 > "") { $deathInfo1 .= ", ";}
				$deathInfo1 .= $firstPeep['DeathLocation'];
			}
			if ($firstPeep['IsLiving']) {
				$deathInfo1 = "";
			} else if ($deathInfo1 > "") {
				$deathInfo1 = "d. " . $deathInfo1 ;
			}

			$birthInfo2 = "";
			if ($thisPeep['BirthDate'] && $thisPeep['BirthDate'] >'') {
				$birthInfo2 = $thisPeep['BirthDate'];
			} else if ($thisPeep['BirthDateDecade'] && $thisPeep['BirthDateDecade'] >'') {
				$birthInfo2 = $thisPeep['BirthDateDecade'];
			}
			if ($thisPeep['BirthLocation'] && $thisPeep['BirthLocation'] >'') {
				if ($birthInfo2 > "") { $birthInfo2 .= ", ";}
				$birthInfo2 .= $thisPeep['BirthLocation'];
			}
			if ($birthInfo2 > "") {
				$birthInfo2 = "b. " . $birthInfo2;
			}

			$deathInfo2 = "";
			if ($thisPeep['DeathDate'] && $thisPeep['DeathDate'] >'') {
				$deathInfo2 = $thisPeep['DeathDate'];
			} else if ($thisPeep['DeathDateDecade'] && $thisPeep['DeathDateDecade'] >'') {
				$deathInfo2 = $thisPeep['DeathDateDecade'];
			}
			if ($thisPeep['DeathLocation'] && $thisPeep['DeathLocation'] >'') {
				if ($deathInfo2 > "") { $deathInfo2 .= ", ";}
				$deathInfo2 .= $thisPeep['DeathLocation'];
			}
			if ($thisPeep['IsLiving']) {
				$deathInfo2 = "";
			} else if ($deathInfo2 > "") {
				$deathInfo2 = "d. " . $deathInfo2 ;
			}

			$html =  "<H3><font color=orange>WikiTree Challenge</font></H3><BR><font color=black><U>Connecting</U></font><BR/><font color=darkgreen><B>" . $firstPeep["BirthNamePrivate"] ."</B><BR>" .
			 $birthInfo1  . "<BR>" . $deathInfo1  . "<BR>WikiTree ID: " . $firstPeep["Name"]. "</B></font>" .  
			 "<BR><span style='background-color:lightgray; color:white;'>&nbsp;to&nbsp;</span><BR>".
			 "<font color=blue><B>". $thisPeep["BirthNamePrivate"]  ."</B><BR>" .
			 $birthInfo2  . "<BR>" . $deathInfo2  . "<BR>WikiTree ID: " . $thisPeep["Name"] . "</font><BR><BR>" .  $pathFoundHTML;
		}
		$html .=  "<BR><SVG height=" . ($svgHeight) . " width=" . ($svgWidth) . " " . $viewBox . ">" . $SVGhtml . "</SVG>";
	
		if ($bkgdColourNum > 0) {
			$html .= "<BR>" .  $firstPeep['RealName'] . " is connected to " . $lastPeep['RealName'] . " through ".($bkgdColourNum + 1)." different families.";
		} else {
			// $html .= "<BR>A is related to B.";
		}

		if ($i > $lastI + 1 && $nextRelType > "") {
			$insertPrevType = $nextRelType . "'s " ;// . $i . " $lastI ";
		} else {
			$insertPrevType = "";
		}

		$finalRelType =  /*" Q " . */interpretRelationship($currentRelType , $currentRelLevel, $thisPeep['Gender']);
		if ($currentRelType == "") { $finalRelType = $nextRelType;}
		$html .= "<BR>" . $relationshipDescriptor  .  
			"<SPAN style='background-color:" .  $bkgdColours[ $bkgdColourNum % 2] . ";'>" . $insertPrevType . $isCurrentHalf .  $finalRelType . "</SPAN>!" .
		// interpretRelationship($currentRelType , $currentRelLevel, $thisPeep['Gender']) 
		"<HR>"  . $rawDescriptor . " <BR> // " . $currentRelType . "/" . $currentRelLevel . "  :  " . $i . "/" . $lastI . "/" . $nextRelType;
	} else {
		// $html = "Path between userid1 and userid2 goes here";
	}


	
	return $html ;
}


# Convert our returned JSON into some HTML.
function renderResults($result) {
	$json = json_decode($result);
	$data = $json[0];
	$pathsLength = $data->{'pathLength'};
	if ($data->{'status'}) {
		# We had some sort of error. 
		return "WikiTree API Error: ".$data->{'status'};
	}
	else {
		# Put our profile information into some HTML to display.
		# The profile data we've retrieved is in the "profile" element.
		$html = 'WikiTree API Result<br>';
		$html .= 'Retrieved Connections between: '. $_POST['keyFrom'] .' & ' . $_POST['keyTo'] .'<br>';
		if ($pathsLength > 1) {
			$html .= 'from ' . $data->{'path'}[0]->{'BirthNamePrivate'} . " to " .   $data->{'path'}[(($pathsLength - 1))]->{'BirthNamePrivate'}  . '<br>';
		}

		
		# Just use a simple table to display each field value.
		$html .= '<table><thead><tr><th>Field</th><th>Value</th></tr></thead><tbody>';
		foreach($data as $x => $value) {
			if ($x != "path"){
				$html .= '<tr><td>'.$x.'</td><td>'.$value.'</td></tr>';
			}
		}
		// 	# DataStatus and PhotoData are themselves objects with a set of keys and values.
		// 	if ($x == 'DataStatus' || $x == 'PhotoData') {
		// 		$html .= '<tr><td>'.$x.'</td><td>';
		// 		foreach($data->{'profile'}->{$x} as $y => $yvalue) {
		// 			$html .= $y . ' = ' . $yvalue .'<br>';
		// 		}
		// 		$html .= '</td></tr>';
		// 	} 

		// 	# If Children, Parents, Siblings, or Spouses are returned, these are arrays where the key
		// 	# is the Id of the related profile and the value is another Profile.
		// 	else if ($x == 'Children' || $x == 'Parents' || $x == 'Spouses' || $x == 'Siblings') {
		// 		$html .= '<tr><td>'.$x.'</td><td>';
		// 		foreach($data->{'profile'}->{$x} as $id => $p) {
		// 			$html .= $id . ': ' . $p->{'Name'} . '<br>';
		// 		}
		// 		$html .= '</td></tr>';
		// 	}

		// 	# Most profile fields are just profile[key] = value.
		// 	else {
		// 		$html .= '<tr><td>'.$x.'</td><td>'.$data->{'profile'}->{$x}.'</td></tr>';
		// 	}
		// }
		$html .= '</tbody></table>';
		return $html;
	}
}

?>
<html>
<head><title>WikiTree API | getConnections</title></head>
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

	.button {
	    border-radius: 10px;
	    color: black;
	    background-color: orange;
	    font-size: medium;	 
	    cursor: pointer;   
	}

	.parent {
		color:blue;
		background-color: darkgray;
	}
	.sibling {
		color:pink;
		background-color: darkblue;
	}
	.child {
		color:yellow;
		background-color: darkgreen;
	}

	.button:hover {	    
	    color: white;	    
	    /*background-color: #d68b01;*/
	    /*font-size: large;	    */
	}

	@media (max-width: 767px) {
		.markdown-body {
			padding: 15px;
		}
	}
</style>

<article class="markdown-body">
	<h1> getConnections Tester</h1>
	<form action="getConnectionsTester.php" method="POST">

	<p>
		<!-- <?php echo (($_POST['doReverse'])). " " . (isset($_POST['doReverse'])) . "<BR>"; ?> -->
		We're doing a simple "getConnections" call here, with the results filled in below.
		The "keys" for getConnections are a pair of WikiTree IDs (e.g., Windsor-1) or a page id (e.g. 30030890).
		<BR>We can also optionally specify a set of fields to return. If omitted, then  a default set is used.
		You can retrieve all fields with a value of "*".
		<BR>See (<a target=_blank href="https://github.com/wikitree/wikitree-api/blob/main/getConnections.md">getConnections.md doc page</a>).
		<table>
		<tr><td>Keys: From WikiTree ID:</td><td><input type="text" id="keyFrom" name="keyFrom" value="<?php 
			if (isset($_POST['keyFrom'])) { 
				if (isset($_POST['doReverse'])) { 
					echo $_POST['keyTo']; } 
				else { 
					echo $_POST['keyFrom']; 
				} 
			}else { 
				echo "Windsor-1";
			} ?>" size="20"> <span id=FromFamilyBtns><?php print $fromButtonsHTML; ?></span></td></tr>
		<tr><td>Keys: To WikiTree ID:</td><td><input type="text" id="keyTo" name="keyTo" value="<?php 
			if (isset($_POST['keyTo'])) { 
				if (isset($_POST['doReverse'])) { 
					echo $_POST['keyFrom']; } 
				else { 
					echo $_POST['keyTo']; 
				} 
			} else { 
				echo "Prevost-1162";
			} ?>" size="20"> <span id=ToFamilyBtns><?php print $toButtonsHTML; ?></span></td></tr>
		<tr><td>Fields:</td><td><input type="text" id="fields" name="fields" value="<?php if (isset($_POST['fields'])) { echo $_POST['fields']; } else { echo "Id,Name,Derived.LongName,BirthDate,DeathDate,Gender";} ?>" size="80"></td></tr>		
		<tr><td>Relation:</td>
			<td>	
				<select id=relation name=relation> 
					<option>Choose type of relationship connection</option>
					<option value=0 <?php if (isset($_POST['relation']) && $_POST['relation'] == 0) {echo "selected";} ?> >0 = Shortest Path</option>
					<option value=1 <?php if (isset($_POST['relation']) && $_POST['relation'] == 1) {echo "selected";} ?> >1 = Shortest Path excluding Spouses</option>
					<option value=2 <?php if (isset($_POST['relation']) && $_POST['relation'] == 2) {echo "selected";} ?> >2 = Shortest Path through a Common Ancestor</option>
					<option value=3 <?php if (isset($_POST['relation']) && $_POST['relation'] == 3) {echo "selected";} ?> >3 = Shortest Path through a Common Descendant</option>
					<option value=4 <?php if (isset($_POST['relation']) && $_POST['relation'] == 4) {echo "selected";} ?> >4 = Shortest Path through Fathers Only</option>
					<option value=5 <?php if (isset($_POST['relation']) && $_POST['relation'] == 5) {echo "selected";} ?> >5 = Shortest Path through Mothers Only</option>
					<option value=6 <?php if (isset($_POST['relation']) && $_POST['relation'] == 6) {echo "selected";} ?> >6 = Shortest Path through yDNA</option>
					<option value=7 <?php if (isset($_POST['relation']) && $_POST['relation'] == 7) {echo "selected";} ?> >7 = Shortest Path through mtDNA</option>
					<option value=8 <?php if (isset($_POST['relation']) && $_POST['relation'] == 8) {echo "selected";} ?> >8 = Shortest Path through auDNA</option>
					<option value=9 <?php if (isset($_POST['relation']) && $_POST['relation'] == 9) {echo "selected";} ?> >9 = TBD </option>
					<option value=10 <?php if (isset($_POST['relation']) && $_POST['relation'] == 10) {echo "selected";} ?> >10 = TBD </option>
					<option value=11 <?php if (isset($_POST['relation']) && $_POST['relation'] == 11) {echo "selected";} ?> >11 = Shortest Path through Ancestors (2)(if found), otherwise Shortest Path through all relations (0)</option>
					<option value=12 <?php if (isset($_POST['relation']) && $_POST['relation'] == 12) {echo "selected";} ?> >12 = TBD </option>
					
				</select>
			</td>
		</tr>
		<tr><td colspan=3>
			<input type='checkbox' name=WTCmode value=Y <?php if ($_GET['WTCmode'] == 'Y' || $_POST['WTCmode'] == 'Y') { print "checked";} ?> > use for WikiTree Challenge<BR>
			<button class=button name=doSubmit type="submit" value="Get Connections">Get Connections</button> &nbsp;&nbsp;&nbsp;&nbsp;
			<button class=button name=doReverse id=doReverse type="submit" value="Reverse Order">Reverse Order</button>
		</td></tr>
		</table>
		<input type=checkbox onclick=showHideTheThing('Note'); id=showHideNoteCheckbox name=showHideNote value=show > <I>Note: <span id=NoteToShowHide style='display:none;'>the following fields need to be added in order to draw the visual path below: <BR>Derived.BirthNamePrivate,BirthDate,DeathDate,Photo,DataStatus,Gender,IsLiving<BR><BR>
			If WikiTree Challenge mode is enabled, the visual path  below is modified to include a header that can be used in images for the Reveal.<BR>Additional fields needed for this include: BirthDateDecade, BirthLocation, DeathDateDecade, DeathLocation, BioFather, BioMother</span> </I>
	</p>

	</form>

	  <h2><input type=checkbox onclick=showHideTheThing('Results'); id=showHideResultsCheckbox name=showHideResults value=show > Results</h2>
	<span id=ResultsToShowHide style='display:none;'>
		<blockquote id="result"><?php print $resultHTML; ?></blockquote>
	</span>

	
	<h2>Path</h2>
	<blockquote id="path"><?php print $pathHTML; ?></blockquote>

	<h2>JSON Results</h2>
	<blockquote id="json"><?php print $resultJSON; ?></blockquote>
</article>

Last updated: Thu Jul 23 - 6:59 p.m.

</body>
</html>

<script>
	function showHideTheThing(ThingName){
		console.log("SHOW HIDE the ", ThingName); //id=showHideNote name=showHideNote value=show> <I>Note: <span id=NoteToShowHide
		let theThing = document.getElementById(ThingName + "ToShowHide");
		let doShow = document.getElementById("showHide" + ThingName + "Checkbox" ).checked;
		if (doShow) {
			theThing.style.display = "revert";
		} else {
			theThing.style.display = "none";
		}

	}; 
</script>