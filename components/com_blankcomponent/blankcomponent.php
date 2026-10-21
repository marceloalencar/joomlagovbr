<?php
/**
 * Author: Omar Muhammad
 * Copyright (C) 2012 http://omar84.com. All Rights Reserved.
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Joomla\CMS\Factory;

defined('_JEXEC') or die;

$application = Factory::getApplication();
$params      = $application->getParams();
$menu        = $application->getMenu()->getActive();
$pageTitle   = trim((string) $params->get('page_title', ''));

if ($pageTitle === '' && $menu !== null) {
    $pageTitle = $menu->title;
}

if ($pageTitle !== '') {
    $application->getDocument()->setTitle($pageTitle);
}

$description = (string) $params->get('menu-meta_description', '');
$keywords    = (string) $params->get('menu-meta_keywords', '');
$robots      = (string) $params->get('robots', '');

if ($description !== '') {
    $application->getDocument()->setDescription($description);
}

if ($keywords !== '') {
    $application->getDocument()->setMetaData('keywords', $keywords);
}

if ($robots !== '') {
    $application->getDocument()->setMetaData('robots', $robots);
}

$pageClass = trim((string) $params->get('pageclass_sfx', ''));
$class     = 'blank' . ($pageClass !== '' ? ' ' . $pageClass : '');

echo '<div class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '">';

if ($params->get('show_page_heading', 1)) {
    $heading = trim((string) $params->get('page_heading', ''));
    $heading = $heading !== '' ? $heading : $pageTitle;

    if ($heading !== '') {
        echo '<h1>' . htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') . '</h1>';
    }
}

echo '</div>';