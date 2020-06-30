<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">	
	<title>AmlCheck | Search Result</title>
	<link rel="stylesheet" type="text/css" href="styles.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<script src="script.js"></script>
</head>
<body>

<nav class="topnav">
  <a class="active" href="index.html"><i class="fa fa-fw fa-home"></i> Home</a>
  <a href="#contact"><i class="fa fa-fw fa-phone"></i> Contact</a>
  <a href="#about"><i class="fa fa-fw fa-user"></i> About</a>
</nav>
<br>

<main>
<fieldset class="campo">
	<br>
	<legend><b>AML Check</b></legend>
	<form method="get" action="consulta.php">
		<b>Entity Name:</b> <input id="name" type="text" name="nome" placeholder="Search"/>
		<button type="submit" class="botao"><i class="fa fa-fw fa-search"></i></button>
		<br><br>Similarity: <input type="range" id="similaridade" name="similaridade" min="0" max="100" value="70" class="slider"><span id="similar"></span>%
	</form>
</fieldset>

<script>
		var slider = document.getElementById("similaridade");
		var output = document.getElementById("similar");
		output.innerHTML = slider.value;
		
		slider.oninput = function() {
		  output.innerHTML = this.value;
		}
</script>

<script>
function printDiv() { 
    var divContents = document.getElementById("printable").innerHTML; 
    var a = window.open('', '', 'height=500, width=500'); 
    a.document.write('<html>'); 
    a.document.write('<body >'); 
    a.document.write(divContents); 
    a.document.write('</body></html>'); 
    a.document.close(); 
    a.print(); 
}
</script>

<br/>

<fieldset id="printable">
	<legend><b>Result</b></legend>

	<b>Today's Date: </b><spam id="time"></spam>
	
	<script>
		document.getElementById("time").innerHTML = new Date();
	</script>

	<?php
	$servername = "localhost";
	$username = "root";
	$password = "password";
	$dbname = "sancoes";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} 

	$nome = isset($_GET["nome"])?$_GET["nome"]:"";
	$similaridade = isset($_GET["similaridade"])?$_GET["similaridade"]:"";

//	function plevenshtein($str1, $str2) {
//		$stopwords = ['s.a', 'a.s', 'ltda', 'ltd', 'limited', 'b.v', 'corp.', 'inc.', 's.r.l.', 's.r.o.', 's.a.r.l.', 'de c.v.', 'ooo', 'e.i.r.l.'];
//		$lstr1 = str_replace($stopwords, "", strtolower($str1));
//		$lstr2 = str_replace($stopwords, "", strtolower($str2));
//		return number_format((1 - levenshtein($lstr1, $lstr2)/max(strlen($lstr1), strlen($lstr2)))*100,2);
//	} 

	function superlevenstein($str1, $str2){
		$stopwords = ['s.a', 'a.s', 'ltda', 'ltd', 'limited', 'b.v', 'corp.', 'inc.', 's.r.l.', 's.r.o.', 's.a.r.l.', 'de c.v.', 'ooo', 'de c.v.'];
		$alstr1 = explode(" ", str_replace($stopwords, "", strtolower($str1)));
		$alstr2 = explode(" ", str_replace($stopwords, "", strtolower($str2)));

		for ($x = 0; sizeof($alstr1); $x++) {
			$compare = array();
			for ($y = 0; sizeof($alstr2); $y++){
				array_push($compare, number_format((1 - levenshtein($alstr1[$x], $alstr2[$x])/max(strlen($alstr1[$x]), strlen($alstr2[$x])))*100,2));
				$maximo = max($compare);
				return $maximo;
			}
		}
	}
	
	//AS a WHERE levenshtein_ratio(a.SDN_Name, '$nome') > 85 OR a.SDN_Name like '%$nome%'
	//AS b WHERE levenshtein_ratio(b.SDN_Name, '$nome') > 85 OR b.SDN_Name like '%$nome%'
	//ORDER BY levenshtein_ratio(SDN_Name, '$nome') DESC";
	$sql = "SELECT SDN_Name, Program FROM sdn 
			UNION 
			SELECT SDN_Name, Program FROM non_sdn";	
			
	$result = $conn->query($sql);

	echo "<b> | Term Consulted: </b><spam>'$nome'</spam>";
	echo "<b> | Similarity: </b><spam>$similaridade%</spam><br><br>";

	echo '<button type="button" onclick="printDiv()"><i class="fa fa-fw fa-download"></i></button><br>';
	
	echo "<table>
		<tr>
		<th>Entity Name</th>
		<th>Program</th>
		<th>Similarity (%)</th>
		</tr>";
		
		$final = array();
		// output data of each row
		while($row = $result->fetch_assoc()) {
			if(superlevenstein($row["SDN_Name"],$nome) > $similaridade){
			//	echo "<tr><td>".$row["SDN_Name"]."</td>
			//	<td>".$row["Program"]."</td>
			//	<td>".plevenshtein($row["SDN_Name"],$nome)."%</td></tr>";
				array_push($final, array("name"=>$row["SDN_Name"], "program"=>$row["Program"], "similarity"=>superlevenstein($row["SDN_Name"],$nome)));
			}
		}

	//echo "</table>";

	$sim = array_column($final, 'similarity');
	array_multisort($sim, SORT_DESC, $final);

	foreach($final as $arr){
	//	echo "<p>".json_encode($arr['name'])."</p>";
		echo "<tr><td>".str_replace('"', "", json_encode($arr['name']))."</td>";
		echo "<td>".str_replace('"', "", json_encode($arr['program']))."</td>";
		echo "<td>".str_replace('"', "", json_encode($arr['similarity']))."</td></tr>";
	}
	
	echo "</table>";

	//if(!$row){echo "0 results found";}
	
	$conn->close();
	?>

</fieldset>
<br>
</main>

<footer><i><small>Developed by Guilherme Santoro, Certified Anti-Money Laudering Specialist&reg</small></i></footer>
</body>
</html>
