<?php
/**
 * Patches to Main WordPress Formatting API.
 *
 * This is the file of functions, that is missing in wp-includes\formatting.php
 *
 * @see \wp-includes\formatting.php
 * @package WordPress
 */

if(!function_exists('esc_br_html'))
{
    /**
     * Escape with line-breaks
     *
     * Related ticked - https://core.trac.wordpress.org/ticket/46188
     *
     * @param string $text
     * @return string
     */
    function esc_br_html($text)
    {
        $escaped_text_array = array_map('esc_html', explode("\n", $text));
        $escaped_multiline_text = implode("<br />", $escaped_text_array);

        return $escaped_multiline_text;
    }
}