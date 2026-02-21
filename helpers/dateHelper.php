<?php
/* ---    PONIZSZY KOD JEST FUNKCJA OBSLUGUJACA WYSWIETLANIE AKTUALNEJ DATY
   W JEZYKU POLSKIM. IMPLEMENTUJE KLASE PHP, KTORA FORMATUJE DATY I GODZINY 
   ORAZ OBSLUGUJE JEZYKI I KRAJE, KORZYSTAJAC Z BIBLIOTEKI ICU 			--- */

	function formatPolishDate($timestamp) 
	{
		// dzieki static zmienna zachowuje wartosc miedzy wywolaniami funkcji
		static $formatter = null;
		
		if ($formatter === null) 
		{
			// IntlDateFormatter - klasa PHP ktora formatuje daty, uzywa lokacji , odmienia msc i dni oraz uzywa stref czasowych
			$formatter = new IntlDateFormatter('pl_PL', IntlDateFormatter::FULL, IntlDateFormatter::SHORT, 'Europe/Warsaw');
		}
		
		// -> to operator dostepu do obiektu. czyli mozna to czytac tak : "na obiekcie x wywolaj metode y"
		return $formatter->format($timestamp);
	}
?>