<?php

namespace Bitrix\Mymodulpars;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
use Bitrix\Highloadblock as HL;

class Main
{
	public function getUsdValue()
	{
		$date = date("Y-m-d H:i:s");
		$url = 'http://www.finmarket.ru/currency/USD/';
		$resultTable = Table::getTableLine();

		// Проверяем есть ли записи в таблице
			if ($resultTable['ID']>0){
				$hourdiff = round((strtotime($date) - strtotime($resultTable['DATE']))/3600, 1);
				
				if ($hourdiff>2){
		//Проверяем актуальность данных, если запись в таблице сделана более 2х часов назад, обновляем
					$result = Parser::getCurrencyValue($url, 'https://yandex.ru', $date, 'USD');
					Table::updateTable($resultTable['ID'], $result);
					echo $result['USD']['VALUE'];
				}else{
					echo $resultTable['VALUE'];
				}
			}else{
		// Если таблица есть, но в ней нет записей (удалены)
			    $result = PARSER::getCurrencyValue($url, 'https://yandex.ru', $date, 'USD');
				Table::addToTable($result);
				echo $result['USD']['VALUE'];
			}
	}

}