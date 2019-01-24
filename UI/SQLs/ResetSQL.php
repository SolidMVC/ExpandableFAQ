<?php
/**
 * Extension replace sql when plugin is (re)enabled
 * @note        Fired every time when plugin is enabled, or enabled->disabled->enabled, etc.
 * @note2       MySQL 'REPLACE INTO' works like MySQL 'INSERT INTO', except that if there is a row
 *              with the same key you are trying to insert, it will be deleted on replace instead of giving you an error.
 * @note3       Supports [BLOG_ID] BB code
 * @package     ExpandableFAQ
 * @author      Kestutis Matuliauskas
 * @copyright   Kestutis Matuliauskas
 * @License     @license See Legal/License.txt for details.
 */
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );

$arrReplaceSQL = array();
$arrPluginReplaceSQL = array();

$arrPluginReplaceSQL['settings'] = "(`conf_key`, `conf_value`, `conf_translatable`, `blog_id`) VALUES
('conf_load_font_awesome_from_plugin', '1', '0', [BLOG_ID])";