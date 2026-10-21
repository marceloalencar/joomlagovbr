<?php

/**
 * Override de mod_finder (Joomla 6.2) - substitui o antigo mod_search/default.php (busca do portal).
 * Variáveis fornecidas pelo Dispatcher do core: $app, $module, $params, $route, $query.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// Idioma do componente
$app->getLanguage()->load('com_finder', JPATH_SITE);

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_finder');

// Autocomplete desabilitado: o bloco do core (awesomplete + script option 'finder-search') foi removido.

$wa->useScript('com_finder.finder');

$finderHelper = $app->bootModule('mod_finder', 'site')->getHelper('FinderHelper');
$value        = htmlspecialchars($app->getInput()->get('q', '', 'string'), ENT_COMPAT, 'UTF-8');
?>
<form id="mod-finder-searchform<?php echo $module->id; ?>" action="<?php echo Route::_($route); ?>" method="get" class="pull-right js-finder-searchform" aria-label="Buscar no portal">
	<fieldset>
		<legend class="hide">Busca</legend>
		<h2 class="hidden">Buscar no portal</h2>
		<div class="input-append">
			<input type="text" name="q" id="portal-searchbox-field" class="searchField js-finder-search-query" value="<?php echo $value; ?>" placeholder="Buscar no portal" aria-label="Buscar no portal" title="Buscar no portal">
			<button type="submit" class="btn searchButton" aria-label="Buscar"><span class="hide">Buscar</span><i class="icon-search"></i></button>
		</div>
		<?php echo $finderHelper->getHiddenFields($route); ?>
	</fieldset>
</form>
