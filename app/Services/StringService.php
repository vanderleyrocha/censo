<?php

namespace App\Services;

class StringService
{
    public function replacesBackslashWithApostrophe(string $text): string
    {
        // Trata strings entre aspas simples
        $processed = preg_replace_callback(
            "/'(?:[^'\\\\]|\\\\.)*'/",
            function ($match) {
                $str = $match[0];
                // substitui \` por `
                $str = str_replace("\\`", "`", $str);
                // substitui \' por `
                $str = str_replace("\\'", "`", $str);
                return $str;
            },
            $text
        );

        // Fora das aspas, remove \`
        $processed = str_replace("\\'", "`", $processed);
        $processed = str_replace("\\`", "`", $processed);

        return $processed;
    }
}
