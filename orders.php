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

/* --- WYSWIETLANIE ZAMOWIEN PO AKCJACH --- */
$orders = [];
$filtrDaty = $_POST['dataFiltru'] ?? date('Y-m-d');

if($action == 'wyswietlZamowienia' || $action == 'potwierdzUsuniecieZamowienia') 
{
    $sql = "SELECT * FROM orders WHERE DeliveryDate = '$filtrDaty' ORDER BY OrderNumber ASC";
    $wynik = mysqli_query($polaczenie, $sql);
	
    if (mysqli_num_rows($wynik) > 0) 
	{
        while($row = mysqli_fetch_assoc($wynik)) 
		{
            $orders[] = $row;
        }
    }
	else
	{
		$error = 'Brak zamówień w bazie danych !';
	}
}

/* --- LOGIKA USUWANIA ZAMOWIEN --- */
if($action == 'usunZamowienie') 
{
    $idDoUsuniecia = $_POST['orderId'] ?? null;
	
    if($idDoUsuniecia) 
	{
        $sql = "DELETE FROM orders WHERE Id = '$idDoUsuniecia'";
		$wynik = mysqli_query($polaczenie, $sql);
        
        if($wynik) 
		{
            $success = "Zamówienie zostało usunięte.";
        } 
		else 
		{
            $error = "Błąd podczas usuwania zamówienia.";
        }
    }
    $action = 'wyswietlZamowienia';
}

/* --- LOGIKA EDYTOWANIA ZAMOWIENIA --- */
if($action == 'zaktualizujZamowienie') 
{
	$id = $_POST['orderId'];
    $orderNumber = $_POST['orderNumber'];
    $deliveryAddress = $_POST['deliveryAddress'];
    $orderDate = $_POST['orderDate'];
    $deliveryDate = $_POST['deliveryDate'];
    $notes = $_POST['notes'];
    
    $sql = "UPDATE orders SET 
            OrderNumber='$orderNumber',
            DeliveryAddress='$deliveryAddress',
            OrderDate='$orderDate',
            DeliveryDate='$deliveryDate',
            Notes='$notes'
            WHERE Id ='$id'";
            
    $wynik = mysqli_query($polaczenie, $sql);
    
    if($wynik) 
	{
        $success = "Zamówienie zostało zaktualizowane.";
        $action = 'wyswietlZamowienia';
    } 
	else 
	{
        $error = "Błąd edycji zamówienia.";
    }
}

/* --- POBIERANIE DANYCH DO EDYCJI --- */
$orderToEdit = null;
if($action == 'edytujZamowienie') 
{
    $idDoEdycji = $_POST['orderId'] ?? null;
    
    if($idDoEdycji) 
	{
        $sql = "SELECT * FROM orders WHERE Id = '$idDoEdycji'";
        $wynik = mysqli_query($polaczenie, $sql);
        
        if($wynik && mysqli_num_rows($wynik) > 0) 
		{
            $orderToEdit = mysqli_fetch_assoc($wynik);
        } 
		else 
		{
            $error = "Nie znaleziono zamówienia o ID: $idDoEdycji";
            $action = 'wyswietlZamowienia';
        }
    }
}
?>

<div class="mainPageOrders">

	<div class="secondaryMenu">
		<div class="secondaryMenuItems">

			<form action="" method="POST">
				<button type="submit" name="action" value="wyswietlZamowienia" class="navLink">Wyświetl zamówienia</button>
				<button type="submit" name="action" value="dodajZamowienie" class="navLink">Dodaj zamówienie</button>
			</form>

		</div>
	</div>

	<main class="mainContent">
		<div class="ordersContentInner">

			<?php if ($error): ?>
				<div class="errorMessage"><?= htmlspecialchars($error) ?></div>
			<?php endif ?>

			<?php if (isset($success)): ?>
				<div class="successMessage"><?= htmlspecialchars($success) ?></div>
			<?php endif ?>

			<!-- TABELA ZAMOWIEN -->
			<?php if ($action == 'wyswietlZamowienia'): ?>
			
				<div class="filterSection">
					<form action="" method="POST" class="inlineForm">
						<input type="hidden" name="action" value="wyswietlZamowienia">
						<label>Data dostawy:</label>
						<input type="date" name="dataFiltru" value="<?= $filtrDaty ?>">
						<button type="submit" name="filtrujDate" class="actionBtn">Filtruj</button>
					</form>
				</div>
				
				<?php if(!empty($orders)): ?>
					<table class="ordersTable">
						<tr>
							<th>Id</th>
							<th>Numer zamówienia</th>
							<th>Adres dostawy</th>
							<th>Data zamówienia</th>
							<th>Data dostawy</th>
							<th>Operacje</th>
						</tr>
						<?php foreach($orders as $order): ?>
						<tr>
							<td><?= htmlspecialchars($order['Id']) ?></td>
							<td><?= htmlspecialchars($order['OrderNumber']) ?></td>
							<td><?= htmlspecialchars($order['DeliveryAddress']) ?></td>
							<td><?= htmlspecialchars($order['OrderDate']) ?></td>
							<td><?= htmlspecialchars($order['DeliveryDate']) ?></td>
							<td>
								<form action="" method="POST" style="display:inline;">
									<input type="hidden" name="orderId" value="<?= $order['Id'] ?>">
									<button type="submit" name="action" value="edytujZamowienie" class="tabButton edit">&#9998;</button>
								</form>
								<form action="" method="POST" style="display:inline;">
									<input type="hidden" name="orderId" value="<?= $order['Id'] ?>">
									<input type="hidden" name="orderNumber" value="<?= $order['OrderNumber'] ?>">
									<button type="submit" name="action" value="potwierdzUsuniecieZamowienia" class="tabButton delete">&#10006;</button>
								</form>
							</td>
						</tr>
						<?php endforeach; ?>
					</table>
				<?php endif; ?>
				<!-- DODAWANIE NOWEGO KLIENTA -->
				<?php elseif($action == 'dodajZamowienie'): ?>
	
					<?php require_once __DIR__ . '/addOrders.php'; ?>
	
				<?php endif; ?>
			
			<!--USUWANIE ZAMOWIEN -->
			<?php if ($action == 'potwierdzUsuniecieZamowienia'): ?>
				<div class="deleteContainer">
					<div class="modalWindow">
						<h3>Ostrzeżenie</h3>
						<p>Czy na pewno chcesz usunąć zamówienie <strong>#<?= htmlspecialchars($_POST['orderNumber']) ?></strong>?</p>
						<div class="modalButtons">
							<form action="" method="POST">
								<input type="hidden" name="orderId" value="<?= htmlspecialchars($_POST['orderId']) ?>">
								<button type="submit" name="action" value="usunZamowienie" class="customBtn danger">Usuń</button>
							</form>
							<form action="" method="POST">
								<button type="submit" name="action" value="wyswietlZamowienia" class="customBtn secondary">Anuluj</button>
							</form>
						</div>
					</div>
				</div>
				
			<!-- EDYCJA ZAMÓWIENIA -->
			<?php elseif ($action == 'edytujZamowienie' && $orderToEdit): ?>
			
				<div class="registrationContainer">
					<div class="registrationInputSection">
			
						<h2>Edycja zamówienia ID: <?= htmlspecialchars($orderToEdit['Id']) ?></h2>
			
						<form action="" method="POST">
							<input type="hidden" name="orderId" value="<?= htmlspecialchars($orderToEdit['Id']) ?>">
			
							<div class="inputField">
								<label>Numer zamówienia:</label>
								<input type="text" name="orderNumber"
									value="<?= htmlspecialchars($orderToEdit['OrderNumber']) ?>" required>
							</div>
			
							<div class="inputField">
								<label>Adres dostawy:</label>
								<input type="text" name="deliveryAddress"
									value="<?= htmlspecialchars($orderToEdit['DeliveryAddress']) ?>" required>
							</div>
			
							<div class="inputField">
								<label>Data zamówienia:</label>
								<input type="date" name="orderDate"
									value="<?= htmlspecialchars($orderToEdit['OrderDate']) ?>" required>
							</div>
			
							<div class="inputField">
								<label>Data dostawy:</label>
								<input type="date" name="deliveryDate"
									value="<?= htmlspecialchars($orderToEdit['DeliveryDate']) ?>" required>
							</div>
			
							<div class="inputField">
								<label>Uwagi:</label>
								<textarea name="notes"><?= htmlspecialchars($orderToEdit['Notes']) ?></textarea>
							</div>
			
							<div class="modalButtons">
								<button type="submit"
										name="action"
										value="zaktualizujZamowienie"
										class="customBtn">
									Zapisz zmiany
								</button>
			
								<button type="submit"
										name="action"
										value="wyswietlZamowienia"
										class="customBtn secondary">
									Anuluj
								</button>
							</div>
			
						</form>
					</div>
				</div>
			<?php endif; ?>
			
		</div>
	</main>

</div>

<?php require_once __DIR__ . '/template/footer.php'; ?>
