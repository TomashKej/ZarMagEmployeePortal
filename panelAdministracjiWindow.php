<?php
session_start();

if(!isset($_SESSION['zalogowany']))
{
	header("Location: loginWindow.php");
	exit();
}

require_once __DIR__ . '/template/header.php';
require_once __DIR__ . '/helpers/dataBaseConnector.php';

$error = null;
$action = $_POST['action'] ?? null;

$polaczenie = polaczZBaza();
if(!$polaczenie) 
{ 
	$error = 'Błąd połączenia z bazą danych !';
	exit(); 
}

/* --- WYSWIETLANIE UZYTKOWNIKOW PO AKCJACH () --- */
$users = [];
if($action == 'wyswietlUzytkownikow' || $action == 'potwierdzUsuniecie') 
{
    $sql = "SELECT * FROM Users";
    $wynik = mysqli_query($polaczenie, $sql);
	
    if (mysqli_num_rows($wynik) > 0) 
	{
        while($row = mysqli_fetch_assoc($wynik)) 
		{
            $users[] = $row;
        }
    }
	else
	{
		$error = 'Brak rekordów w bazie danych !';
	}
}

/* --- LOGIKA USUWANIA UZYTKOWNIKA Z BAZY DANYCH --- */
if($action == 'usunUzytkownika') 
{
	
    $idDoUsuniecia = $_POST['userId'] ?? null;
	
    if($idDoUsuniecia) 
	{
        
		$sql = "DELETE FROM Users WHERE IdPracownika = '$idDoUsuniecia'";
		$wynik = mysqli_query($polaczenie, $sql);
        
        if($wynik) 
		{
            $success = "Użytkownik został usunięty.";
        } 
		else 
		{
            $error = "Błąd podczas usuwania: ";
        }
    }
    // ODSWIERZAMY WIDOK LISTY
    $action = 'wyswietlUzytkownikow';
}

/* --- LOGIKA AKTUALIZACJI UZYTKOWNIKA --- */
if($action == 'zaktualizujUzytkownika') 
{
    $id = $_POST['userId'];
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $email = $_POST['email'];
    $stanowisko = $_POST['stanowisko'];
    
    $sql = "UPDATE Users SET 
            Imie='$imie', 
            Nazwisko='$nazwisko', 
            Email='$email', 
            Stanowisko='$stanowisko' 
            WHERE IdPracownika='$id'";
            
    $wynik = mysqli_query($polaczenie, $sql);
    
    if($wynik) 
	{
        $success = "Dane użytkownika zostały zaktualizowane.";
        $action = 'wyswietlUzytkownikow'; // Powrót do listy
    } 
	else 
	{
        $error = "Błąd edycji: " . mysqli_error($polaczenie);
    }
}

/* --- POBIERANIE DANYCH DO EDYCJI --- */
$userToEdit = null;
if($action == 'edytujUzytkownika') 
{
    $idDoEdycji = $_POST['userId'] ?? null;
    
    if($idDoEdycji) 
	{
        $sql = "SELECT * FROM Users WHERE IdPracownika = '$idDoEdycji'";
        $wynik = mysqli_query($polaczenie, $sql);
        
        if($wynik && mysqli_num_rows($wynik) > 0) 
		{
            $userToEdit = mysqli_fetch_assoc($wynik);
        } 
		else 
		{
            $error = "Nie znaleziono użytkownika o ID: $idDoEdycji";
            $action = 'wyswietlUzytkownikow';
        }
    }
}
?>

<div class="mainPageAdmin">
	
	<!-- MENU STRONY PANEL ADMINISTRACJI -->
		<div class="secondaryMenu">
			<!-- LISTA PRACOWNIKOW -->
			<div class="secondaryMenuItems">
				
				<?php if ($error): ?>
				<div class="errorMessage">
					<?= htmlspecialchars($error) ?>
				</div>
				<?php endif ?>
				
				<form action="" method="POST">
					
					<button type="submit" name="action" value="wyswietlUzytkownikow" class="navLink">Wyświetl użytkowników</button>
					<button type="submit" name="action" value="dodajUzytkownika" class="navLink">Rejestracja użytkownika</button>
					
				</form>
			</div>
		</div>
		
		<main class="mainContent">
			<div class="adminContentInner">
				
				<!-- OBSLUGA KOMUNIKATOW -->
				<?php if ($error): ?>
				<div class="errorMessage">
					<?= htmlspecialchars($error) ?>
				</div>
				<?php endif ?>
				
				<?php if (isset($success)): ?>
					<div class="successMessage">
					<?= htmlspecialchars($success) ?></div>
				<?php endif ?>
				
				<!-- WYSWIETLANIE WSZYSTKICH UZYTKOWNIKOW -->
				<?php if ($action == 'wyswietlUzytkownikow'): ?>
					
					<?php if(!empty($users)): ?>
						<table class="usersTable">
							<tr>
								<th>Id Pracownika</th>
								<th>Imię</th>
								<th>Nazwisko</th>
								<th>Płeć</th>
								<th>Adres Email</th>
								<th>Stanowisko</th>
								<th>Data Urodzenia</th>
								<th>Operacje</th>
							</tr>
							<?php foreach($users as $user): ?>
							<tr>
								<td><?= htmlspecialchars($user['IdPracownika']) ?></td>
								<td><?= htmlspecialchars($user['Imie']) ?></td>
								<td><?= htmlspecialchars($user['Nazwisko']) ?></td>
								<td><?= htmlspecialchars($user['Plec']) ?></td>
								<td><?= htmlspecialchars($user['Email']) ?></td>
								<td><?= htmlspecialchars($user['Stanowisko']) ?></td>
								<td><?= htmlspecialchars($user['DataUrodzenia']) ?></td>
								<td>
									<div style="display: flex; gap: 5px; justify-content: center;">
										
										<form action="" method="POST">
											<input type="hidden" name="userId" value="<?= $user['IdPracownika'] ?>">
											<button class="tabButton edit" type="submit" name="action" value="edytujUzytkownika">&#9998;</button>
										</form>
										
										<form action="" method="POST">
											<input type="hidden" name="userId" value="<?= $user['IdPracownika'] ?>">
											<button class="tabButton delete" type="submit" name="action" value="potwierdzUsuniecie">&#10006;</button>
										</form>
			
									</div>
								</td>
							</tr>
							<?php endforeach; ?>
						</table>
					<?php else: $error = 'Brak uzytkownikow w bazie danych!'?>
				<?php endif; ?>
				
				<!-- REJESTRACJA NOWEGO UZYTKOWNIKA -->
				<?php elseif($action == 'dodajUzytkownika'): ?>

					<?php require_once __DIR__ . '/rejestracja.php'; ?>

				<?php endif; ?>
				
				<!-- USUWANIE UZYTKOWNIKA -->
				<?php if ($action == 'potwierdzUsuniecie'): ?>
					<div class="deleteContainer">
						<div class="modalWindow">
							<h3>Ostrzeżenie</h3>
							<p>Czy na pewno chcesz usunąć użytkownika o ID: <strong><?= htmlspecialchars($_POST['userId']) ?></strong>?</p>
							<p>Tej operacji nie można cofnąć.</p>
							<div class="modalButtons">
								<form action="" method="POST">
									<input type="hidden" name="userId" value="<?= htmlspecialchars($_POST['userId']) ?>">
									<button type="submit" name="action" value="usunUzytkownika" class="customBtn danger">Usuń</button>
								</form>
								<form action="" method="POST">
									<button type="submit" name="action" value="wyswietlUzytkownikow" class="customBtn secondary">Anuluj</button>
								</form>
							</div>
						</div>
					</div>
					
				<!-- EDYTOWANIE UZYTKOWNIKA -->	
				<?php elseif($action == 'edytujUzytkownika' && $userToEdit): ?>

					<div class="registrationContainer">
						<div class="registrationInputSection">
							<h2>Edycja Użytkownika #<?= htmlspecialchars($userToEdit['IdPracownika']) ?></h2>
							<form action="" method="POST">
								<input type="hidden" name="userId" value="<?= htmlspecialchars($userToEdit['IdPracownika']) ?>">
								
								<div class="inputField">
									<label>Imię:</label>
									<input type="text" name="imie" value="<?= htmlspecialchars($userToEdit['Imie']) ?>" required>
								</div>
								
								<div class="inputField">
									<label>Nazwisko:</label>
									<input type="text" name="nazwisko" value="<?= htmlspecialchars($userToEdit['Nazwisko']) ?>" required>
								</div>
			
								<div class="inputField">
									<label>Email:</label>
									<input type="email" name="email" value="<?= htmlspecialchars($userToEdit['Email']) ?>" required>
								</div>
			
								<div class="inputField">
									<label>Stanowisko:</label>
									<input type="text" name="stanowisko" value="<?= htmlspecialchars($userToEdit['Stanowisko']) ?>" required>
								</div>
			
								<div class="modalButtons">
									<button type="submit" name="action" value="zaktualizujUzytkownika" class="customBtn">Zapisz zmiany</button>
									<button type="submit" name="action" value="wyswietlUzytkownikow" class="customBtn secondary" style="background-color: #666; border-color: #555;">Anuluj</button>
								</div>
							</form>
						</div>
					</div>
				<?php endif; ?>	
				
			</div>
		</main>
</div>

<?php
require_once __DIR__ . '/template/footer.php';
?>