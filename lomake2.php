<!DOCTYPE html>

<?php

	session_start();
	
	echo '<p> ' . $_SESSION['veloitettavan_nimi'];
	echo ' on siirtänyt ' . $_SESSION['siirrettava_summa'] . ' euroa ';
	echo ' henkilölle ' . $_SESSION['vastaanottajan_nimi'] . '</p>';
	
	if(isset($_POST['poistu'])) {
		session_destroy();
		header('Location: lomake1.php');
	}
?>

<html>
	<head>
		<meta charset="utf-8" />
		<link href="/style.css" rel?"stylesheet" />
		<title>Tilisiirto</title>
	</head>
	<body>
		<form method="post" action="lomake2.php">
			<input type="submit" name="poistu" value="Poistu"/>
		</form>
	</body>
</html>