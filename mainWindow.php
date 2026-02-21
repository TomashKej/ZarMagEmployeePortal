<?php
/* 
	* require - jesli nie ma pliku to blad 
	* once - nie zaladuje drugi raz
	*__DIR__ - zapewnia poprawna sciezke nie zaleznie skad ladujemy
*/
require_once __DIR__ . '/helpers/dateHelper.php';
require_once __DIR__ . '/helpers/dataBaseConnector.php';

session_start();
if (!isset($_SESSION['zalogowany']))
{
	header("Location: loginWindow.php");
	exit();
}
		
require_once __DIR__ . '/template/header.php'; 

$polaczenie = polaczZBaza();

/* GOTOWE ZALADUNKI */
$dzisiaj = date('Y-m-d');
$dzisiejszeZaladunki = 0;
$pozostaleZaladunki = 0;

$sql = "SELECT COUNT(DISTINCT NumerZaladunku) as ile FROM deliveries d 
        JOIN orders o ON d.OrderId = o.Id 
        WHERE o.DeliveryDate = '$dzisiaj'";
$wynik = mysqli_query($polaczenie, $sql);
if($row = mysqli_fetch_assoc($wynik)) 
{
    $dzisiejszeZaladunki = $row['ile'];
}

/* NIE GOTOWE ZALADUNKI */
$sql = "SELECT COUNT(*) as ile FROM (
            SELECT d.NumerZaladunku FROM deliveries d 
            JOIN orders o ON d.OrderId = o.Id 
            WHERE o.DeliveryDate = '$dzisiaj' 
            GROUP BY d.NumerZaladunku 
            HAVING MIN(d.CzyZaladowane) = 0
        ) as podzapytanie";
$wynik = mysqli_query($polaczenie, $sql);

if($row = mysqli_fetch_assoc($wynik)) 
{
    $pozostaleZaladunki = $row['ile'];
}


?>
		
		<!-- SEKCJA GLOWNA STRONY GLOWNEJ -->
		<main class="mainContent">
		
			<!-- POWITANIE -->
			<section class="welcome">
				<h1>Witaj, <?php echo $_SESSION['imieUzytkownika']; ?>!</h1>
				<p>Dziś mamy <?php echo formatPolishDate(time()); ?>. Mam nadzieje że ten dzień będzie udany!</p>
			</section>
			
			<!-- DASHBOARD WYSWIETLAJACY INFORMACJE NA DANY DZIEN -->
			<div class="dashboard">
				<!-- AKTUALNA LICYBA WSZYSTKICH ZALADUNKOW NA DANY DZIEN -->
				<div class="dashboardInfoCard">
					<h3>Licza dzisiejszych załadunków</h3>
					<p class="dashboardStatNo"><?= $dzisiejszeZaladunki ?></p>
				</div>
				
				<!-- AKTUALNA LICZBA ZALADUNKOW W PROGRESIE -->
				<div class="dashboardInfoCard">
					<h3>Licza pozostałych załadunków</h3>
					<p class="dashboardStatNo" style="color: <?= ($pozostaleZaladunki > 0) ? '#C20000' : '#2ecc71' ?>;"><?= $pozostaleZaladunki ?></p>
				</div>
			</div>
			
		</main>
<?php
require_once __DIR__ . '/template/footer.php';
?>