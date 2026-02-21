<!DOCTYPE html>
<html lang="pl">
	<head>
		<meta charset="utf-8">
		<title>ZarMag - Login</title>
		<link href="style.css" rel="stylesheet">
	</head>
	<body class="loginWindow">
	
	<?php
	session_start();
	
	$error = null;
	// PRZETESTUJ DZIALANIE + DODAJ DO BAZY DANYCH UZYTKOWNIKOW + POMYSL CZY NIE ODDZIELIC LOGIKI
	
	/* --- OBSLUGA LOGOWANIA --- */
	if (isset($_POST['login']) && isset($_POST['haslo']))
	{
		
		$polaczenie = mysqli_connect("localhost", "root", "","zarmagdb");
		if(!$polaczenie) { exit("Błąd połączenia z bazą danych!"); }

		$podanyLogin = $_POST['login'];
		$podaneHaslo = $_POST['haslo'];
		
		$sql = "SELECT * FROM users WHERE Login = '$podanyLogin'";
		$wynik = mysqli_query($polaczenie, $sql);
		
		if (mysqli_num_rows($wynik) == 1)
		{
			/* --- POBRANIE DANYCH UZYTKOWNIKA DO TABLICY --- */
			$user = mysqli_fetch_assoc($wynik);
			
			/* --- SPRAWDZANIE HASLA --- */
			
			if (password_verify($podaneHaslo, $user['Haslo'])) 
			{ 
				$_SESSION['zalogowany'] = true;
				$_SESSION['userId'] = $user['IdPracownika'];
				$_SESSION['userLogin'] = $user['Login'];
				$_SESSION['imieUzytkownika'] = $user['Imie'];
				
				/* --- PRZEKIEROWANIE DO STRONY GLOWNEK -- */
				header("Location: mainWindow.php");
				exit();
			}	
			else
			{
				/* --- W PRZYPADKU ZLEGO HASLA --- */
				$error = "Podane haslo jest nieprawidlowe";
			}
		}
		else 
		{
			/* --- UZYTKOWNIK NIE ISTNIEJE --- */
			$error = "Dane nieprawidlowe";
		}
		
		/* --- ZAMYKAMY POLACZENIE --- */
		mysqli_close($polaczenie);
	}
	?>
		
		<!-- OKNO LOGOWANIA -->
		<div class="loginContainer">
			
			<!-- DIVISION ZAWIERAJACY LOGO -->
			<div class="logoSection">
				<img src="resources/companyLogo.png" class="appLogo"  alt="Logo Aplikacji">
			</div>
			
			<!-- DIVISION ZAWIERAJACY SEKCJE LOGOWANIA (INPUTS) -->
			<div class="loginInputsSection">
				<?php if ($error): ?>
				<div class="errorMessage">
					<?= htmlspecialchars($error) ?>
				</div>
				<?php endif ?>
				
				<form action="" method="POST">
					
					<div class="inputField">
						<label for="login">Login:</label>
						<input type="text" id="login" name="login" required placeholder="Wprowadź login">
					</div>
					
					<div class="inputField">
						<label for="haslo">Password:</label>
						<input type="password" id="haslo" name="haslo" required placeholder="Wprowadź hasło">	
					</div>
				
					<button type="submit" class="customBtn">Zaloguj</button>
					<a href="remindPasswordWindow.php" class="forgotPasswordBtnLink">Zapomniałem hasła</a>
				</form>
			</div>
		
		</div>
	</body>
</html>