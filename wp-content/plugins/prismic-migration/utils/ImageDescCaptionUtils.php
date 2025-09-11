<?php

namespace utils;

class ImageDescCaptionUtils
{
    public static function getDescAndCaption($caption): array
    {
        $desc = '';
        $legend = '';
        if (isset($caption)) {
            $posSymbole = strpos($caption, '©');
            $posC = strpos($caption, '(c)');
            if ($posSymbole !== false) {
                $desc = substr($caption, 0, $posSymbole);
                $legend = substr($caption, $posSymbole);
            } elseif ($posC !== false) {
                $desc = substr($caption, 0, $posC);
                $legend = substr($caption, $posC);
            } else {
                $desc = trim($caption);
            }
        }
        return [
            'description' => $desc,
            'caption' => $legend,
        ];
    }
}
