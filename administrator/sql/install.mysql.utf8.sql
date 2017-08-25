/**
 * @package         Joomla Easy Custom Script (jecs)
 * @version         1.0.0 Free
 * 
 * @author          Massimo Di Primio <info@diprimio.com>
 * @link            https://www.diprimio.com
 * @copyright       Copyright © 2017 Massimo Di Primio All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/
CREATE TABLE IF NOT EXISTS `#__jecs_rules` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `modified_by` int(11) NOT NULL,
  `rulename` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The nam of this rule',
  `debug` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=debug-off, 1=debug-on',
  `side` tinyint(1) NOT NULL DEFAULT 2 COMMENT '0=none, 1=backend, 2=frontend, 3=both',
  `script_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=javascript, 1=stylesheet, 2=json, 3=ld+json, 4=custom',
  `script_source` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=internal, 1=external(inline)', 
  `script_location` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=before-end-of-head, 1=after-start-of-body, 2=before-end-of-body',
  `script_inline` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL default "" COMMENT 'content of the inline script',
  `script_minify` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=no-minify, 1=minify (inline script only)',
  `file_path` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL default "" COMMENT 'path of the script file',
  `script_params` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL default "" COMMENT 'param for script file',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

