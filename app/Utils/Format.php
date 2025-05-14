<?php

namespace App\Utils;


class Format {



	public static function digitOnly($string) {
		return preg_replace("/[^0-9]/", "", $string);
	}


	public static function phone($numero)	{
		$ret =  self::digitOnly($numero);
		$length = strlen($ret);
		if ($length > 4) {
			$ret = substr_replace($ret, '-', -4, 0);
			$length++;
		}
		if ($length > 9) {
			$ret = substr_replace($ret, ' ', -9, 0);
			$length++;
		}

		if ($length == 12) {
			$ret = substr_replace($ret, ')', 2, 0);
			$ret = substr_replace($ret, '(', 0, 0);
		}
		if ($length == 13) {
			$ret = substr_replace($ret, ' ', 2, 0);
			$ret = substr_replace($ret, ')', 2, 0);
			$ret = substr_replace($ret, '(', 0, 0);
		}
		return $ret;
	}

	public static function cpf($string)
	{
		$string = Format::digitOnly($string);
		if (strlen($string) == 11) {
			$formatado  = substr($string, 0, 3) . '.';
			$formatado .= substr($string, 3, 3) . '.';
			$formatado .= substr($string, 6, 3) . '-';
			$formatado .= substr($string, 9, 2);
			$string = $formatado;
		}
		return $string;
	}

	public static function inep($string) : string|null
    {
        return substr_replace(substr_replace(substr_replace($string, " ", 6, 0), " ", 4, 0), " ", 2, 0);
	}

	public static function dateBRtoEn($string) : string|null
	{
		$date = explode("/", $string);
		if (count($date) != 3) {
			return null;
		}
		return "{$date[2]}-{$date[1]}-{$date[0]}";
	}

}


?>
