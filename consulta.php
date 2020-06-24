<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8"/>
	<title>Search Result</title>
	<link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>

<nav class="topnav">
  <a class="active" href="home.html">Home</a>
  <a href="#news">News</a>
  <a href="#contact">Contact</a>
  <a href="#about">About</a>
</nav><br>

<fieldset class="campo">
	<br>
	<legend><b>Consulte Aqui as Listas de Sanções Internacionais</b></legend>
	<form method="get" action="consulta.php">
		<b>Nome:</b> <input type="text" name="nome" placeholder="Search"/>
		<input type="submit" value="Procurar" class="botao"/>
	</form>
</fieldset>

<br/>

<fieldset>
	<legend><b>Resultado</b></legend>

	<?php
	$servername = "localhost";
	$username = "root";
	$password = "mestrAdo123";
	$dbname = "sancoes";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} 

	$nome = isset($_GET["nome"])?$_GET["nome"]:"";

	function plevenshtein($str1, $str2) {
		$lstr1 = strtolower($str1);
		$lstr2 = strtolower($str2);
		return number_format((1 - levenshtein($lstr1, $lstr2)/max(strlen($lstr1), strlen($lstr2)))*100,2);
	}
	
	//AS a WHERE levenshtein_ratio(a.SDN_Name, '$nome') > 85 OR a.SDN_Name like '%$nome%'
	//AS b WHERE levenshtein_ratio(b.SDN_Name, '$nome') > 85 OR b.SDN_Name like '%$nome%'
	//ORDER BY levenshtein_ratio(SDN_Name, '$nome') DESC";
	$sql = "SELECT SDN_Name, Program FROM sdn 
			UNION 
			SELECT SDN_Name, Program FROM non_sdn";	
			
	$result = $conn->query($sql);

	echo "<table>
		<tr>
		<th>Entity Name</th>
		<th>Program</th>
		<th>Similarity</th>
		</tr>";
		
	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			if(plevenshtein($row["SDN_Name"],$nome)>30){
				echo "<tr><td>".$row["SDN_Name"]."</td>
				<td>".$row["Program"]."</td>
				<td>".plevenshtein($row["SDN_Name"],$nome)."%</td></tr>	";
			}
		}
	} else {
	
	echo "0 results found";
	}
	
	echo "</table>";
	
	$conn->close();
	?>

</fieldset>
<br>
<footer><i><small>Desenvolvido por<br/>Guilherme Santoro<br/>Certified Anti-Money Laudering Specialist&reg</small></i></footer>
</body>
</html>