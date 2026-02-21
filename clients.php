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

/* --- WYSWIETLANIE KLIENOW PO AKCJACH () --- */
$clients = [];
$szukanaNazwa = $_POST['szukajNazwy'] ?? ''; 

// w razie klikniecia resetuj - resetujemy
if(isset($_POST['resetuj'])) {
    $szukanaNazwa = '';
}

if($action == 'wyswietlKlientow' || $action == 'potwierdzUsuniecie' || isset($_POST['szukajAction']) ) 
{
    $sql = "SELECT * FROM clients";
	
	if($szukanaNazwa != '') 
	{
        $sql = "SELECT * FROM clients WHERE Nazwa LIKE '%$szukanaNazwa%'";
    }
	
	$sql .= " ORDER BY Id ASC";
	
    $wynik = mysqli_query($polaczenie, $sql);
	
    if (mysqli_num_rows($wynik) > 0) 
	{
        while($row = mysqli_fetch_assoc($wynik)) 
		{
            $clients[] = $row;
        }
    }
	else
	{
		$error = 'Brak rekordów w bazie danych !';
	}
}

/* --- LOGIKA USUWANIA KLIENTOW Z BAZY DANYCH --- */
if($action == 'usunKlienta') 
{
	
    $idDoUsuniecia = $_POST['clientId'] ?? null;
	
    if($idDoUsuniecia) 
	{
        
		$sql = "DELETE FROM clients WHERE Id = '$idDoUsuniecia'";
		$wynik = mysqli_query($polaczenie, $sql);
        
        if($wynik) 
		{
            $success = "Klient został usunięty.";
        } 
		else 
		{
            $error = "Błąd podczas usuwania: ";
        }
    }
    // ODSWIERZAMY WIDOK LISTY
    $action = 'wyswietlKlientow';
}

/* --- LOGIKA EDYTOWANIA KLIENTA --- */
if($action == 'zaktualizujKlienta') 
{
	$id = $_POST['clientId'];
    $nazwa = $_POST['nazwa'];
    $adres = $_POST['adres'];
    $nrTelefonu = $_POST['nrTelefonu'];
    $email = $_POST['email'];
    
    $sql = "UPDATE clients SET 
            Nazwa='$nazwa', 
            Adres='$adres', 
            NrTelefonu='$nrTelefonu', 
            Email='$email' 
            WHERE Id ='$id'";
            
    $wynik = mysqli_query($polaczenie, $sql);
    
    if($wynik) 
	{
        $success = "Dane klienta zostały zaktualizowane.";
        $action = 'wyswietlKlientow'; // Powrót do listy
    } 
	else 
	{
        $error = "Błąd edycji: ";
    }
}

/* --- POBIERANIE DANYCH DO EDYCJI --- */
$clientToEdit = null;
if($action == 'edytujKlienta') 
{
    $idDoEdycji = $_POST['clientId'] ?? null;
    
    if($idDoEdycji) 
	{
        $sql = "SELECT * FROM clients WHERE Id = '$idDoEdycji'";
        $wynik = mysqli_query($polaczenie, $sql);
        
        if($wynik && mysqli_num_rows($wynik) > 0) 
		{
            $clientToEdit = mysqli_fetch_assoc($wynik);
        } 
		else 
		{
            $error = "Nie znaleziono klienta o ID: $idDoEdycji";
            $action = 'wyswietlKlientow';
        }
    }
}
?>

	<div class="mainPageClient">
	
		<!-- MENU STRONY PANEL KLENCI -->
		<div class="secondaryMenu">
			<!-- LISTA KLIENTOW -->
			<div class="secondaryMenuItems">
			
				<?php if ($error): ?>
				<div class="errorMessage">
					<?= htmlspecialchars($error) ?>
				</div>
				<?php endif ?>
				
				<form action="" method="POST">
					
					<button type="submit" name="action" value="wyswietlKlientow" class="navLink">Wyświetl klientów</button>
					<button type="submit" name="action" value="dodajKlienta" class="navLink">Dodaj klienta</button>
					
				</form>
			</div>
		</div>
		
		<main class="mainContent">
			<div class="clientContentInner">
				
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
				
				<!-- WYSWIETLANIE WSZYSTKICH KLIENTOW -->
				<?php if ($action == 'wyswietlKlientow'): ?>
					
					<div class="filterSection">
						<form action="" method="POST" class="inlineForm">
							<input type="hidden" name="action" value="wyswietlKlientow">
							
							<div class="filterItem">
								<label>Nazwa klienta:</label>
								<input type="text" name="szukajNazwy" value="<?= $szukanaNazwa ?>" placeholder="Wpisz szukaną nazwę...">
							</div>
				
							<button type="submit" name="szukajAction" class="actionBtn">Szukaj</button>
							<button type="submit" name="resetuj" class="actionBtn">Pokaż wszystkich</button>
						</form>
					</div>
					
					<?php if(!empty($clients)): ?>
						<table class="clientTable">
							<tr>
								<th>Id</th>
								<th>Nazwa</th>
								<th>Adres</th>
								<th>Numer telefonu</th>
								<th>Adres Email</th>
								<th>Operacje</th>
							</tr>
							<?php foreach($clients as $client): ?>
							<tr>
								<td><?= htmlspecialchars($client['Id']) ?></td>
								<td><?= htmlspecialchars($client['Nazwa']) ?></td>
								<td><?= htmlspecialchars($client['Adres']) ?></td>
								<td><?= htmlspecialchars($client['NrTelefonu']) ?></td>
								<td><?= htmlspecialchars($client['Email']) ?></td>
								<td>
									<div style="display: flex; gap: 5px; justify-content: center;">
										
										<form action="" method="POST">
											<input type="hidden" name="clientId" value="<?= $client['Id'] ?>">
											<button class="tabButton edit" type="submit" name="action" value="edytujKlienta">&#9998;</button>
										</form>
										
										<form action="" method="POST">
											<input type="hidden" name="clientId" value="<?= $client['Id'] ?>">
											<button class="tabButton delete" type="submit" name="action" value="potwierdzUsuniecie">&#10006;</button>
										</form>
			
									</div>
								</td>
							</tr>
							<?php endforeach; ?>
						</table>
					<?php else: $error = 'Brak klientów w bazie danych!'?>
				<?php endif; ?>
				
				<!-- DODAWANIE NOWEGO KLIENTA -->
				<?php elseif($action == 'dodajKlienta'): ?>
	
					<?php require_once __DIR__ . '/addClient.php'; ?>
	
				<?php endif; ?>
				
				<!-- USUWANIE UZYTKOWNIKA -->
				<?php if ($action == 'potwierdzUsuniecie'): ?>
					<div class="deleteContainer">
						<div class="modalWindow">
							<h3>Ostrzeżenie</h3>
							<p>Czy na pewno chcesz usunąć klienta: <strong><?= htmlspecialchars($_POST['clientId']) ?></strong>?</p>
							<p>Tej operacji nie można cofnąć.</p>
							<div class="modalButtons">
								<form action="" method="POST">
									<input type="hidden" name="clientId" value="<?= htmlspecialchars($_POST['clientId']) ?>">
									<button type="submit" name="action" value="usunKlienta" class="customBtn danger">Usuń</button>
								</form>
								<form action="" method="POST">
									<button type="submit" name="action" value="wyswietlKlientow" class="customBtn secondary">Anuluj</button>
								</form>
							</div>
						</div>
					</div>
					
				<!-- EDYCJA KLIENTA -->	
				<?php elseif($action == 'edytujKlienta' && $clientToEdit): ?>
	
					<div class="registrationContainer">
						<div class="registrationInputSection">
							<h2>Edycja Klienta o numerze Id:<?= htmlspecialchars($clientToEdit['Id']) ?></h2>
							<form action="" method="POST">
								<input type="hidden" name="clientId" value="<?= htmlspecialchars($clientToEdit['Id']) ?>">
								
								<div class="inputField">
									<label>Nazwa:</label>
									<input type="text" name="nazwa" value="<?= htmlspecialchars($clientToEdit['Nazwa']) ?>" required>
								</div>
								
								<div class="inputField">
									<label>Adres:</label>
									<input type="text" name="adres" value="<?= htmlspecialchars($clientToEdit['Adres']) ?>" required>
								</div>
			
								<div class="inputField">
									<label>Numer telefonu:</label>
									<input type="text" name="nrTelefonu" value="<?= htmlspecialchars($clientToEdit['NrTelefonu']) ?>" required>
								</div>
			
								<div class="inputField">
									<label>Email:</label>
									<input type="email" name="email" value="<?= htmlspecialchars($clientToEdit['Email']) ?>" required>
								</div>
			
								<div class="modalButtons">
									<button type="submit" name="action" value="zaktualizujKlienta" class="customBtn">Zapisz zmiany</button>
									<button type="submit" name="action" value="wyswietlKlientow" class="customBtn secondary" style="background-color: #666; border-color: #555;">Anuluj</button>
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