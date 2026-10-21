<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_chamadas
 *
 * @copyright   Copyright (C) 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Helper\ModuleHelper;

defined('_JEXEC') or die;

require_once __DIR__ . '/helper.php';

$chamadas = new ModChamadasHelper();

$lista_chamadas  = $chamadas->getChamadas($params);
$link_saiba_mais = $chamadas->getLink($params, ['link_saiba_mais', 'link_saiba_mais_menu', 'link_saiba_mais_article']);
$link_secundario = $chamadas->getLink($params, ['link_secundario', 'link_secundario_menu', 'link_secundario_article']);

if (!empty($lista_chamadas)) {
    require ModuleHelper::getLayoutPath('mod_chamadas', $params->get('layout', 'default'));
} elseif ($params->get('habilitar_mensagem_vazia')) {
    require ModuleHelper::getLayoutPath('mod_chamadas', 'empty');
} else {
    return;
}
