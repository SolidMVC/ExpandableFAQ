<?php
/**
 * Backwards compatibility functions of PHP 5.6
 *
 * @package PHP
 */

if(!function_exists('php7_dirname'))
{
    /**
     * Workaround for dirname in PHP 5.6
     *
     * @see https://www.php.net/dirname
     * @param string $path
     * @param int $count
     * @return string
     */
    function php7_dirname($path, $count = 1)
    {
        if ($count > 1)
        {
            return dirname(php7_dirname($path, --$count));
        } else
            {
            return dirname($path);
        }
    }
}