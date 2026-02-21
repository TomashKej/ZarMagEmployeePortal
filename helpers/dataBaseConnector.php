<?php
/* --- POŁĄCZENIE Z BAZĄ --- */

function polaczZBaza() {
    $polaczenie = mysqli_connect("localhost", "root", "", "zarmagdb");
    if (!$polaczenie) {
        exit("Błąd połączenia z bazą danych");
    }
    return $polaczenie;
}
?>