<?php

namespace Bitrix\Mymodulpars;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
use Bitrix\Highloadblock as HL;

class Utils
{
	public function dollarValue()
	{
		$date = date("Y-m-d H:i:s");
		$url = 'http://www.finmarket.ru/currency/USD/';
		$hlblock = HL\HighloadBlockTable::getList([
	    'filter' => ['=NAME' => 'TableOfCurrency']
	    ])->fetch();
 		if ($hlblock){
			$arFilter = array("UF_CURRENCY" => "USD");
			$arSelect = array('*');
			$entity_data_class = self::GetEntityDataClass($hlblock['ID']);
			$arData = $entity_data_class::getList(array(
					"select" => $arSelect,
					"filter" => $arFilter
			));
			$arData = new \CDBResult($arData, "table_currency"); 
			while($arResult = $arData->Fetch()){
				$idElement = $arResult["ID"];
				$dateOld = $arResult['UF_ADDED'];
				$usdOld = $arResult['UF_VALUE'];

			}
		// Проверяем есть ли записи в таблице
			if ($idElement>0){
				$hourdiff = round((strtotime($date) - strtotime($dateOld))/3600, 1);
				if ($hourdiff>2){
		//Проверяем актуальность данных, если запись в таблице сделана более 2х часов назад, обновляем
										$result = self::get_page($url, 'https://yandex.ru', $date);
					$resultUpdate = $entity_data_class::update($idElement, array(
					  'UF_CURRENCY'      => "USD",
					  'UF_ADDED'         => $result['USD']['DATE'],
					  'UF_VALUE'         => $result['USD']['VALUE'],
					  
					));
					echo $result['USD']['VALUE'];
				}else{
					echo $usdOld;
				}
			}else{
		// Если таблица есть, но в ней нет записей (удалены)
			    $result = self::get_page($url, 'https://yandex.ru', $date);
				$entity_data_class = self::GetEntityDataClass($hlblock['ID']);

				$resultAdd = $entity_data_class::add(array(
			      'UF_CURRENCY'         => 'USD',
			      'UF_ADDED'         => $result['USD']['DATE'],
			      'UF_VALUE'        => $result['USD']['VALUE']
			      
			   ));
				echo $result['USD']['VALUE'];
			}
 		}else{
 	// Если нет хайлоад таблицы с валютами
		    $result = self::get_page($url, 'https://yandex.ru', $date);
		    echo $result['USD']['VALUE'];
		    $arLangs = Array(
	          'ru' => 'Таблица валют',
	          'en' => 'TableOfCurrency'
	        );

	        $resultAdd = HL\HighloadBlockTable::add(array(
	          'NAME' => 'TableOfCurrency',
	          'TABLE_NAME' => 'table_currency', 
	        ));

			if ($resultAdd->isSuccess()) {
		    	$id = $resultAdd->getId();
		    	foreach($arLangs as $lang_key => $lang_val){
		        	HL\HighloadBlockLangTable::add(array(
		            	'ID' => $id,
		            	'LID' => $lang_key,
		            	'NAME' => $lang_val
		        	));
		    	}
			}else {
		    	$errors = $resultAdd->getErrorMessages();
			}
		 	$UFObject = 'HLBLOCK_'.$id;
			$arCartFields = Array(
			    'UF_CURRENCY'=>Array(
			        'ENTITY_ID' => $UFObject,
			        'FIELD_NAME' => 'UF_CURRENCY',
			        'USER_TYPE_ID' => 'string',
			        'MANDATORY' => 'Y',
			        "EDIT_FORM_LABEL" => Array('ru'=>'Валюта', 'en'=>'Currency'), 
			        "LIST_COLUMN_LABEL" => Array('ru'=>'Валюта', 'en'=>'Currency'),
			        "LIST_FILTER_LABEL" => Array('ru'=>'Валюта', 'en'=>'Currency'), 
			        "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
			        "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
			    ),
			    'UF_ADDED'=>Array(
			        'ENTITY_ID' => $UFObject,
			        'FIELD_NAME' => 'UF_ADDED',
			        'USER_TYPE_ID' => 'string',
			        'MANDATORY' => 'Y',
			        "EDIT_FORM_LABEL" => Array('ru'=>'Дата добавления', 'en'=>'Date added'), 
			        "LIST_COLUMN_LABEL" => Array('ru'=>'Дата добавления', 'en'=>'Date added'),
			        "LIST_FILTER_LABEL" => Array('ru'=>'Дата добавления', 'en'=>'Date added'), 
			        "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
			        "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
			    ),
			    'UF_VALUE'=>Array(
			        'ENTITY_ID' => $UFObject,
			        'FIELD_NAME' => 'UF_VALUE',
			        'USER_TYPE_ID' => 'string',
			        'MANDATORY' => 'Y',
			        "EDIT_FORM_LABEL" => Array('ru'=>'Значение валюты', 'en'=>'Value of currency'), 
			        "LIST_COLUMN_LABEL" => Array('ru'=>'Значение валюты', 'en'=>'Value of currency'),
			        "LIST_FILTER_LABEL" => Array('ru'=>'Значение валюты', 'en'=>'Value of currency'), 
			        "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''), 
			        "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
			    ),

			);

			$arSavedFieldsRes = Array();
			foreach($arCartFields as $arCartField){
				$obUserField  = new \CUserTypeEntity;
				$ID = $obUserField->Add($arCartField);
				$arSavedFieldsRes[] = $ID;
			}
			
			$entity_data_class = self::GetEntityDataClass($id);
			$resultAdd2 = $entity_data_class::add(array(
		      'UF_CURRENCY'         => 'USD',
		      'UF_ADDED'         => $result['USD']['DATE'],
		      'UF_VALUE'        => $result['USD']['VALUE']
		      
		   ));

 		}
	}
	function get_page($url, $referer = '', $date, $currency = 'USD') {

		$header[] = "Accept: text/html";
	    $header[] = "Accept-Charset: utf-8, windows-1251";
	    $curl = curl_init();
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.70 Safari/537.36");
	
	    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
   //	curl_setopt($curl, CURLOPT_HEADER, 1);
	    curl_setopt($curl, CURLOPT_FAILONERROR, 1);
	    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
	    curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

	    if ( !empty($referer) )
		    curl_setopt($curl, CURLOPT_REFERER, $referer);
		
	    $begin_time = microtime(true);

	    $res = curl_exec($curl);
	    $enc = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

	    $end_time = microtime(true);
	    $all_time = $end_time - $begin_time;
	    if ($all_time >= 9.9) {
		 
	    	return [];
	    }

	    if ( !empty($enc) )
	    {
		    $pos = strpos($enc, 'charset=');		
		    if ( $pos )
		    {
			    $enc = substr($enc, $pos + 8);
			    $enc = trim(strtolower($enc));
		    }
		    else	$enc = '';
	    }
	    else	$enc = '';

	    curl_close($curl);

	    if ($enc == 'windows-1251' || $enc == 'cp1251') {
		    $res = mb_convert_encoding($res, 'UTF-8', $enc);
	    }
	    elseif($enc == '') {
		    $res = mb_convert_encoding($res, 'UTF-8', 'auto');
	    }
	    $usd = self::getDataByOrder($res, '<div class="valvalue">', '</div>', 1);
		$result[$currency]['URL'] = $url;
		$result[$currency]['VALUE'] = $usd;
		$result[$currency]['DATE'] = $date;
		return $result;
}

    function getDataByOrder($text, $limit1, $limit2, $order) {
	    for ( $i = 1; $i <= $order; $i++ ) {
		    $pos = strpos($text, $limit1);
		    if ( $pos === false )
			    return false;
		    else {
			    $pos += strlen($limit1);
			    $text = substr($text, $pos);
			    if ( $i == $order )
			    {
				    $pos = strpos($text, $limit2);
				    if ( $pos === false )	return false;
				    else	$text = substr($text, 0, $pos);
			    }
		    }
	    }
	    return $text;
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