<?php

if (!function_exists('translateCategory')) {
    function translateCategory(string $name): string
    {
        $key = 'calendar.categories.' . $name;
        $translated = __($key);
        return ($translated !== $key) ? $translated : $name;
    }
}
