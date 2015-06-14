<?php

$bibtex = "@article{adamsSasse,
  title={Users are not the enemy},
  author={Adams, Anne and Sasse, Martina Angela},
  journal={Communications of the ACM},
  volume={42},
  number={12},
  pages={40--46},
  year={1999},
  publisher={ACM}
}";


//get each different reference in the list...
//echo'<pre>';

//echo'</pre>';

function render_reference($identifier){

	$references = get_list_of_references();
	//check for the identifier
	if(array_key_exists($identifier, $references)){
		$reference = $references[$identifier];
		$type = $reference[0];
		$parsed_reference = parse_reference($reference[1]);


		//Some variables which may be used a few times:
		$author  = $parsed_reference['author'];
		$title = $parsed_reference['title'];
		$booktitle = $parsed_reference['booktitle'];
		$journal = $parsed_reference['journal'];
		$pages = $parsed_reference['pages'];
		$year = $parsed_reference['year'];
		$editor = $parsed_reference['editor'];
		$howpublished = $parsed_reference['howpublished'];

		$location = $parsed_reference['location'];
		if($location != NULL)
			$location = $location . ', ';

		$booktitle = '<span class="italics">' . $parsed_reference['booktitle'] . '</span>';
		$publisher = $parsed_reference['publisher'];
		if($publisher != NULL)
			$publisher = $publisher . ', ';

		$author_title = "$author. $title.";
		$vol_issue = $parsed_reference['issue'] == NULL ? $parsed_reference['volume'] . '. <span class="italics"' : $parsed_reference['volume'] . 
			'</span>(' . $parsed_reference['issue'] . '). ';


		switch ($type) {
			case 'article':				
				return $author_title . ". <span class=\"italics\">$journal</span>, $vol_issue, $pages, $year."; 
					
			case 'book':
				return "$author . <span class=\"italics\">$title</span>, $publisher $location $year.";
			case 'incollection' :
				return "$author_title in $editor $booktitle $location $year, $pages.";

			case 'inproceedings':				
				return "$author_title in <span class=\"italics\" $booktitle</span> ($location$year),$publisher $pages.";
			
			case 'techreport':
				return 'techreport';

			case 'misc'://assume it's a URL...
				return "$identifier misc";
			default:
				return "Something went wrong with $identifier";
				
		}

	}
	else{
		return "$identifier";
	}
}

function parse_reference($bibtex){
	//echo "$bibtex";
	$matches = array();
	$new_array = array();
	preg_match_all('/(address|annote|author|booktitle|chapter|crossref|edition|editor|howpublished|institution|journal|key|month|note|number|
		organization|pages|publisher|school|series|title|type|volume|year) *= *\\{(.*?)\\}/', $bibtex, $matches);


	for($i=0; $i< count($matches[1]); $i++){
		$new_array[$matches[1][$i]] = $matches[2][$i];
	}

	return $new_array;
	foreach ($new_array as $key => $value) {
		echo "Key: $key Value: $value<br />";
	}

}

function get_list_of_references(){
	$references = array();
	$matches = array();
	preg_match_all('/@[^@]+/', file_get_contents('references.bib'), $matches);
	//print_r($references[0]);
	foreach ($matches[0] as $m) {
		$title_match = array();
		preg_match('/@(article|book|booklet|conference|inbook|incollection|inproceedings|manual|mastersthesis|misc|phdthesis|proceedings|techreport|
			unpublished)\\{([^,]*),/', $m, $title_match);
		$references[$title_match[2]] = array($title_match[1],$m);
	}	

	return $references;
}








?>