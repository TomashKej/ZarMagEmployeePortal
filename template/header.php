<!DOCTYPE html>
<html lang="pl">
	
	<head>
		<meta charset="utf-8">
		<title>ZarMag - Portal</title>
		<link href="style.css" rel="stylesheet">
		<!-- PONIZSZA BIBLIOTEKA POZWALA DODAWAC IKONY ZA POMOCA ZNACZNIKOW TEKSTOWYCH -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> 
	</head>
	
	<body class="mainPage">
	
		<!-- SEKCJA NAGŁÓWKA -->
		<header class="topPageNavBar">
		
			<!-- LEWA STRONA NAGŁÓWKA (LOGO) -->
			<div class="navLeftSide">
				<img src="resources/companyLogoBarVersionSmallerV2.png" class="navLogo" alt="Logo">
			</div>
			
			<!-- SRODKOWA CZESC NAGŁÓWKA (HYPERLINKS) -->
			<nav class="navCenter">
				<?php $page = basename($_SERVER['PHP_SELF']);?>
			
				<a href="mainWindow.php" class="navLink <?php echo ($page == 'mainWindow.php') ? 'active' :''; ?>">Strona główna</a>
				<a href="loading.php" class="navLink <?php echo ($page == 'loading.php') ? 'active' :''; ?>">Załadunki</a>
				<a href="orders.php" class="navLink <?php echo ($page == 'orders.php') ? 'active' :''; ?>">Zamówienia</a>
				<a href="clients.php" class="navLink <?php echo ($page == 'clients.php') ? 'active' :''; ?>">Klienci</a>
				<a href="panelAdministracjiWindow.php" class="navLink <?php echo ($page == 'panelAdministracjiWindow.php') ? 'active' :''; ?>">Panel Administracji</a>
			</nav>
			
			<!-- PRAWA CZESC NAGŁOWKA (USTAWIENIA, PROFIL ...)-->
			<div class="navRightSide">
				<div class="navSettings">
					<button class="dropBtn">
						<i class="fa-solid fa-gear"></i> 
						<span><?php echo $_SESSION['userLogin']; ?></span> 
					</button>
					<div class="dropContent">
						<a href="profile.php"><i class="fa-solid fa-user"></i>Profil</a>
						<!-- ODZIELAMY LOGIKE WYLOGOWYWANIA DO OSOBNEJ STRONY ZEBY NIE ZASMIECAC KODU I ZEBY MIEC WIEKSZA KONTROLE NAD CZYSZCZENIEM SESJI -->
						<a href="helpers/logout.php" class="logoutLink"><i class="fa-solid fa-power-off"></i>Wyloguj</a>
					</div>
				</div>
			</div>
		</header>