<?php

	session_start();

	$y_tiedot = "dbname=ccjami user=ccjami password=password_hidden";

	if (!$yhteys = pg_connect($y_tiedot))
	   die("Tietokantayhteyden luominen epäonnistui.");
		
	if (isset($_POST['lomake1'])) {
		
		$_SESSION['siirrettava_summa'] = $_POST['siirrettava_summa'];
		$_SESSION['veloitettava_tnro'] = $_POST['veloitettava_tnro'];
		$_SESSION['siirronkohde_tnro'] = $_POST['siirronkohde_tnro'];
		
		$siirrettava_summa = $_POST['siirrettava_summa'];
		$veloitettava_tnro = $_POST['veloitettava_tnro'];
		$siirronkohde_tnro = $_POST['siirronkohde_tnro'];
			
		pg_query('BEGIN')
				or die('ei onnistu:' . pg_last_error());
				
			$kysely_yksi = pg_query("UPDATE TILIT SET summa = summa - '$siirrettava_summa' WHERE tilinumero = '$veloitettava_tnro' AND summa > '$siirrettava_summa'")
				or die ('Siirto ei onnistunut' . pg_last_error());
			$kysely_kaksi = pg_query("UPDATE TILIT SET summa = summa + '$siirrettava_summa' WHERE tilinumero = '$siirronkohde_tnro'")
				or die ('virhe' . pg_last_error());
				
			if (pg_affected_rows($kysely_yksi) != 1) {
				
				pg_query('ROLLBACK')
					or die('ei onnistuttu perumaan' . pg_last_error());
				echo 'Veloitettavan tilin numero on väärä tai saldo ei riitä';
				return 'Veloitettavan tilin numero on väärä tai tilin saldo ei riitä.';
			}
			
			if (pg_affected_rows($kysely_kaksi) != 1) {
				
				pg_query('ROLLBACK')
					or die('ei onnistuttu perumaan' . pg_last_error());
				echo 'Vastaanottajan tilinumeroa ei löydetty';
				return 'Vastaanottajan tilinumeroa ei löydetty.';
			}
						
			pg_query('COMMIT')
				or die('ei onnistuttu hyväksymään' . pg_last_error());
				
		/*$_SESSION['veloitettavan_nimi'] = $_POST['veloitettavan_nimi'];
		$_SESSION['vastaanottajan_nimi'] = $_POST['vastaanottajan_nimi'];*/	

		$veloitettava_kysely = pg_query("SELECT omistaja FROM TILIT WHERE tilinumero = '$veloitettava_tnro'");
		if (!$veloitettava_kysely) {
			echo "virhe kyselyssä. \n";
			exit;
		}
		
		while ($rivi = pg_fetch_row($veloitettava_kysely)) {
			$_SESSION['veloitettavan_nimi'] = $rivi[0];
		}
		
		$vastaanottaja_kysely = pg_query("SELECT omistaja FROM TILIT WHERE tilinumero = '$siirronkohde_tnro'");
		if (!$vastaanottaja_kysely) {
			echo "virhe kyselyssä. \n";
			exit;
		}
		
		while ($rivi = pg_fetch_row($vastaanottaja_kysely)) {
			$_SESSION['vastaanottajan_nimi'] = $rivi[0];
		}
		
		header('Location: lomake2.php');
				
	}

	pg_close($yhteys);
?>

<html>
 <head>
  <title>Tilisiirto</title>
 </head>
 <body>

    <!-- Lomake lähetetään samalle sivulle (vrt lomakkeen kutsuminen) -->
    <form action="lomake1.php" method="post">

    <h2>TILISIIRTO</h2>

    <?php if (isset($viesti)) echo '<p style="color:red">'.$viesti.'</p>'; ?>

	<!—PHP-ohjelmassa viitataan kenttien nimiin (name) -->
	<table border="0" cellspacing="0" cellpadding="3">
	    <tr>
    	    <td>Summa</td>
    	    <td><input type="decimal" name="siirrettava_summa" value="" /></td>
	    </tr>
	    <tr>
    	    <td>Veloitettavan tilinumero</td>
    	    <td><input type="integer" name="veloitettava_tnro" value="" /></td>
	    </tr>
	    <tr>
    	    <td>Tilinumero jonne summa siirretään</td>
    	    <td><input type="integer" name="siirronkohde_tnro" value="" /></td>
	    </tr>
	</table>

	<br />

	<!-- hidden-kenttää käytetään varotoimena, esim. IE ei välttämättä
	 lähetä submit-tyyppisen kentän arvoja jos lomake lähetetään
	 enterin painalluksella. Tätä arvoa tarkkailemalla voidaan
	 skriptissä helposti päätellä, saavutaanko lomakkeelta. -->

	<input type="hidden" name="tallenna" value="jep" />
	<input type="submit" name="lomake1" value="SIIRTO" />
	</form>

</body>
</html>
