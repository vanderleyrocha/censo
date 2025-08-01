<?php

namespace App\Helpers;

class Validate
{

    public static function cpf($cpf)
    {

        // Extrai somente os números
        $cpf = preg_replace('/[^0-9]/is', '', $cpf);
        $cpf = str_replace(' ', '', $cpf);

        // Verifica se foi informado todos os digitos corretamente
        if ((strlen($cpf) > 11) || empty($cpf)) {
            return false;
        }
        
        $cpf = str_pad($cpf, 11, "0", STR_PAD_LEFT);
        // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Faz o calculo para validar o CPF
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }

    public static function nis(string $nis): bool
    {
        // Remove any non-numeric characters
        $nis = preg_replace('/\D/', '', $nis);

        // NIS must be 11 digits
        if (strlen($nis) !== 11) {
            return false;
        }

        // Calculate the checksum
        $sum = 0;
        $weights = [3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 10; $i++) {
            $sum += $nis[$i] * $weights[$i];
        }

        $remainder = $sum % 11;
        $checkDigit = $remainder < 2 ? 0 : 11 - $remainder;

        // Validate the check digit
        return intval($nis[10]) === $checkDigit;
    }

    public static function data(string $date, string $format): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

}


?>
