<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Sampledata.egov
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\SampleData\Egov\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Plugin\AjaxEvent;
use Joomla\CMS\Event\SampleData\GetOverviewEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;
use RuntimeException;
use Throwable;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

final class Egov extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    protected $autoloadLanguage = true;

    private ?array $dataset = null;

    public static function getSubscribedEvents(): array
    {
        return [
            'onSampledataGetOverview'    => 'onSampledataGetOverview',
            'onAjaxSampledataApplyStep1' => 'onAjaxSampledataApplyStep1',
            'onAjaxSampledataApplyStep2' => 'onAjaxSampledataApplyStep2',
            'onAjaxSampledataApplyStep3' => 'onAjaxSampledataApplyStep3',
        ];
    }

    public function onSampledataGetOverview(GetOverviewEvent $event): void
    {
        if (!$this->getApplication()->getIdentity()->authorise('core.create', 'com_content')) {
            return;
        }

        $overview              = new \stdClass();
        $overview->name        = $this->_name;
        $overview->title       = Text::_('PLG_SAMPLEDATA_EGOV_OVERVIEW_TITLE');
        $overview->description = Text::_('PLG_SAMPLEDATA_EGOV_OVERVIEW_DESC');
        $overview->icon        = 'download';
        $overview->steps       = 3;

        $event->addResult($overview);
    }

    public function onAjaxSampledataApplyStep1(AjaxEvent $event): void
    {
        $this->runStep($event, 1, ['com_content', 'com_tags'], fn (): int => $this->createContent());
    }

    public function onAjaxSampledataApplyStep2(AjaxEvent $event): void
    {
        $this->runStep($event, 2, ['com_contact', 'com_banners'], fn (): int => $this->createContactAndBanners());
    }

    public function onAjaxSampledataApplyStep3(AjaxEvent $event): void
    {
        $this->runStep(
            $event,
            3,
            ['com_menus', 'com_content', 'com_contact', 'com_blankcomponent', 'com_modules'],
            fn (): int => $this->createMenus()
        );
    }

    private function runStep(AjaxEvent $event, int $step, array $components, callable $callback): void
    {
        if (!Session::checkToken('get') || $this->getApplication()->getInput()->get('type') !== $this->_name) {
            return;
        }

        try {
            foreach ($components as $component) {
                $this->assertComponentAccess($component);
            }

            $count = $callback();
            $event->addResult([
                'success' => true,
                'message' => Text::sprintf('PLG_SAMPLEDATA_EGOV_STEP_SUCCESS', $step, $count),
            ]);
        } catch (Throwable $error) {
            $event->addResult([
                'success' => false,
                'message' => Text::sprintf('PLG_SAMPLEDATA_EGOV_STEP_FAILED', $step, $error->getMessage()),
            ]);
        }
    }

    private function assertComponentAccess(string $component): void
    {
        if (
            !ComponentHelper::isEnabled($component)
            || !$this->getApplication()->getIdentity()->authorise('core.create', $component)
        ) {
            throw new RuntimeException(Text::sprintf('PLG_SAMPLEDATA_EGOV_COMPONENT_REQUIRED', $component));
        }
    }

    private function createContent(): int
    {
        $data       = $this->getDataset();
        $userId     = (int) $this->getApplication()->getIdentity()->id;
        $categories = $this->createCategories($data['categories'], $userId, ['com_content']);
        $tags       = $this->createTags($data['tags'], $userId);

        $factory       = $this->getApplication()->bootComponent('com_content')->getMVCFactory();
        $articleModel  = $factory->createModel('Article', 'Administrator', ['ignore_request' => true]);

        foreach ($data['articles'] as $article) {
            $marker = $this->marker('article', $article['id']);
            $id     = $this->findMarkerId('#__content', 'note', $marker);

            if ($id !== null) {
                $this->ensureFeaturedArticle((int) $id, (int) $article['featured'] === 1);
                continue;
            }

            $tagIds = [];

            foreach ($article['tags'] as $sourceTagId) {
                if (!isset($tags[(int) $sourceTagId])) {
                    throw new RuntimeException(
                        'Missing migrated tag ' . $sourceTagId . ' for article ' . $article['id']
                    );
                }

                $tagIds[] = (string) $tags[(int) $sourceTagId];
            }

            $record = [
                'id'               => 0,
                'title'            => $article['title'],
                'alias'            => $this->safeAlias($article['alias']),
                'introtext'        => $article['introtext'],
                'fulltext'         => $article['fulltext'],
                'state'            => 1,
                'catid'            => $this->categoryId((int) $article['catid'], $categories),
                'created'          => $this->validDate($article['created']),
                'created_user_id'  => $userId,
                'created_by_alias' => $article['created_by_alias'],
                'modified'         => null,
                'modified_user_id' => $userId,
                'publish_up'       => $this->validDate($article['publish_up']),
                'publish_down'     => $this->validDate($article['publish_down']),
                'images'           => $this->jsonArray($article['images'], 'article images'),
                'urls'             => json_encode(
                    $this->jsonArray($article['urls'], 'article URLs'),
                    JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
                ),
                'attribs'          => $this->jsonArray($article['attribs'], 'article attributes'),
                'metakey'          => $article['metakey'],
                'metadesc'         => $article['metadesc'],
                'metadata'         => $this->jsonArray($article['metadata'], 'article metadata'),
                'featured'         => (int) $article['featured'],
                'access'           => 1,
                'language'         => '*',
                'note'             => (string) ($menu['note'] ?? '') !== ''
                    ? $marker . "\n" . (string) $menu['note']
                    : $marker,
                'associations'     => [],
                'tags'             => $tagIds,
            ];

            if (!$articleModel->save($record)) {
                throw new RuntimeException($articleModel->getError());
            }

            $this->ensureFeaturedArticle((int) $articleModel->getItem()->id, (int) $article['featured'] === 1);
        }

        return count($this->filterCategories($data['categories'], ['com_content']))
            + count($data['tags'])
            + count($data['articles']);
    }

    private function createCategories(array $sourceCategories, int $userId, array $extensions): array
    {
        $sourceCategories = $this->filterCategories($sourceCategories, $extensions);
        $factory          = $this->getApplication()->bootComponent('com_categories')->getMVCFactory();
        $model            = $factory->createModel('Category', 'Administrator', ['ignore_request' => true]);
        $ids              = [];

        usort(
            $sourceCategories,
            static fn (array $left, array $right): int => [(int) $left['level'], (int) $left['id']]
                <=> [(int) $right['level'], (int) $right['id']]
        );

        foreach ($sourceCategories as $category) {
            $sourceId = (int) $category['id'];

            if ($category['alias'] === 'uncategorised') {
                $ids[$sourceId] = $this->findDefaultCategoryId($category['extension']);
                continue;
            }

            $marker = $this->marker('category', $sourceId);
            $id     = $this->findMarkerId('#__categories', 'note', $marker);

            if ($id !== null) {
                $ids[$sourceId] = $id;
                continue;
            }

            $parentId = (int) $category['parent_id'];
            $parentId = $parentId === 1
                ? 1
                : ($ids[$parentId] ?? $this->findDefaultCategoryId($category['extension']));

            $record = [
                'id'              => 0,
                'extension'       => $category['extension'],
                'title'           => $category['title'],
                'alias'           => $this->safeAlias($category['alias']),
                'note'            => $marker,
                'description'     => $category['description'],
                'published'       => 1,
                'parent_id'       => $parentId,
                'access'          => 1,
                'params'          => $this->jsonArray($category['params'], 'category parameters'),
                'metadesc'        => $category['metadesc'],
                'metakey'         => $category['metakey'],
                'metadata'        => $this->jsonArray($category['metadata'], 'category metadata'),
                'created_user_id' => $userId,
                'language'        => '*',
            ];

            if (!$model->save($record)) {
                throw new RuntimeException($model->getError());
            }

            $ids[$sourceId] = (int) $model->getItem()->id;
        }

        return $ids;
    }

    private function ensureFeaturedArticle(int $articleId, bool $featured): void
    {
        if (
            !$featured
            || !ComponentHelper::isEnabled('com_workflow')
            || !PluginHelper::isEnabled('workflow', 'featuring')
            || !ComponentHelper::getParams('com_content')->get('workflow_enabled')
        ) {
            return;
        }

        $query = $this->getDatabase()->getQuery(true)
            ->select($this->getDatabase()->quoteName('content_id'))
            ->from($this->getDatabase()->quoteName('#__content_frontpage'))
            ->where($this->getDatabase()->quoteName('content_id') . ' = :content_id')
            ->bind(':content_id', $articleId);

        if ($this->getDatabase()->setQuery($query)->loadResult() !== null) {
            return;
        }

        $featuredItem = (object) [
            'content_id'    => $articleId,
            'ordering'      => 0,
            'featured_up'   => null,
            'featured_down' => null,
        ];

        $this->getDatabase()->insertObject('#__content_frontpage', $featuredItem);
    }

    private function createTags(array $sourceTags, int $userId): array
    {
        $model = $this->getApplication()->bootComponent('com_tags')->getMVCFactory()
            ->createModel('Tag', 'Administrator', ['ignore_request' => true]);
        $ids = [];

        usort(
            $sourceTags,
            static fn (array $left, array $right): int => [(int) $left['level'], (int) $left['id']]
                <=> [(int) $right['level'], (int) $right['id']]
        );

        foreach ($sourceTags as $tag) {
            $sourceId = (int) $tag['id'];
            $marker   = $this->marker('tag', $sourceId);
            $id       = $this->findMarkerId('#__tags', 'note', $marker);

            if ($id !== null) {
                $ids[$sourceId] = $id;
                continue;
            }

            $parentId = (int) $tag['parent_id'];
            $parentId = $parentId === 1 ? 1 : ($ids[$parentId] ?? 1);

            $record = [
                'id'              => 0,
                'title'           => $tag['title'],
                'alias'           => $this->safeAlias($tag['alias']),
                'note'            => $marker,
                'description'     => $tag['description'],
                'parent_id'       => $parentId,
                'published'       => 1,
                'access'          => 1,
                'params'          => $this->jsonArray($tag['params'], 'tag parameters'),
                'language'        => '*',
                'created_user_id' => $userId,
            ];

            if (!$model->save($record)) {
                throw new RuntimeException($model->getError());
            }

            $ids[$sourceId] = (int) $model->getState('tag.id');
        }

        return $ids;
    }

    private function createContactAndBanners(): int
    {
        $data       = $this->getDataset();
        $userId     = (int) $this->getApplication()->getIdentity()->id;
        $categories = $this->createCategories($data['categories'], $userId, ['com_contact', 'com_banners']);

        $contactModel = $this->getApplication()->bootComponent('com_contact')->getMVCFactory()
            ->createModel('Contact', 'Administrator', ['ignore_request' => true]);

        foreach ($data['contacts'] as $contact) {
            $alias = $this->contactAlias($contact);

            if ($this->findMarkerId('#__contact_details', 'alias', $alias) !== null) {
                continue;
            }

            $record = [
                'id'               => 0,
                'name'             => $contact['name'],
                'alias'            => $alias,
                'con_position'     => $contact['con_position'],
                'address'          => $contact['address'],
                'suburb'           => $contact['suburb'],
                'state'            => $contact['state'],
                'country'          => $contact['country'],
                'postcode'         => $contact['postcode'],
                'telephone'        => $contact['telephone'],
                'fax'              => $contact['fax'],
                'misc'             => $contact['misc'],
                'image'            => $contact['image'],
                'email_to'         => $contact['email_to'],
                'default_con'      => 0,
                'published'        => 1,
                'ordering'         => 0,
                'params'           => $this->jsonArray($contact['params'], 'contact parameters'),
                'user_id'          => 0,
                'catid'            => $this->categoryId((int) $contact['catid'], $categories),
                'access'           => 1,
                'mobile'           => $contact['mobile'],
                'webpage'          => $contact['webpage'],
                'sortname1'        => $contact['sortname1'],
                'sortname2'        => $contact['sortname2'],
                'sortname3'        => $contact['sortname3'],
                'language'         => '*',
                'created'          => $this->validDate($contact['created']),
                'created_user_id'  => $userId,
                'created_by_alias' => $contact['created_by_alias'],
                'metakey'          => $contact['metakey'],
                'metadesc'         => $contact['metadesc'],
                'metadata'         => json_encode(
                    $this->jsonArray($contact['metadata'], 'contact metadata'),
                    JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
                ),
                'featured'         => (int) $contact['featured'],
                'publish_up'       => $this->validDate($contact['publish_up']),
                'publish_down'     => $this->validDate($contact['publish_down']),
            ];

            if (!$contactModel->save($record)) {
                throw new RuntimeException($contactModel->getError());
            }

        }

        $bannerModel = $this->getApplication()->bootComponent('com_banners')->getMVCFactory()
            ->createModel('Banner', 'Administrator', ['ignore_request' => true]);

        foreach ($data['banners'] as $banner) {
            $alias = 'egov-' . trim($banner['alias']);

            if ($this->findMarkerId('#__banners', 'alias', $alias) !== null) {
                continue;
            }

            $record = [
                'id'               => 0,
                'cid'              => 0,
                'type'             => (int) $banner['type'],
                'name'             => $banner['name'],
                'alias'            => $alias,
                'clickurl'         => $banner['clickurl'],
                'state'            => 1,
                'catid'            => $this->categoryId((int) $banner['catid'], $categories),
                'description'      => $banner['description'],
                'custombannercode' => $banner['custombannercode'],
                'sticky'           => (int) $banner['sticky'],
                'ordering'         => (int) $banner['ordering'],
                'metakey'          => $banner['metakey'],
                'params'           => $banner['params'],
                'own_prefix'       => (int) $banner['own_prefix'],
                'metakey_prefix'   => $banner['metakey_prefix'],
                'purchase_type'    => (int) $banner['purchase_type'],
                'track_clicks'     => (int) $banner['track_clicks'],
                'track_impressions' => (int) $banner['track_impressions'],
                'publish_up'       => $this->validDate($banner['publish_up']),
                'publish_down'     => $this->validDate($banner['publish_down']),
                'created_by'       => $userId,
                'created_by_alias' => $banner['created_by_alias'],
                'language'         => '*',
            ];

            if (!$bannerModel->save($record)) {
                throw new RuntimeException($bannerModel->getError());
            }

        }

        return count($this->filterCategories($data['categories'], ['com_contact', 'com_banners']))
            + count($data['contacts'])
            + count($data['banners']);
    }

    private function createModules(
        array $modules,
        array $menuTypes,
        array $menuIds,
        array $categoryIds,
        array $articleIds
    ): int
    {
        $model = $this->getApplication()->bootComponent('com_modules')->getMVCFactory()
            ->createModel('Module', 'Administrator', ['ignore_request' => true]);
        $moduleTypes = [];
        $created     = 0;

        foreach ($modules as $module) {
            $sourceId = (int) $module['id'];
            $marker   = $this->marker('module', $sourceId);

            $existingModuleId = $this->findModuleMarkerId($marker);

            if ($existingModuleId !== null) {
                $this->removeLegacyModuleStyle($existingModuleId);
                continue;
            }

            $moduleType    = 'module';
            $moduleElement = (string) $module['module'];

            if (!isset($moduleTypes[$moduleElement])) {
                $query = $this->getDatabase()->getQuery(true)
                    ->select($this->getDatabase()->quoteName('extension_id'))
                    ->from($this->getDatabase()->quoteName('#__extensions'))
                    ->where($this->getDatabase()->quoteName('type') . ' = :type')
                    ->where($this->getDatabase()->quoteName('element') . ' = :element')
                    ->where($this->getDatabase()->quoteName('client_id') . ' = 0')
                    ->where($this->getDatabase()->quoteName('enabled') . ' = 1')
                    ->bind(':type', $moduleType)
                    ->bind(':element', $moduleElement);
                $moduleTypes[$moduleElement] = $this->getDatabase()->setQuery($query)->loadResult();
            }

            if ($moduleTypes[$moduleElement] === null) {
                throw new RuntimeException('The ' . $moduleElement . ' module must be installed and enabled.');
            }

            $params = $this->jsonArray($module['params'], $moduleElement . ' module parameters');

            if (in_array((string) ($params['style'] ?? ''), ['0', '_:default', 'default'], true)) {
                unset($params['style']);
            }

            if ($moduleElement === 'mod_menu') {
                $sourceMenuType = (string) ($params['menutype'] ?? '');

                if (!isset($menuTypes[$sourceMenuType])) {
                    throw new RuntimeException('The menu type ' . $sourceMenuType . ' was not migrated for module ' . $module['title'] . '.');
                }

                $params['menutype'] = $menuTypes[$sourceMenuType];
            }

            if ($moduleElement === 'mod_banners') {
                if (!isset($params['catid']) || !is_array($params['catid'])) {
                    throw new RuntimeException('The banner module has no valid category selection.');
                }

                foreach ($params['catid'] as $index => $categoryId) {
                    $params['catid'][$index] = (string) $this->categoryId((int) $categoryId, $categoryIds);
                }
            }

            if ($moduleElement === 'mod_chamadas') {
                if (!empty($params['catid'])) {
                    if (!is_array($params['catid'])) {
                        throw new RuntimeException('The chamada module has no valid category selection.');
                    }

                    foreach ($params['catid'] as $index => $categoryId) {
                        $params['catid'][$index] = (string) $this->categoryId((int) $categoryId, $categoryIds);
                    }
                }

                foreach (['id_article_unico', 'id_item_unico'] as $field) {
                    if (!empty($params[$field])) {
                        $sourceArticleId = (int) $params[$field];
                        $params[$field] = (string) ($articleIds[$sourceArticleId]
                            ?? throw new RuntimeException(
                                'The chamada module references missing article ' . $sourceArticleId . '.'
                            ));
                    }
                }

                foreach ($params as $field => $sourceId) {
                    if (empty($sourceId)) {
                        continue;
                    }

                    $field = (string) $field;

                    if (str_ends_with($field, '_menu') || preg_match('/^url_menu_item\d+$/D', $field)) {
                        $sourceMenuId = (int) $sourceId;
                        $params[$field] = (string) ($menuIds[$sourceMenuId]
                            ?? throw new RuntimeException(
                                'The chamada module references missing menu item ' . $sourceMenuId . '.'
                            ));
                    } elseif (str_ends_with($field, '_article') || preg_match('/^url_article_item\d+$/D', $field)) {
                        $sourceArticleId = (int) $sourceId;
                        $params[$field] = (string) ($articleIds[$sourceArticleId]
                            ?? throw new RuntimeException(
                                'The chamada module references missing article ' . $sourceArticleId . '.'
                            ));
                    }
                }
            }

            if ($moduleElement === 'mod_videosdestaque') {
                $menuField = 'url_link_footer_menu';
                $menuId    = (int) ($params[$menuField] ?? 0);

                if ($menuId > 0) {
                    $params[$menuField] = (string) ($menuIds[$menuId]
                        ?? throw new RuntimeException(
                            'The video highlight module references missing menu item ' . $menuId . '.'
                        ));
                }

                $articleField = 'url_link_footer_article';
                $articleId    = (int) ($params[$articleField] ?? 0);

                if ($articleId > 0) {
                    $params[$articleField] = (string) ($articleIds[$articleId]
                        ?? throw new RuntimeException(
                            'The video highlight module references missing article ' . $articleId . '.'
                        ));
                }
            }

            $assignment = (int) ($module['assignment'] ?? 0);
            $assigned   = [];

            foreach ($module['assigned'] ?? [] as $sourceMenuId) {
                $sourceMenuId = (int) $sourceMenuId;

                if (!isset($menuIds[$sourceMenuId])) {
                    throw new RuntimeException('Menu item ' . $sourceMenuId . ' was not migrated for module ' . $module['title'] . '.');
                }

                $assigned[] = $menuIds[$sourceMenuId];
            }

            $sourceNote = trim((string) ($module['source_note'] ?? ''));
            $note       = $sourceNote === '' ? $marker : $marker . "\n" . $sourceNote;
            $record     = [
                'id'         => 0,
                'title'      => $module['title'],
                'note'       => $note,
                'content'    => $module['content'] ?? '',
                'ordering'   => (int) ($module['ordering'] ?? 0),
                'position'   => $module['position'],
                'published'  => 1,
                'module'     => $moduleElement,
                'access'     => (int) $module['access'],
                'showtitle'  => (int) $module['showtitle'],
                'params'     => $params,
                'client_id'  => 0,
                'language'   => '*',
                'assignment' => $assignment,
                'assigned'   => $assigned,
            ];

            if (!$model->save($record)) {
                throw new RuntimeException($module['title'] . ': ' . $model->getError());
            }

            $created++;
        }

        return $created;
    }

    private function createMenus(): int
    {
        $data        = $this->getDataset();
        $categoryIds = $this->loadCategoryIds($data['categories']);
        $articleIds  = $this->loadArticleIds($data['articles']);
        $contactIds  = $this->loadContactIds($data['contacts']);
        $menuTypes   = $this->createMenuTypes($data['menu_types']);
        $model       = $this->getApplication()->bootComponent('com_menus')->getMVCFactory()
            ->createModel('Item', 'Administrator', ['ignore_request' => true]);
        $ids        = [];
        $menus      = $data['menus'];

        usort(
            $menus,
            static fn (array $left, array $right): int => [(int) $left['level'], (int) $left['lft']]
                <=> [(int) $right['level'], (int) $right['lft']]
        );

        foreach ($menus as $menu) {
            if ($menu['type'] === 'alias') {
                continue;
            }

            $id = $this->createMenuItem($model, $menu, $menuTypes, $ids, $categoryIds, $articleIds, $contactIds);
            $ids[(int) $menu['id']] = $id;
        }

        foreach ($menus as $menu) {
            if ($menu['type'] !== 'alias') {
                continue;
            }

            $sourceTargetId = $this->aliasTargetId($menu['link'], $menu['params']);

            if (!isset($ids[$sourceTargetId])) {
                continue;
            }

            $id = $this->createMenuItem(
                $model,
                $menu,
                $menuTypes,
                $ids,
                $categoryIds,
                $articleIds,
                $contactIds,
                $ids[$sourceTargetId]
            );
            $ids[(int) $menu['id']] = $id;
        }

        return count($data['menus']) + count($data['menu_types'])
            + $this->createModules($data['modules'], $menuTypes, $ids, $categoryIds, $articleIds);
    }

    private function createMenuTypes(array $menuTypes): array
    {
        $table = new \Joomla\Component\Menus\Administrator\Table\MenuTypeTable($this->getDatabase());
        $types = [];

        foreach ($menuTypes as $menuType) {
            $sourceType = (string) $menuType['menutype'];
            $targetType = 'egov-' . substr($sourceType, 0, 19);
            $query      = $this->getDatabase()->getQuery(true)
                ->select($this->getDatabase()->quoteName('menutype'))
                ->from($this->getDatabase()->quoteName('#__menu_types'))
                ->where($this->getDatabase()->quoteName('menutype') . ' = :menutype')
                ->bind(':menutype', $targetType);

            $existing = $this->getDatabase()->setQuery($query)->loadResult();

            if ($existing === null) {
                $table->reset();
                $bound = $table->bind([
                    'id'          => 0,
                    'menutype'    => $targetType,
                    'title'       => 'eGov - ' . $menuType['title'],
                    'description' => $menuType['description'],
                ]);

                if (!$bound || !$table->check() || !$table->store()) {
                    throw new RuntimeException($table->getError());
                }
            }

            $types[$sourceType] = $targetType;
        }

        return $types;
    }

    private function createMenuItem(
        \Joomla\Component\Menus\Administrator\Model\ItemModel $model,
        array $menu,
        array $menuTypes,
        array $createdIds,
        array $categoryIds,
        array $articleIds,
        array $contactIds,
        ?int $aliasTargetId = null
    ): int {
        $marker = $this->marker('menu', $menu['id']);
        $id     = $this->findMarkerId('#__menu', 'note', $marker);
        $link   = $aliasTargetId === null
            ? $this->rewriteMenuLink($menu, $categoryIds, $articleIds, $contactIds, $createdIds)
            : 'index.php?Itemid=' . $aliasTargetId;

        if ($id !== null) {
            $title = (string) $menu['title'];
            $note = trim((string) ($menu['note'] ?? ''));
            $storedNote = $note === '' ? $marker : $marker . "\n" . $note;
            $query = $this->getDatabase()->getQuery(true)
                ->update($this->getDatabase()->quoteName('#__menu'))
                ->set($this->getDatabase()->quoteName('title') . ' = :title')
                ->set($this->getDatabase()->quoteName('link') . ' = :link')
                ->set($this->getDatabase()->quoteName('note') . ' = :note')
                ->where($this->getDatabase()->quoteName('id') . ' = :id')
                ->bind(':title', $title)
                ->bind(':link', $link)
                ->bind(':note', $storedNote)
                ->bind(':id', $id);
            $this->getDatabase()->setQuery($query)->execute();
            $this->ensureMenuHome($model, $id, $menu);

            return $id;
        }

        $sourceParentId = (int) $menu['parent_id'];
        $parentId       = $sourceParentId === 1 ? 1 : ($createdIds[$sourceParentId] ?? 1);
        $option          = $this->linkOption($link);
        $componentId     = $menu['type'] === 'component' && $option !== ''
            ? (int) ComponentHelper::getComponent($option)->id
            : 0;

        $record = [
            'id'               => 0,
            'menutype'         => $menuTypes[$menu['menutype']],
            'title'            => $menu['title'],
            'alias'            => $this->safeAlias($menu['alias']),
            'note'             => (($note = trim((string) ($menu['note'] ?? ''))) !== '')
                ? $marker . "\n" . $note
                : $marker,
            'link'             => $link,
            'type'             => $menu['type'],
            'published'        => 1,
            'parent_id'        => $parentId,
            'component_id'     => $componentId,
            'browserNav'       => (int) $menu['browserNav'],
            'access'           => 1,
            'img'              => $menu['img'],
            'template_style_id' => 0,
            'params'           => $this->rewriteMenuParams(
                $menu,
                $this->jsonArray($menu['params'], 'menu parameters'),
                $categoryIds
            ),
            'home'             => (int) ($menu['home'] ?? 0) === 1 ? 1 : 0,
            'language'         => '*',
            'client_id'        => 0,
        ];

        if (!$model->save($record)) {
            throw new RuntimeException($menu['title'] . ': ' . $model->getError());
        }

        return (int) $model->getState('item.id');
    }

    private function ensureMenuHome(
        \Joomla\Component\Menus\Administrator\Model\ItemModel $model,
        int $menuId,
        array $menu
    ): void {
        if ((int) ($menu['home'] ?? 0) !== 1) {
            return;
        }

        $query = $this->getDatabase()->getQuery(true)
            ->select($this->getDatabase()->quoteName('home'))
            ->from($this->getDatabase()->quoteName('#__menu'))
            ->where($this->getDatabase()->quoteName('id') . ' = :id')
            ->bind(':id', $menuId);

        if ((int) $this->getDatabase()->setQuery($query)->loadResult() === 1) {
            return;
        }

        $ids = [$menuId];

        if (!$model->setHome($ids, 1)) {
            throw new RuntimeException('Could not set default page for menu item ' . $menu['title']);
        }

        $query = $this->getDatabase()->getQuery(true)
            ->select($this->getDatabase()->quoteName('home'))
            ->from($this->getDatabase()->quoteName('#__menu'))
            ->where($this->getDatabase()->quoteName('id') . ' = :id')
            ->bind(':id', $menuId);

        if ((int) $this->getDatabase()->setQuery($query)->loadResult() !== 1) {
            throw new RuntimeException('Could not set default page for menu item ' . $menu['title']);
        }
    }

    private function rewriteMenuLink(
        array $menu,
        array $categoryIds,
        array $articleIds,
        array $contactIds,
        array $createdMenuIds
    ): string
    {
        $link        = (string) $menu['link'];
        $parts       = parse_url($link);
        $queryString = is_array($parts) ? ($parts['query'] ?? '') : '';
        $query        = [];

        if ($queryString !== '') {
            parse_str($queryString, $query);
        }

        $originalQuery = $query;
        $option        = (string) ($query['option'] ?? '');

        if ($option === 'com_content' || $option === 'com_contact') {
            $view = (string) ($query['view'] ?? '');
            $id   = isset($query['id']) ? (int) $query['id'] : null;

            if ($id !== null && $option === 'com_content' && $view === 'article') {
                $query['id'] = $articleIds[$id]
                    ?? throw new RuntimeException('Menu item references missing article ' . $id);
            } elseif ($id !== null && $option === 'com_content' && in_array($view, ['category', 'categories'], true)) {
                $query['id'] = $categoryIds[$id]
                    ?? throw new RuntimeException('Menu item references missing category ' . $id);
            } elseif ($id !== null && $option === 'com_contact' && $view === 'contact') {
                $query['id'] = $contactIds[$id]
                    ?? throw new RuntimeException('Menu item references missing contact ' . $id);
            } elseif ($id !== null && $option === 'com_contact' && in_array($view, ['category', 'categories'], true)) {
                $query['id'] = $categoryIds[$id]
                    ?? throw new RuntimeException('Menu item references missing contact category ' . $id);
            }

            if ($option === 'com_content' && isset($query['catid'])) {
                $sourceCategoryId = (int) $query['catid'];
                $query['catid']   = $categoryIds[$sourceCategoryId]
                    ?? throw new RuntimeException('Menu item references missing category ' . $sourceCategoryId);
            }
        }

        if (isset($query['Itemid']) && isset($createdMenuIds[(int) $query['Itemid']])) {
            $query['Itemid'] = $createdMenuIds[(int) $query['Itemid']];
        }

        if ($query !== $originalQuery) {
            $path = is_array($parts) ? ($parts['path'] ?? 'index.php') : 'index.php';
            $link = $path . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);

            if (is_array($parts) && isset($parts['fragment'])) {
                $link .= '#' . $parts['fragment'];
            }
        }

        return $link;
    }

    private function rewriteMenuParams(array $menu, array $params, array $categoryIds): array
    {
        if (
            !in_array($this->linkOption((string) $menu['link']), ['com_content', 'com_contact'], true)
            || !isset($params['catid'])
        ) {
            return $params;
        }

        $sourceCategoryId = (int) $params['catid'];
        $params['catid']  = $categoryIds[$sourceCategoryId]
            ?? throw new RuntimeException('Menu item references missing category ' . $sourceCategoryId);

        return $params;
    }

    private function loadCategoryIds(array $categories): array
    {
        $ids = [];

        foreach ($categories as $category) {
            $sourceId = (int) $category['id'];
            $marker   = $this->marker('category', $sourceId);
            $id       = $this->findMarkerId('#__categories', 'note', $marker);

            if ($id === null && $category['alias'] === 'uncategorised') {
                $id = $this->findDefaultCategoryId($category['extension']);
            }

            if ($id !== null) {
                $ids[$sourceId] = $id;
            }
        }

        return $ids;
    }

    private function filterCategories(array $categories, array $extensions): array
    {
        return array_values(array_filter(
            $categories,
            static fn (array $category): bool => in_array($category['extension'], $extensions, true)
        ));
    }

    private function loadArticleIds(array $articles): array
    {
        $ids = [];

        foreach ($articles as $article) {
            $id = $this->findMarkerId('#__content', 'note', $this->marker('article', $article['id']));

            if ($id !== null) {
                $ids[(int) $article['id']] = $id;
            }
        }

        return $ids;
    }

    private function loadContactIds(array $contacts): array
    {
        $ids = [];

        foreach ($contacts as $contact) {
            $id = $this->findMarkerId('#__contact_details', 'alias', $this->contactAlias($contact));

            if ($id !== null) {
                $ids[(int) $contact['id']] = $id;
            }
        }

        return $ids;
    }

    private function categoryId(int $sourceId, array $categoryIds): int
    {
        if (!isset($categoryIds[$sourceId])) {
            throw new RuntimeException('Missing migrated category ' . $sourceId);
        }

        return $categoryIds[$sourceId];
    }

    private function findDefaultCategoryId(string $extension): int
    {
        $defaultAlias = 'uncategorised';
        $query = $this->getDatabase()->getQuery(true)
            ->select($this->getDatabase()->quoteName('id'))
            ->from($this->getDatabase()->quoteName('#__categories'))
            ->where($this->getDatabase()->quoteName('extension') . ' = :extension')
            ->where($this->getDatabase()->quoteName('alias') . ' = :alias')
            ->where($this->getDatabase()->quoteName('published') . ' = 1')
            ->bind(':extension', $extension)
            ->bind(':alias', $defaultAlias);
        $id = $this->getDatabase()->setQuery($query)->loadResult();

        if ($id === null) {
            throw new RuntimeException('Could not find the default category for ' . $extension);
        }

        return (int) $id;
    }

    private function findMarkerId(string $table, string $column, string $marker): ?int
    {
        $query = $this->getDatabase()->getQuery(true)
            ->select($this->getDatabase()->quoteName('id'))
            ->from($this->getDatabase()->quoteName($table))
            ->where($this->getDatabase()->quoteName($column) . ' = :marker')
            ->bind(':marker', $marker);
        $id = $this->getDatabase()->setQuery($query)->loadResult();

        return $id === null ? null : (int) $id;
    }

    private function findModuleMarkerId(string $marker): ?int
    {
        $pattern = '%' . $marker . '%';
        $query = $this->getDatabase()->getQuery(true)
            ->select($this->getDatabase()->quoteName('id'))
            ->from($this->getDatabase()->quoteName('#__modules'))
            ->where($this->getDatabase()->quoteName('note') . ' LIKE :marker')
            ->bind(':marker', $pattern);
        $id = $this->getDatabase()->setQuery($query)->loadResult();

        return $id === null ? null : (int) $id;
    }

    private function removeLegacyModuleStyle(int $moduleId): void
    {
        $query = $this->getDatabase()->getQuery(true)
            ->select($this->getDatabase()->quoteName('params'))
            ->from($this->getDatabase()->quoteName('#__modules'))
            ->where($this->getDatabase()->quoteName('id') . ' = :moduleId')
            ->bind(':moduleId', $moduleId);
        $params = $this->getDatabase()->setQuery($query)->loadResult();

        if ($params === null) {
            return;
        }

        $decoded = json_decode((string) $params, true, 512, JSON_THROW_ON_ERROR);

        if (!in_array((string) ($decoded['style'] ?? ''), ['0', '_:default', 'default'], true)) {
            return;
        }

        unset($decoded['style']);
        $serializedParams = json_encode($decoded, JSON_THROW_ON_ERROR);
        $query = $this->getDatabase()->getQuery(true)
            ->update($this->getDatabase()->quoteName('#__modules'))
            ->set($this->getDatabase()->quoteName('params') . ' = :params')
            ->where($this->getDatabase()->quoteName('id') . ' = :moduleId')
            ->bind(':params', $serializedParams)
            ->bind(':moduleId', $moduleId);
        $this->getDatabase()->setQuery($query)->execute();
    }

    private function getDataset(): array
    {
        if ($this->dataset !== null) {
            return $this->dataset;
        }

        $path    = dirname(__DIR__, 2) . '/data/egov.json';
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('Could not read the eGov sample data file.');
        }

        $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        foreach (['categories', 'tags', 'articles', 'banners', 'modules', 'contacts', 'menu_types', 'menus'] as $key) {
            if (!isset($data[$key]) || !is_array($data[$key])) {
                throw new RuntimeException('The eGov sample data file has no valid "' . $key . '" list.');
            }
        }

        return $this->dataset = $data;
    }

    private function jsonArray(?string $json, string $field): array
    {
        if ($json === null || $json === '') {
            return [];
        }

        $value = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($value)) {
            throw new RuntimeException('Invalid JSON in ' . $field . '.');
        }

        return $value;
    }

    private function validDate(?string $date): ?string
    {
        return $date === null || $date === '' || str_starts_with($date, '0000-00-00')
            ? null
            : $date;
    }

    private function safeAlias(string $alias): string
    {
        return trim($alias);
    }

    private function contactAlias(array $contact): string
    {
        return 'egov-' . (int) $contact['id'] . '-' . $this->safeAlias($contact['alias']);
    }

    private function marker(string $kind, int|string $sourceId): string
    {
        return 'sampledata-egov:' . $kind . ':' . $sourceId;
    }

    private function linkOption(string $link): string
    {
        parse_str((string) parse_url($link, PHP_URL_QUERY), $query);

        return (string) ($query['option'] ?? '');
    }

    private function aliasTargetId(string $link, ?string $params = null): int
    {
        parse_str((string) parse_url($link, PHP_URL_QUERY), $query);

        if (!empty($query['Itemid'])) {
            return (int) $query['Itemid'];
        }

        $menuParams = $this->jsonArray($params, 'menu parameters');

        return (int) ($menuParams['aliasoptions'] ?? 0);
    }
}
