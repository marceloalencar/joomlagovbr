<?php
/**
 * @package     Joomlagovbr
 * @subpackage  mod_videosdestaque
 *
 * @copyright   Copyright (C) 2015 Comunidade Joomla Calango e Grupo de Trabalho de Ministérios
 * @license     GNU General Public License version 2
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Helper\RouteHelper as ContentRouteHelper;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

class ModVideosDestaqueHelper
{
    public static function getItems(Registry $params): array
    {
        $items = $params->get('list_videos', []);

        if (is_string($items)) {
            $items = json_decode($items, true);
        }

        if (!is_array($items)) {
            return [];
        }

        if (isset($items['url']) && is_array($items['url'])) {
            $normalized = [];

            foreach ($items['url'] as $index => $url) {
                $normalized[] = [
                    'url'         => $url,
                    'name'        => $items['name'][$index] ?? '',
                    'description' => $items['description'][$index] ?? '',
                ];
            }

            $items = $normalized;
        } elseif (isset($items['list_videos0'])) {
            $items = array_values(array_filter(
                $items,
                static fn ($item, $key): bool => str_starts_with((string) $key, 'list_videos'),
                ARRAY_FILTER_USE_BOTH
            ));
        }

        $videos = [];

        foreach (array_slice($items, 0, 3) as $item) {
            if (is_object($item)) {
                $item = get_object_vars($item);
            }

            if (!is_array($item)) {
                continue;
            }

            $url = trim((string) ($item['url'] ?? ''));

            $embedUrl = self::getYoutubeEmbedUrl($url);

            if ($embedUrl === null) {
                continue;
            }

            $videos[] = [
                'url'         => $url,
                'embed_url'   => $embedUrl,
                'name'        => (string) ($item['name'] ?? ''),
                'description' => (string) ($item['description'] ?? ''),
            ];
        }

        return $videos;
    }

    public static function getYoutubeEmbedUrl(string $url): ?string
    {
        $parts = parse_url($url);

        if (!is_array($parts)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));

        if (!in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtube-nocookie.com', 'youtube-nocookie.com'], true)) {
            return null;
        }

        $videoId = '';

        if ($host === 'youtu.be') {
            $videoId = trim((string) ($parts['path'] ?? ''), '/');
        } else {
            parse_str((string) ($parts['query'] ?? ''), $query);
            $videoId = (string) ($query['v'] ?? '');

            if ($videoId === '' && preg_match('#^/(?:embed|shorts|live)/([^/]+)#', (string) ($parts['path'] ?? ''), $matches)) {
                $videoId = $matches[1];
            }
        }

        if (!preg_match('/^[A-Za-z0-9_-]{11}$/D', $videoId)) {
            return null;
        }

        return 'https://www.youtube-nocookie.com/embed/' . $videoId;
    }

    public static function getFooterLink(Registry $params): string
    {
        $url = trim((string) $params->get('url_link_footer', ''));

        if ($url !== '') {
            $url = str_ireplace(['{SITE}/', '{SITE}'], Uri::root(), $url);
            $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

            return filter_var($url, FILTER_VALIDATE_URL) && in_array($scheme, ['http', 'https'], true)
                ? $url
                : '';
        }

        $menuId = (int) $params->get('url_link_footer_menu', 0);

        if ($menuId > 0) {
            $menuItem = Factory::getApplication()->getMenu()->getItem($menuId);

            return $menuItem ? Route::_('index.php?Itemid=' . $menuItem->id) : '';
        }

        $articleId = (int) $params->get('url_link_footer_article', 0);

        if ($articleId > 0) {
            return Route::_(
                ContentRouteHelper::getArticleRoute($articleId)
            );
        }

        return '';
    }
}
