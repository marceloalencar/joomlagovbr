<?php
/**
 * @package    redessociais
 * @subpackage C:
 * @author     Ricardo Morais {@link }
 * @author     Created on 22-Nov-2013
 * @license    GNU/GPL
 */

use Joomla\CMS\Helper\ModuleHelper;

//-- No direct access
defined('_JEXEC') || die('=;)');


// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

$config = modRedesSociaisHelper::getConfigs($params);
$moduleclass_sfx = htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

require ModuleHelper::getLayoutPath('mod_redes_sociais', $params->get('layout', 'default'));


?>
