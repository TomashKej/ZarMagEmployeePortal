<?php
	
	require_once __DIR__ . '/helpers/dataBaseConnector.php';
	
	if (!isset($_SESSION['zalogowany']))
	{
		header("Location: loginWindow.php");
		exit();
	}
	$error = null;
	$successMsg = null;
	
	/* --- LOGIKA REJESTRACJI NOWYCH UZYTKOWNIKOW --- */
	if (isset($_POST['login']) && $_POST['action'] == 'dodajUzytkownika') 
	{
		$idPracownika = trim($_POST['idPracownika']); 
		$imie = trim($_POST['imie']); 
		$nazwisko = trim($_POST['nazwisko']); 
		$plec = $_POST['plec']; 
		$email = trim($_POST['email']); 
		$stanowisko = trim($_POST['stanowisko']); 
		$login = trim($_POST['login']); 
		$haslo = $_POST['haslo']; 
		$pytaniePomocnicze = trim($_POST['pytaniePomocnicze']); 
		$odpowiedz = trim($_POST['odpowiedz']); 
		$dataUrodzenia = $_POST['dataUrodzenia'];
	 
		if ($idPracownika === '' || $imie === '' || $nazwisko === '' ||
			$plec === '' || $email === '' || $stanowisko === '' ||
			$login === '' || $haslo === '' || $pytaniePomocnicze === '' ||
			$odpowiedz === '' || $dataUrodzenia === '') 
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
			$sql = "SELECT * FROM users WHERE login = '$login'";
			$wynik = mysqli_query($polaczenie, $sql);
			
			if (mysqli_num_rows($wynik) > 0)
			{
				$error = "Uzytkownik o podanym loginie już istnieje w bazie danych!";
			}
			
			else
			{
				$hasloHash = password_hash($haslo, PASSWORD_DEFAULT);
				$odpowiedzHash = password_hash($odpowiedz, PASSWORD_DEFAULT);
				
				$sql = "INSERT INTO users (
											idPracownika, imie, nazwisko, plec, 
											email, stanowisko, login, haslo, 
											pytaniePomocnicze, dataUrodzenia, odpowiedz
										  )
						VALUES
						(
							'$idPracownika', '$imie', '$nazwisko', '$plec', 
							'$email', '$stanowisko', '$login', '$hasloHash', 
							'$pytaniePomocnicze', '$dataUrodzenia', '$odpowiedzHash'
						)";
				
				if (mysqli_query($polaczenie, $sql))
				{
					$successMsg = "Użytkownik został pomyślnie zarejestrowany!";
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
	
		<form method="POST" action="panelAdministracjiWindow.php">
			<input type="hidden" name="action" value="dodajUzytkownika">
			
			<div class="inputField">
				<label for="idPracownika">Id Pracownika:</label>
				<input type="text" id="idPracownika" name="idPracownika" required placeholder="Wprowadź ID pracownika">
			</div>
			
			<div class="inputField">
				<label for="imie">Imię:</label>
				<input type="text" id="imie" name="imie" required placeholder="Wprowadź imię">
			</div>
				
			<div class="inputField">
				<label for="nazwisko">Nazwisko:</label>
				<input type="text" id="nazwisko" name="nazwisko" required placeholder="Wprowadź nazwisko">
			</div>
				
			<div class="inputField">
				<label for="plec">Płeć:</label>
				<input type="text" id="plec" name="plec" required placeholder="Wprowadź płeć">
			</div>
				
			<div class="inputField">
				<label for="email">Adres email:</label>
				<input type="text" id="email" name="email" required placeholder="Wprowadź email">
			</div>
				
			<div class="inputField">
				<label for="stanowisko">Stanowisko:</label>
				<input type="text" id="stanowisko" name="stanowisko" required placeholder="Wprowadź stanowisko">
			</div>
				
			<div class="inputField">
				<label for="login">Login:</label>
				<input type="text" id="login" name="login" required placeholder="Wprowadź login">
			</div>
				
			<div class="inputField">
				<label for="haslo">Hasło:</label>
				<input type="password" id="haslo" name="haslo" required placeholder="Wprowadź hasło">
			</div>
			
			<div class="inputField">
				<label for="pytaniePomocnicze">Pytanie pomocnicze:</label>
				<textarea id="pytaniePomocnicze" name="pytaniePomocnicze" required placeholder="Wprowadź pytanie pomocnicze"></textarea>
			</div>
			
			<div class="inputField">
				<label for="odpowiedz">Odpowiedź na pytanie pomocnicze</label>
				<input type="password" id="odpowiedz" name="odpowiedz" required placeholder="Wprowadź odpowiedź">
			</div>
				
			<div class="inputField">
				<label for="dataUrodzenia">Data urodzenia:</label>
				<input type="date" id="dataUrodzenia" name="dataUrodzenia" required placeholder="Wprowadź date urodzenia">
			</div>
			
			<input type="submit" value="Zarejestruj" class="customBtn">
			
		</form>
	</div>

</div>
