<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
// use Bitrix\Main\Localization\Loc;
// use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
// use \Bitrix\Highloadblock as HL;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config as Conf;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Entity\Base;
use \Bitrix\Main\Application;
use Bitrix\Main\EventManager; 

use Bitrix\Highloadblock as HL;
Loc::loadMessages(__FILE__);

Class mymodulpars extends CModule
{
	var $MODULE_ID = "mymodulpars";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function __construct()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = "Мой модуль";
		$this->MODULE_DESCRIPTION = "Описание моего модуля";
	}


	function InstallDB($install_wizard = true)
	{
		RegisterModule("mymodulpars");

		return true;
	}

	function UnInstallDB($arParams = Array())
	{
		UnRegisterModule("mymodulpars");
		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles()
	{

		return true;
	}

	function UnInstallFiles()
	{
		return true;
	}

	function DoInstall()
	{
			$arLangs = Array(
	          'ru' => 'Таблица валют',
	          'en' => 'TableOfCurrency'
	        );
			CModule::IncludeModule('highloadblock');
	        $resultAdd = \Bitrix\Highloadblock\HighloadBlockTable::add(array(
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
			$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($id)->fetch();	
			$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
			$entity_data_class = $entity->getDataClass();
			$resultAdd2 = $entity_data_class::add(array(
		      'UF_CURRENCY'         => 'USD',
		      'UF_ADDED'         => $result['USD']['DATE'],
		      'UF_VALUE'        => $result['USD']['VALUE']
		      
		   ));

		$this->InstallFiles();
		$this->InstallDB(false);

	}

	function DoUninstall()
	{
		CModule::IncludeModule('highloadblock');
		$result = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter'=>array('=NAME'=>"TableOfCurrency")));
		if($row = $result->fetch())
		{

		    $id = $row["ID"];
		}
		if ($id>0){
			Bitrix\Highloadblock\HighloadBlockTable::delete($id);
		}
        $this->UnInstallDB(false);
	}
}
?>