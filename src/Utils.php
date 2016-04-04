<?php

namespace Blazon;

class Utils
{
    public static function makePathAbsolute($path)
    {
        switch ($path[0]) {
            case '/':
                break;
            case '~':
                $home = getenv("HOME");
                $path = $home . '/' . $path;
                break;
            default:
                $path = getcwd() . '/' . $path;
                break;
        }
        $path = rtrim($path, '/');

        return $path;
    }
}
