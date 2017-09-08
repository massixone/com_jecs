/**
 * @package         Joomla Easy Custom Script (jecs)
 * @version         1.0.1 Free
 * 
 * @author          Massimo Di Primio <info@diprimio.com>
 * @link            https://www.diprimio.com
 * @copyright       Copyright© 2017 Massimo Di Primio All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 *
 * ----------------------------------------------------------------------------
 * 
 * File containing mysql schema updates for the component.

Changelog

Fix error: 'table column script_inline too short'.
*/
ALTER TABLE `joomladb`.`#__jecs_rules` 
CHANGE COLUMN `script_inline` `script_inline` TEXT CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_unicode_ci' NOT NULL DEFAULT '' COMMENT 'content of the inline script' ;