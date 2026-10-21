<?php
/**
 * @package     Joomlagovbr
 * @subpackage  mod_videosdestaque
 *
 * @copyright   Copyright (C) 2015 Comunidade Joomla Calango e Grupo de Trabalho de Ministérios
 * @license     GNU General Public License version 2
 */

use Joomla\CMS\Helper\ModuleHelper;

defined('_JEXEC') or die;


require_once __DIR__ . '/helper.php';
$items = ModVideosDestaqueHelper::getItems($params);
$text_link_footer = (string) $params->get('text_link_footer', '');
$url_link_footer  = ModVideosDestaqueHelper::getFooterLink($params);

require ModuleHelper::getLayoutPath('mod_videosdestaque', $params->get('layout', 'default'));