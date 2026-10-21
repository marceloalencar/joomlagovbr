<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_chamadas
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Helper\RouteHelper as ContentRouteHelper;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Helper for mod_chamadas.
 */
class ModChamadasHelper
{
    public function getChamadas(Registry $params): array
    {
        $modelo = (string) $params->get('modelo', 'article_content');
        $models = [
            'article_content' => 'ModeloArticle_content',
            'manual'          => 'ModeloManual',
        ];

        if (!isset($models[$modelo])) {
            throw new RuntimeException('Unsupported mod_chamadas model: ' . $modelo);
        }

        require_once __DIR__ . '/modelos/' . $modelo . '/' . $modelo . '.php';

        $modelClass = $models[$modelo];
        $model      = new $modelClass();

        return $model->getListaModelo($params);
    }

    public static function getIntroLimiteCaracteres(string $intro, Registry $params): string
    {
        if ($params->get('limitar_caractere')) {
            $limit = max(0, (int) $params->get('limite_caractere'));
            $text  = strip_tags($intro);

            if (strlen($text) > $limit) {
                $text  = substr($text, 0, $limit);
                $space = strrpos($text, ' ');
                $text  = $space === false ? $text : substr($text, 0, $space);

                return trim($text) . '...';
            }

            return $text;
        }

        return strip_tags($intro, '<b><i><strong><u>');
    }

    public static function getLink(
        Registry $params,
        array $fields = ['simple', 'menu', 'article'],
        ?object $contentItem = null,
        bool $isJoomlaArticle = true
    ): string {
        [$simple, $menu, $article] = array_pad($fields, 3, '');

        if ($params->get($simple, '') !== '') {
            return str_ireplace('{SITE}/', Uri::root(), (string) $params->get($simple));
        }

        if ($params->get($menu, '') !== '') {
            $menuId   = (int) $params->get($menu);
            $menuItem = Factory::getApplication()->getMenu()->getItem($menuId);

            return $menuItem ? Route::_($menuItem->link . '&Itemid=' . $menuItem->id) : '';
        }

        if ($params->get($article, '') !== '') {
            return Route::_(
                ContentRouteHelper::getArticleRoute((int) $params->get($article))
            );
        }

        if ($contentItem && $isJoomlaArticle) {
            return Route::_(
                ContentRouteHelper::getArticleRoute((int) $contentItem->id, (int) $contentItem->catid)
            );
        }

        if ($contentItem && isset($contentItem->link)) {
            return (string) $contentItem->link;
        }

        return '';
    }
}
