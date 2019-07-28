<?php
/**
 * Demo data
 * @package     ExpandableFAQ
 * @author      Kestutis Matuliauskas
 * @copyright   Kestutis Matuliauskas
 * @License     @license See Legal/License.txt for details.
 *
 * @expandable-faq-plugin-demo
 * Demo UID: 1
 * Demo Name: Car Rental Agency - Steel Blue
 * Demo Enabled: 1
 */
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );

$arrPluginReplaceSQL = array();

// First - include a common demo SQL data, to avoid repeatedness
include('Shared/CarRentalAgencySQLPartial.php');

// Then - list tables that are different for each demo version

$arrPluginReplaceSQL['settings'] = "(`conf_key`, `conf_value`, `conf_translatable`, `blog_id`) VALUES
('conf_load_font_awesome_from_plugin', '1', '0', [BLOG_ID]),
('conf_system_style', 'Steel Blue', '0', [BLOG_ID]),
('conf_updated', '0', '0', [BLOG_ID]),
('conf_use_sessions', '1', '0', [BLOG_ID])";