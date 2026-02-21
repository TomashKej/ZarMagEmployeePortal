<?php
/* --- 
PONIZSZY KOD OBSLUGUJE PROCES WYLOGOWYWANIA 
ODZIELILEM GO OD STRONY GLOWNEJ ZEBY ZAPOBIEC 
BALAGANU W KODZIE. FUNKCJA UPEWNIA SIE ZE 
COOKIES JAK I SESJA ZOSTAJA WYCZYSZCZONE
--- */
	
session_start();

// czyscimy wszystkie zmienne z sesji
$_SESSION = [];

// upewniamy sie ze czyszczone sa rowniez ciasteczka
$params = session_get_cookie_params();
setcookie(session_name(), '', time() - 1, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);

//niszczymy sesje
session_destroy();

header("Location: ../loginWindow.php");
exit();

?>