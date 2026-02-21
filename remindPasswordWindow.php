<!DOCTYPE html>
<html lang="pl">
	<head>
		<meta charset="utf-8">
		<title>ZarMag - Przypomnij hasło</title>
		<link href="style.css" rel="stylesheet">
	</head>
	<body class="remindPasswordWindow">
		
	<?php
	
	require_once __DIR__ . '/helpers/dataBaseConnector.php';
	session_start();
	
	$error = null;
	
	if (!isset($_SESSION['etap']))
	{
			$_SESSION['etap'] = 1;
	}
	
	/* OBSŁUGA RESETOWANIA HASLA ETAP 1 - WERYFIKACJA DANYCH UZYTKOWNIKA */
	if ($_SESSION['etap'] == 1)
	{
		if (isset($_POST['loginInput']) && isset($_POST['idPracownikaInput']) && isset($_POST['dataUrodzeniaInput']))
		{
			$polaczenie = polaczZBaza();
			
			$podanyLogin = $_POST['loginInput'];
			$podaneIdpracownika = $_POST['idPracownikaInput'];
			$podanaDataUrodzenia = $_POST['dataUrodzeniaInput'];
			
			$sql = "SELECT * FROM users WHERE Login = '$podanyLogin'";
			$wynik = mysqli_query($polaczenie, $sql);
			
			if (mysqli_num_rows($wynik) == 1)
			{
				$user = mysqli_fetch_assoc($wynik);
				
				if ($podaneIdpracownika == $user['IdPracownika'] && $podanaDataUrodzenia == date('Y-m-d', strtotime($user['DataUrodzenia'])))
				{
					$_SESSION['idPracownika'] = $user['IdPracownika'];
					$_SESSION['pytaniePomocnicze'] = $user['PytaniePomocnicze'];
					$_SESSION['etap'] = 2;
				}
				else
				{
					$error = "Nieprawidłowa data urodzenia bądź Id pracownika !";
				}		
			}
			else
			{
				$error = "Nie znaleziono użytkownika dla podanych danych !";
			}
			
			mysqli_close($polaczenie);
		}
	}
	
	/* OBSŁUGA RESETOWANIA HASLA ETAP 2 - WERYFIKACJA PYTANIA POMOCNICZEGO */
	if($_SESSION['etap'] == 2)
	{
		if (isset($_POST['odpowiedzPomocnicza']))
		{
			$polaczenie = polaczZBaza();
			$podanaOdpowiedzPomocnicza = $_POST['odpowiedzPomocnicza'];
			$idPracownika = $_SESSION['idPracownika'];
			
			$sql = "SELECT * FROM users WHERE IdPracownika = '$idPracownika'";
			$wynik = mysqli_query($polaczenie, $sql);
			
			if (mysqli_num_rows($wynik) == 1)
			{
				$user = mysqli_fetch_assoc($wynik);
				
				if (password_verify($podanaOdpowiedzPomocnicza, $user['odpowiedz']))
				{
					$_SESSION['etap'] = 3;
				}
				else
				{
					$error = "Odpowiedz jest nieprawidlowa!";
				}
			}
			else
			{
				$error = "Nie znaleziono użytkownika o Id Pracownika = '$idPracownika'";
			}
			
			mysqli_close($polaczenie);
		}
	}
	
	/* OBSŁUGA RESETOWANIA HASLA ETAP 3 - TWORZENIE NOWEGO HASLA */
	if ($_SESSION['etap'] == 3)
	{
		if (isset($_POST['noweHaslo']) && isset($_POST['powtorzHaslo']))
		{
			
			$podaneNoweHaslo = $_POST['noweHaslo'];
			$podanePowtorzoneNoweHaslo = $_POST['powtorzHaslo'];
			
			if ($podaneNoweHaslo == $podanePowtorzoneNoweHaslo)
			{
				$polaczenie = polaczZBaza();
				
				$idPracownika = $_SESSION['idPracownika'];
				$hasloHash = password_hash($podaneNoweHaslo, PASSWORD_DEFAULT);
					
				$sql = "UPDATE users SET Haslo = '$hasloHash' WHERE IdPracownika = '$idPracownika'";
				if (mysqli_query($polaczenie, $sql))
				{
					if (mysqli_affected_rows($polaczenie) === 1)
					{
						$_SESSION['etap'] = 4;
					}
					else
					{
						$error = "Nie udało się zmienić hasła.";
					}
				}
				else
				{
					$error = "Błąd zapisu hasła.";
				}
				
				mysqli_close($polaczenie);
			}
			else 
			{
				$error = "Podane hasła nie są identyczne!";
			}
		}
	}
	
	// czyszczenie sesji
	if (isset($_POST['zakonczReset'])) {
		$_SESSION = [];
		
		// upewniamy sie ze czyszczone sa rowniez ciasteczka
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 1, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		
		//niszczymy sesje
		session_destroy();
		
		header("Location: loginWindow.php");
		exit();
	}
?>	
		<!-- OKNO PRZYPOMNIENIA HASLA -->
		<div class="remindPasswordContainer">
			<div class="remindPasswordInputSection">
				
				<?php if ($error): ?>
				<div class="errorMessage">
					<?= htmlspecialchars($error) ?>
				</div>
				<?php endif ?>
				
				<?php if ($_SESSION['etap'] == 1): ?>
				<form action="" method="POST">
					
					<div class="inputField">
						<label for="loginInput">Jaki jest twój login? :</label>
						<input type="text" id="loginInput" name="loginInput" required placeholder="Wprowadź swój login">
					</div>
					
					<div class="inputField">
						<label for="idPracownikaInput">Jaki jest twój numer pracownika? :</label>
						<input type="text" id="idPracownikaInput" name="idPracownikaInput" required placeholder="Wprowadź numer pracownika">
					</div>
					
					<div class="inputField">
						<label for="dataUrodzeniaInput">Jaka jest twoja data urodzenia? :</label>
						<input type="date" id="dataUrodzeniaInput" name="dataUrodzeniaInput" required>
					</div>
				
						<button type="submit" class="customBtn" >Odzyskaj hasło</button>
				</form>
				
				<?php elseif ($_SESSION['etap'] == 2): ?>
				<form action="" method="POST">
				
					<div class="inputField">
						<label><?= $_SESSION['pytaniePomocnicze']; ?></label>
						<input type="password" name="odpowiedzPomocnicza" required>
					</div>
				
					<button type="submit" class="customBtn">Dalej</button>
				</form>
				
				<?php elseif ($_SESSION['etap'] == 3): ?>
				<form action="" method="POST">
				
					<div class="inputField">
						<label>Nowe hasło</label>
						<input type="password" name="noweHaslo" required>
					</div>
			
					<div class="inputField">
						<label>Powtórz hasło</label>
						<input type="password" name="powtorzHaslo" required>
					</div>
			
					<button class="customBtn" type="submit">Zapisz hasło</button>
				</form>
				
				<?php elseif ($_SESSION['etap'] == 4): ?>
				<form action="" method="POST">
				
					<p class="successMessage">Hasło zostało zmienione !</p>
					<button type="submit" class="customBtn"	name="zakonczReset">Wróć na strone logowania</button>
				</form>
				
				<?php endif; ?>
			
			</div>
		</div>
	
	</body>
</html>