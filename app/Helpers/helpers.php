<?php

if (! function_exists('convert_image')) {
    function convert_image($path): String
    {
        if ($path) {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $image = base64_encode(file_get_contents($path));
            return "data:image/" . $ext . ";base64," . $image;
        } else {
            return "";
        }
    }
}
if (! function_exists('bong_format')) {
    function bong_format($value): String
    {
        return number_format($value, 2);
    }
}
