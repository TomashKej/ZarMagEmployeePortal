<?php 
session_start();
require_once __DIR__ . "/template/header.php";
require_once __DIR__ . "/helpers/dataBaseConnector.php";

if (!isset($_SESSION['zalogowany']))
{
	header("Location: loginWindow.php");
	exit();
}
$error = null;
$successMsg = null;
$currentUser = $_SESSION['userId'];
$polaczenie = polaczZBaza();

/* --- AKTUALIZACJA DANYCH --- */
$action = $_POST['action'] ?? null;
if ($action == 'zaktualizujDane')
{
    $email = $_POST['email'];
    $plec = $_POST['gender'];
    $pytanie = $_POST['nowePytaniePomocnicze'];
    $noweHaslo = $_POST['noweHaslo'];
    $nowaOdpowiedz = $_POST['nowaOdpowiedz'];
	
    $sql = "UPDATE users SET 
            Email = '$email', 
            Plec = '$plec', 
            PytaniePomocnicze = '$pytanie' 
            WHERE IdPracownika = '$currentUser'";
            
    $wynik = mysqli_query($polaczenie, $sql);
    
    if ($wynik) 
    {
        $successMsg = "Dane profilu zostały zaktualizowane.";

        /* PRZYPADEK WPISANIA HASLA */
        if ($noweHaslo != "") 
        {
            $hasloHash = password_hash($noweHaslo, PASSWORD_DEFAULT);
            $sqlHaslo = "UPDATE users SET haslo = '$hasloHash' WHERE idPracownika = '$currentUser'";
            mysqli_query($polaczenie, $sqlHaslo);
        }

        /* PRZYPADEK WPISANIA NOWEJ ODPOWIEDZI */
        if ($nowaOdpowiedz != "") 
        {
            $odpowiedzHash = password_hash($nowaOdpowiedz, PASSWORD_DEFAULT);
            $sqlOdpowiedz = "UPDATE users SET odpowiedz = '$odpowiedzHash' WHERE idPracownika = '$currentUser'";
            mysqli_query($polaczenie, $sqlOdpowiedz);
        }
    } 
    else 
    {
        $error = "Błąd edycji danych ";
    }
}
$sql = "SELECT * FROM users WHERE IdPracownika = '$currentUser'";
$wynik = mysqli_query($polaczenie, $sql);
$user = mysqli_fetch_assoc($wynik);

$initials = substr($user['Imie'], 0, 1) . substr($user['Nazwisko'], 0, 1);
$profilePic = "https://ui-avatars.com/api/?name=" . urlencode($user['Imie'] . "+" . $user['Nazwisko']) . "&background=764ba2&color=fff&size=128";
?>

<main class="mainContent">

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
		
    <div class="userProfileCard">
        
		<div class="userProfileLeft">
            <div class="profileAvatarContainer">
                <img src="<?php echo $profilePic; ?>" alt="User Avatar">
            </div>
            <h2 class="profileDisplayName"><?php echo $user['Imie'] . " " . $user['Nazwisko']; ?></h2>
            <p class="profilePositionTag"><?php echo $user['Stanowisko']; ?></p>
        </div>

        <div class="userProfileRight">
            <form action="profile.php" method="POST">
				<input type="hidden" name="action" value="zaktualizujDane">
                <h3 class="formSectionTitle">Dane podstawowe</h3>
                <div class="inputRow">
                    <div class="inputField">
                        <label>Imię</label>
                        <input type="text" name="imie" value="<?= htmlspecialchars($user['Imie']) ?>" readonly>
                    </div>
                    <div class="inputField">
                        <label>Nazwisko</label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($user['Nazwisko']) ?>" readonly>
                    </div>
                </div>

                <div class="inputRow">
				
                    <div class="inputField">
                        <label>Płeć</label>
                        <select name="gender" class="clientSelect">
                            <option value="male" <?php echo ($user['Plec'] == 'M') ? 'selected' : ''; ?>>Mężczyzna</option>
                            <option value="female" <?php echo ($user['Plec'] == 'K') ? 'selected' : ''; ?>>Kobieta</option>
                        </select>
                    </div>
					
                    <div class="inputField">
                        <label>Adres E-mail</label>
                        <input type="email" name="email" value="<?php echo $user['Email']; ?>">
                    </div>
					
                </div>
				<p> * By edytować imię lub nazwisko - skontaktuj się z administracją</p>
                <hr class="formDivider">
				
                <h3 class="formSectionTitle">Bezpieczeństwo</h3>

                <div class="inputField">
                    <label>Nowe hasło (zostaw puste, jeśli bez zmian)</label>
                    <input type="password" name="noweHaslo" placeholder="Wpisz nowe hasło">
                </div>

                <div class="inputRow">
                    <div class="inputField">
                        <label for="pytaniePomocnicze">Pytanie pomocnicze:</label>
						<textarea name="nowePytaniePomocnicze" required ><?php echo $user['PytaniePomocnicze'];?></textarea>
                    </div>
                    <div class="inputField">
                        <label>Odpowiedź na pytanie <br>(zostaw puste, jeśli bez zmian) </label>
                        <input type="password" name="nowaOdpowiedz" placeholder="Wpisz nową odpowiedź">
                    </div>
                </div>
                <button type="submit" class="customBtn">Zapisz ustawienia profilu</button>
				
            </form>
        </div>
    </div>
</main>

<?php 
require_once __DIR__ . "/template/footer.php";
?>