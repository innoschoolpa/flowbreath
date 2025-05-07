<?php

if (!function_exists('storage_path')) {
    /**
     * Get the storage path for the application.
     *
     * @param string $path
     * @return string
     */
    function storage_path($path = '')
    {
        return dirname(__DIR__, 2) . '/storage' . ($path ? '/' . $path : '');
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            case 'empty':
            case '(empty)':
                return '';
        }
        
        return $value;
    }
} 