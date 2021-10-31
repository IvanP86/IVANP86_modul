<?php

namespace Bitrix\Mymodulpars;

use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
use Bitrix\Highloadblock as HL;

class Table
{
	public function updateTable($lineId, $arrayTable, $id = 0 , $currency = 'USD')
	{
		if ($id == 0)
		{
			$id = self::getIdtable();
		}
			$entity_data_class = self::GetEntityDataClass($id);
			$resultUpdate = $entity_data_class::update($lineId, array(
					  'UF_CURRENCY'      => $currency,
					  'UF_ADDED'         => $arrayTable['USD']['DATE'],
					  'UF_VALUE'         => $arrayTable['USD']['VALUE'],
					  
					));
	}

	public function getTableLine($currencyName = 'USD')
	{
			$arFilter = array("UF_CURRENCY" => $currencyName);
			$arSelect = array('*');
			$id = self::getIdtable();
			$entity_data_class = self::GetEntityDataClass($id);
			$arData = $entity_data_class::getList(array(
					"select" => $arSelect,
					"filter" => $arFilter
			));
			$arData = new \CDBResult($arData, "table_currency");

			while($arResult = $arData->Fetch()){
				/*$idElement = $arResult["ID"];
				$dateOld = $arResult['UF_ADDED'];
				$usdOld = $arResult['UF_VALUE'];*/
				$result['ID'] = $arResult["ID"];
				$result['DATE'] = $arResult['UF_ADDED'];
				$result['VALUE'] = $arResult['UF_VALUE'];

			}
/*			if (!$arResult["ID"] > 0){
				return false;
			}*/
			// print_r($result);
			return $result;
	}

	public function getIdtable($tableName = 'TableOfCurrency')
	{
		$hlblock = HL\HighloadBlockTable::getList([
	    'filter' => ['=NAME' => $tableName]
	    ])->fetch();
	    if (!$hlblock){
	    	echo "Таблица не установлена, пожалуйста, переустановите модуль";
	    	die();
	    	// return false;
	    }
	    return $hlblock['ID'];
	}

	public function addToTable($arrayLineTable, $currency = 'USD')
	{
		$id = self::getIdtable();
		$entity_data_class = self::GetEntityDataClass($id);
		$resultAdd = $entity_data_class::add(array(
		   'UF_CURRENCY'      => $currency,
		   'UF_ADDED'         => $arrayLineTable[$currency]['DATE'],
		   'UF_VALUE'         => $arrayLineTable[$currency]['VALUE']
			      
	   ));
	}

	function GetEntityDataClass($HlBlockId) {
		if (empty($HlBlockId) || $HlBlockId < 1)
		{
			return false;
		}
		$hlblock = HLBT::getById($HlBlockId)->fetch();	
		$entity = HLBT::compileEntity($hlblock);
		$entity_data_class = $entity->getDataClass();
		return $entity_data_class;
	}
}