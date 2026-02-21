<?php

	if (!isset($_SESSION['zalogowany']))
	{
		header("Location: loginWindow.php");
		exit();
	}
	include_once __DIR__ . '/helpers/dataBaseConnector.php';
	
	$error = null;
	$successMsg = null;
	
	
	/* POBIERAMY KLIENTOW Z BAZY DANYCH */
	$polaczenie = polaczZBaza();
	$klienci = [];
	
	if(!$polaczenie)
	{
		$error = "Nie można połączyć z bazą danych!";
	}
	$polaczenie->set_charset("utf8mb4");
	$sql = "SELECT Id, Nazwa, Adres FROM clients ORDER BY Nazwa ASC";
	$wynik = mysqli_query($polaczenie, $sql);
	
	if($wynik && mysqli_num_rows($wynik) > 0)
	{
		while($row = mysqli_fetch_assoc($wynik))
		{
			$klienci[] = $row;
		}
	}
	else 
	{
		$error = "Brak klientów w bazie danych!";
	}
	
	/* JESLI WYBRANO KLIENTA TO USTAWIAMY ADRES */
	$selectedClientId = $_POST['clientId'] ?? '';
	$deliveryAddress = '';
	if($selectedClientId)
	{
		foreach($klienci as $klient)
		{
			if($klient['Id'] == $selectedClientId) 
			{
				$deliveryAddress = $klient['Adres'];
				break;
			}
		}
	}
	
	// --- POBIERAMY OSTATNI NUMER ZAMOWIENIA Z BAZY DANYCH ---
	$sql = "SELECT OrderNumber FROM orders ORDER BY Id DESC LIMIT 1";
	$wynik = mysqli_query($polaczenie, $sql);
	$nextOrderNumber = "ORD-001"; // DOMYSLENIE JESLI BRAK ZAMOWIEN
	
	if($wynik && mysqli_num_rows($wynik) > 0){
		$row = mysqli_fetch_assoc($wynik);
		$lastOrder = $row['OrderNumber']; // np. "ORD-030"
		
		// WYCIAGAMY LICZBY Z KONCA NUMERU ZAMOWIENIA
		preg_match('/(\d+)$/', $lastOrder, $matches);
		$lastNumber = isset($matches[1]) ? intval($matches[1]) : 0;
		
		//ZWIEKSZA O 1
		$nextNumber = $lastNumber + 1;
		
		// TWORZYMY NOWU NUMER ZAMOWIENIA Z ZERAMI NA POCZATKU
		$nextOrderNumber = 'ORD-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
	}
	
	/* --- LOGIKA DODAWANIA NOWEGO ZAMOWIENIA --- */
	if (isset($_POST['orderNumber']) && $_POST['action'] == 'dodajZamowienie') 
	{
		$orderNumber = trim($_POST['orderNumber']);
		$clientId = trim($_POST['clientId']);
		$deliveryAddress = trim($_POST['deliveryAddress']);
		$orderDate = trim($_POST['orderDate']);
		$deliveryDate = trim($_POST['deliveryDate']);
		$notes = trim($_POST['notes']);
	 
		if (
			$orderNumber === '' || 
			$clientId === '' || 
			$deliveryAddress === '' || 
			$orderDate === '' || 
			$deliveryDate === ''
		) 
		{		
			$error = "Wszystkie pola muszą być wypełnione.";  
		}
		
		else 
		{
			/* --- SPRAWDZAMY CZY NUMER ZAMOWIENIA ISTNIEJE --- */
			$sql = "SELECT * FROM orders WHERE OrderNumber = '$orderNumber'";
			$wynik = mysqli_query($polaczenie, $sql);
			
			if (mysqli_num_rows($wynik) > 0)
			{
				$error = "Zamówienie o podanym numerze już istnieje!";
				
			}
			
			else
			{
				$sql = "INSERT INTO orders (
							OrderNumber,
							ClientId,
							DeliveryAddress,
							OrderDate,
							DeliveryDate,
							Notes
						)
						VALUES
						(
							'$orderNumber',
							'$clientId',
							'$deliveryAddress',
							'$orderDate',
							'$deliveryDate',
							'$notes'
						)";
				
				if (mysqli_query($polaczenie, $sql))
				{
					/* --- TWORZYMY AUTOMATYZACJE - DODAWANIE DO TABELI DELIVERIES --- */
					$lastOrderId = mysqli_insert_id($polaczenie); 		// pobieramy ID wlasnie dodanego  zamowienia
    
					$checkSql = "SELECT d.NumerZaladunku 
								FROM deliveries d 
								JOIN orders o ON d.OrderId = o.Id 
								WHERE o.ClientId = '$clientId' 
								AND o.DeliveryDate = '$deliveryDate' 
								LIMIT 1";
					
					$checkResult = mysqli_query($polaczenie, $checkSql);
					
					if (mysqli_num_rows($checkResult) > 0) {
						// ZNALEZIONO ZALADUNEK 
						$row = mysqli_fetch_assoc($checkResult);
						$numerZaladunku = $row['NumerZaladunku'];
					} else {
						// NIE MA ZALADUNKU
						$numerZaladunku = "ZAL-" . $clientId . "-" . date('dm', strtotime($deliveryDate));
					}
					
					// WSTAWIAMY REKORD DO TABELI DELIVERIES
					$sqlDelivery = "INSERT INTO deliveries (NumerZaladunku, OrderId, CzyZaladowane) 
									VALUES ('$numerZaladunku', $lastOrderId, 0)";
					
					mysqli_query($polaczenie, $sqlDelivery);
					$successMsg = "Zamówienie dodane i przypisane do załadunku: $numerZaladunku";
				}
				else
				{
					$error = "Błąd podczas zapisu zamówienia w bazie danych!";
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
	
		<form method="POST" action="orders.php">
			<input type="hidden" name="action" value="dodajZamowienie">
			
			<div class="inputField">
				<label>Numer zamówienia:</label>
				<input type="text" name="orderNumber" value="<?= htmlspecialchars($nextOrderNumber) ?>" required readonly>

			</div>
			
			<div class="inputField">
				<label>Nazwa klienta:</label>
				<select class="clientSelect" name="clientId" id="clientSelect" required onchange="updateAddress()">
				<option value="">-- Wybierz klienta --</option>
					<?php foreach($klienci as $klient): ?>
						<option value="<?= $klient['Id'] ?>" data-address="<?= htmlspecialchars($klient['Adres']) ?>">
							<?= htmlspecialchars($klient['Nazwa']) ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			
			<div class="inputField">
				<label>Adres dostawy:</label>
				<input type="text" name="deliveryAddress" value="<?= htmlspecialchars($deliveryAddress) ?>" required readonly>
			</div>
			
			<div class="inputField">
				<label>Data zamówienia:</label>
				<input type="date" name="orderDate" value="<?= date('Y-m-d') ?>" required>
			</div>
			
			<div class="inputField">
				<label>Data dostawy:</label>
				<input type="date" name="deliveryDate" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
			</div>
			
			<div class="inputField">
				<label>Uwagi:</label>
				<textarea name="notes" placeholder="Opcjonalne uwagi"></textarea>
			</div>
			
			<input type="submit" value="Dodaj zamówienie" class="customBtn">
			
		</form>
	</div>

</div>

<!-- 
	DODAJE MALY JS PONIEWAZ CHCE ZEBY POLE ADRES DOSTAWY UZEPELNIAL SIE W OPARCIU O WYBRANEGO KLIENTA
	DODANIE MALEGO SKRYPTU W JS JEST CHYBA NAJLATWIEJSZA OPCJA.
 -->
<script>
function updateAddress() {
    var select = document.getElementById('clientSelect'); 												// pobieramy element selkect z lista klientow
    var selectedOption = select.options[select.selectedIndex];											// pobieramy aktualnie wybranego klienta
    var addressInput = document.querySelector('input[name="deliveryAddress"]');							// pobieramy pole takstowe w ktorym wyswietlimy adres
    addressInput.value = selectedOption.getAttribute('data-address') || 'BRAK ADRESU W BAZIE DANYCH';	// ustawiamy wartosc , jesli nie ma adresu to dajemy defaultowa wartosc
}
</script>
