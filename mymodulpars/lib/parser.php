<?php

namespace Bitrix\Mymodulpars;

// require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

class Parser
{
    public function getCurrencyValue($url, $referer = '', $date, $currency = 'USD')
    {
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
	    if ($currency == ('USD')){
	    	$currencyValue = self::getDataByOrder($res, '<div class="valvalue">', '</div>', 1);
		}
		$result[$currency]['URL'] = $url;
		$result[$currency]['VALUE'] = $currencyValue;
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

}
