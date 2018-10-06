<?php
/**
 * Extension install insert sql data
 * @note        Supports all installation BB codes
 * @package     ExpandableFAQ
 * @author      Kestutis Matuliauskas
 * @copyright   Kestutis Matuliauskas
 * @License     @license See Legal/License.txt for details.
 */
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );

$arrInsertSQL = array();
$arrPluginInsertSQL = array();

$arrPluginInsertSQL['settings'] = "(`conf_key`, `conf_value`, `conf_translatable`, `blog_id`) VALUES
('conf_load_font_awesome_from_plugin', '0', '0', [BLOG_ID]),
('conf_plugin_version', '[PLUGIN_VERSION]', '0', [BLOG_ID]),
('conf_system_style', 'Crimson Red', '0', [BLOG_ID]),
('conf_updated', '0', '0', [BLOG_ID]),
('conf_use_sessions', '1', '0', [BLOG_ID]),
('conf_timestamp', '[TIMESTAMP]', '0', [BLOG_ID]);";
