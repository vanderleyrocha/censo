<?php

if (!function_exists('formatarIbge')) {
    function formatarIbge($ibge)
    {
        if (strlen($ibge) !== 7) {
            return $ibge; // Retorna sem formatação se não tiver 7 dígitos
        }

        $estado = substr($ibge, 0, 2);
        $municipio = substr($ibge, 2, 4);
        $digito = substr($ibge, 6, 1);

        return "$estado.$municipio-$digito";
    }
}