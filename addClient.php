<?php

	if (!isset($_SESSION['zalogowany']))
	{
		header("Location: loginWindow.php");
		exit();
	}
	
	include_once __DIR__ . '/helpers/dataBaseConnector.php';
	
	$error = null;
	$successMsg = null;
	
	/* --- LOGIKA REJESTRACJI NOWYCH KLIENOTW --- */
	if (isset($_POST['nazwa']) && $_POST['action'] == 'dodajKlienta') 
	{
		$nazwa = trim($_POST['nazwa']); 
		$adres = trim($_POST['adres']); 
		$nrTelefonu = trim($_POST['nrTelefonu']); 
		$email = trim($_POST['email']); 
	 
		if ($nazwa === '' || $adres === '' || $nrTelefonu === '' ||
			$email === '') 
		{		
			$error = "Wszystkie pola muszą być wypełnione.";  
		}
		
		elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
		{
			$error = "Nieprawidłowy adres email.";
		}
		
		else 
		{
			/* --- POLACZENIE Z BAZA --- */
			$polaczenie = polaczZBaza();
			
			if(!$polaczenie)
			{
				$error = "Nie można połączyć z bazą danych!";
			}
			
			/* --- UPEWNIAMY SIE ZE OBSLUZY POSLKIE ZNAKI --- */
			$polaczenie->set_charset("utf8mb4");
			
			/* --- SPRAWDZAMY CZY LOGIN ISTNIEJE --- */
			$sql = "SELECT * FROM clients WHERE nazwa = '$nazwa'";
			$wynik = mysqli_query($polaczenie, $sql);
			
			if (mysqli_num_rows($wynik) > 0)
			{
				$error = "Klient o podanej nazwie już istnieje w bazie danych!";
			}
			
			else
			{
				
				$sql = "INSERT INTO clients (
											nazwa, adres, nrTelefonu, email
										  )
						VALUES
						(
							'$nazwa', '$adres', '$nrTelefonu', '$email'
						)";
				
				if (mysqli_query($polaczenie, $sql))
				{
					$successMsg = "Klient został pomyślnie dodany!";
				}
				else
				{
					$error = "Błąd podczas zapisu w bazie danych!";
				}
			}
			mysqli_close($polaczenie);
		}
	}
?>

<div class="registrationContainer">
	
	<div class="registrationInputSection">
	
		<?php if ($error): ?>
				<div class="errorMessage">
					<?= htmlspecialchars($error) ?>
				</div>
		<?php endif; ?>
		
		<?php if ($successMsg): ?>
            <div class="successMessage">
                <?= htmlspecialchars($successMsg) ?>
            </div>
        <?php endif; ?>
	
		<form method="POST" action="clients.php">
			<input type="hidden" name="action" value="dodajKlienta">
			
			<div class="inputField">
				<label for="nazwa">Nazwa:</label>
				<input type="text" id="nazwa" name="nazwa" required placeholder="Wprowadź nazwe">
			</div>
				
			<div class="inputField">
				<label for="adres">Adres:</label>
				<input type="text" id="adres" name="adres" required placeholder="Wprowadź adres">
			</div>
				
			<div class="inputField">
				<label for="nrTelefonu">Numer Telefonu:</label>
				<input type="text" id="nrTelefonu" name="nrTelefonu" required placeholder="Wprowadź nrTelefonu">
			</div>
				
			<div class="inputField">
				<label for="email">Adres email:</label>
				<input type="email" id="email" name="email" required placeholder="Wprowadź email">
			</div>
			
			<input type="submit" value="Zarejestruj" class="customBtn">
			
		</form>
	</div>

</div>
