<?php

	$tex = file_get_contents('paper.tex');
	//$tex = htmlentities($tex);
	//echo $tex;
	//$pregArray = array();
	//get rid of the commands at the start:

	$pregArray = array();
	//deal with the headings
	preg_match("/\\\begin\\{abstract\\}(\r\n)*(.*)(\r\n)*\\\end\\{abstract\\}/", $tex, $pregArray);// '<p class="abstract">$2</p>', $tex);
	$abstract = $pregArray[2];
	preg_match('/\\\title\\{([^}]*)\\}/', $tex, $pregArray);
	$title = $pregArray[1];	

	//comments		
	$tex = preg_replace("/\r\n%.*?\r\n/", "", $tex);

	$tex = str_replace(substr($tex, 0, strpos($tex, '\end{abstract}') + 14), "<h1>$title</h1><h2>Abstract</h2><p id=\"abstract\">$abstract</p>", $tex);
	$tex = preg_replace('/\\\section\\{([^}]*)\\}/', '<h2>$1</h2>', $tex);
	$tex = preg_replace('/\\\subsection\\{([^}]*)\\}/', '<h3>$1</h3>', $tex);
	$tex = preg_replace('/\\\subsubsection\\{([^}]*)\\}/', '<h4>$1</h4>', $tex);
	$tex = preg_replace("/[^\\\]%(.*?)\r\n/", '', $tex);
	

	//paragraphs
	$tex = preg_replace('~(</h1>|</h2>|</h3>|</h4>)~', "$1\n<p>", $tex);
	$tex = str_replace("\r\n\r\n", '</p>', $tex);
	$tex = str_replace("\n\n", '</p>', $tex);
	$tex = preg_replace("~(</p>\r\n*)[A-Z]~", '$1<p>', $tex);

	//quotes
	$tex = preg_replace("/\\\begin\\{quote\\}(\r\n)*(.*)(\r\n)*\\\end\\{quote\\}/", '<blockquote>$2</blockquote>', $tex);
	$tex = str_replace('``', '"', $tex);
	$tex = str_replace('`', '\'', $tex);

	//URLs
	$tex = preg_replace('/\\\url\\{([^}]*)\\}/', '<a href="$1">$1</a>', $tex);

	//Lists
	$tex = str_replace('\\begin{enumerate}', '<ol>', $tex);
	$tex = str_replace('\\end{enumerate}', '</ol>', $tex);
	$tex = str_replace('\\begin{itemize}', '<ul>', $tex);
	$tex = str_replace('\\end{itemize}', '</ul>', $tex);	
	$tex = preg_replace("/\\\item(.*?)(\r)*\n/", '<li>$1</li>', $tex);

	//formatting
	$tex = preg_replace('/\\\emph\\{([^}]*)\\}/', '<em>$1</em>', $tex);
	$tex = preg_replace('/\\\textit\\{([^}]*)\\}/', '<em>$1</em>', $tex);
	$tex = preg_replace('/\\\textbf\\{([^}]*)\\}/', '<strong>$1</strong>', $tex);
	$tex = preg_replace('/\\\texttt\\{([^}]*)\\}/', '<code>$1</code>', $tex);
	
	//code
	$tex = str_replace('\\begin{lstListing}', '<code><pre>', $tex);
	$tex = str_replace('\\end{lstListing}', '</pre></code>', $tex);

	//citations
	preg_match_all('/\\\(footcite|cite|citep|autocite)([^}]*)\\}/', $tex, $pregArray);
	preg_replace("/\\\bibliography\\{([^}]*?)\\}/", '<h2>Bibliography</h2>', $tex);
	$references = $pregArray[0];
	$used_references = array();

	$i=0;
	
	foreach($references as $r){
		$text = $r;
		$refNo = $i + 1;
		$split = explode('{', $text);//[1];
		$split = $split[1];
		$text = substr($split, 0, strlen($split) - 1) ;		
		if(!in_array($text, $used_references)){
			$used_references[] = $text;
			$i++;
		}		
		else{
			$refNo = array_search($text, $used_references);
			
		}	
		$tex = str_replace($r, '<a id="' . $refNo . '" href="#ref' . $refNo . '">[' . $refNo . ']</a>', $tex);	
	}

	//table
	$tex = str_replace('\\begin{table}', '<table>', $tex);	
	$tex = str_replace('\\end{table}', '</table>', $tex);
	$tex = preg_replace('/\\\caption\\{([^}]*)\\}/', '<caption>$1</caption>', $tex);
	$tex = str_replace('\\hline', '<tr>', $tex);	
	$tex = str_replace("&", "</td><td>", $tex);
	$tex = str_replace('\\\\', "</td></tr>", $tex);
	$tex = str_replace("<tr>\r\n", "<tr><td>", $tex);
	$tex = preg_replace("/\\\(begin|end)\\{tabular\\}.*\r\n/", '', $tex);



	//unescaping
	$tex = str_replace('\\%', '%', $tex);
	$tex = str_replace('\\$', '$', $tex);
	$tex = str_replace('\\_', '_', $tex);
	$tex = str_replace('\\&', '&', $tex);



?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo $title; ?></title>	
	<style type="text/css">
	#wholePage{
		width: 960px;
		margin: auto;
		font-family:'lucida grande',tahoma,verdana,arial,sans-serif;
 	}
 	#abstract{
 		font-size: small;
 	}
 	.comment{
 		color: #aaaaaa;
 	}
 	.italics{
 		font-style: italic;
 	}
	</style>
</head>
<body>
	<div id="wholePage">
	<p><strong>Original files:</strong>&nbsp;<a href="paper.tex">.tex file</a>&nbsp;<a href="references.bib">.bib file</a></p>
	
	<?php echo $tex; ?>
	<ol>
		<?php
		require_once 'parsebibtex.php';
		//$references = get_list_of_references();
		for($i=0; $i<sizeof($used_references); $i++){
?>
<li id="ref<?php echo ($i + 1); ?>"><?php echo render_reference($used_references[$i]); ?></li>
<?php			
		}

?>
	</ul>
	</div>
</body>
</html>	

