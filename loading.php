<?php
session_start();
if (!isset($_SESSION['zalogowany']))
{
	header("Location: loginWindow.php");
	exit();
}

include_once __DIR__ . '/template/header.php';
include_once __DIR__ . '/helpers/dataBaseConnector.php';

$polaczenie = polaczZBaza();

$error = null;
$etap = 1;
$wybranyNrZaladunku = $_GET['nr'] ?? null;
$dataWybrana = $_GET['data'] ?? '';

// --- ETAP OBSŁUGI ZMIANY STATUSU POJEDYNCZEGO ZAMOWIENIA ---
if (isset($_POST['action']) && $_POST['action'] == 'zmienStatus') 
{
    $idDostawy = $_POST['deliveryId'];
    $nowyStatus = ($_POST['currentStatus'] == '1') ? 0 : 1;
    
    $sql = "UPDATE deliveries SET CzyZaladowane = '$nowyStatus' WHERE Id = '$idDostawy'";
    mysqli_query($polaczenie, $sql);
}

/* --- WYSWIETLAMY ZALADUNKI NA DANY DZIEN --- */
if (isset($_POST['action']) && $_POST['action'] == 'wyswietlZaladunki' || $dataWybrana != '') 
{
    $dataDostawy = $_POST['dataDostawy'] ?? $dataWybrana;
    $dataWybrana = $dataDostawy;
    
    if ($wybranyNrZaladunku == null) 
    {
        $etap = 2;
        $sql = "SELECT d.NumerZaladunku, c.Nazwa AS Klient, COUNT(o.Id) as LiczbaZamowien,
                       MIN(d.CzyZaladowane) as CzyWszystko
                FROM deliveries d
                JOIN orders o ON d.OrderId = o.Id
                JOIN clients c ON o.ClientId = c.Id
                WHERE o.DeliveryDate = '$dataDostawy'
                GROUP BY d.NumerZaladunku";
        $wynik = mysqli_query($polaczenie, $sql);
    }
}

// --- ETAP WYSWIETLANIA ZAMOWIEN DLA KONKRETNEGO ZAŁADUNKU ---
if ($wybranyNrZaladunku != null) 
{
    $etap = 3;
    $sql = "SELECT d.Id AS DeliveryId, d.CzyZaladowane, o.OrderNumber, o.DeliveryAddress, c.Nazwa as NazwaKlienta
            FROM deliveries d
            JOIN orders o ON d.OrderId = o.Id
            JOIN clients c ON o.ClientId = c.Id
            WHERE d.NumerZaladunku = '$wybranyNrZaladunku'";
    $wynik = mysqli_query($polaczenie, $sql);
}

?>

<main class="mainContent">
	
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
		
	<div class="datePickerContainer">
	
	<?php if($etap == 1): ?>
		<div class="datePickerModalWindow">
            <h3>Wybierz datę załadunków:</h3>
            <form action="" method="POST">
                <div class="inputField">
                    <input type="date" name="dataDostawy" required>
                </div>
                <div class="modalButtons">
                    <button type="submit" name="action" value="wyswietlZaladunki" class="customBtn">Wyświetl</button>
                </div>
            </form>
        </div>
	</div>
	
	<!-- ETAP WYSWIETLANIA LISTY ZALADUNKOW NA DANY DZIEN -->
	<?php elseif($etap == 2): ?>
        <h2 class="specialLoadingHeader">Trasy na dzień: <?= $dataWybrana ?></h2>
        <table class="customTable">
            <thead>
                <tr>
                    <th>Numer Załadunku</th>
                    <th>Główny Klient</th>
                    <th>Ilość zamówień</th>
                    <th>Opcje</th>
					<th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($wynik)): ?>
                <tr>
                    <td><strong><?= $row['NumerZaladunku'] ?></strong></td>
                    <td><?= $row['Klient'] ?></td>
                    <td><?= $row['LiczbaZamowien'] ?></td>
                    <td>
                        <a href="?nr=<?= urlencode($row['NumerZaladunku']) ?>&data=<?= $dataWybrana ?>" class="actionBtn">Otwórz listę</a>
                    </td>
					<td style="text-align: center;">
						<span class="statusDot <?= $row['CzyWszystko'] == 1 ? 'dotGreen' : 'dotRed' ?>"></span>
					</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="loading.php" class="customBtn secondary">Wróć do kalendarza</a>

    <?php elseif($etap == 3): ?>
        <h2 class="specialLoadingHeader">Załadunek: <?= htmlspecialchars($wybranyNrZaladunku) ?></h2>
        <table class="customTable">
            <thead>
                <tr>
                    <th>Nr Zamówienia</th>
                    <th>Adres Dostawy</th>
                    <th>Status</th>
                    <th>Akcja</th>
                </tr>
            </thead>
            <tbody>
			<!-- d to nasza zmienna tymczasowa ktora zapisuje kazdy kolejy wiersz jako tablice asocjacyjna-->
                <?php while($d = mysqli_fetch_assoc($wynik)): ?>
                <tr class="<?= $d['CzyZaladowane'] ? 'rowLoaded' : '' ?>">
                    <td><?= $d['OrderNumber'] ?></td>
                    <td><?= $d['DeliveryAddress'] ?></td>
                    <td><?= $d['CzyZaladowane'] ? '✅ ZAŁADOWANE' : '⏳ OCZEKUJE' ?></td>
                    <td>
                        <form action="" method="POST">
                            <input type="hidden" name="deliveryId" value="<?= $d['DeliveryId'] ?>">
                            <input type="hidden" name="currentStatus" value="<?= $d['CzyZaladowane'] ?>">
                            <button type="submit" name="action" value="zmienStatus" class="customBtn small">
                                <?= $d['CzyZaladowane'] ? 'Odznacz' : 'Zaladuj' ?>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="?data=<?= $dataWybrana ?>" class="customBtn secondary">Wróć do listy tras</a>
    <?php endif; ?>

</main>

<?php
include_once __DIR__ . '/template/footer.php';
?>