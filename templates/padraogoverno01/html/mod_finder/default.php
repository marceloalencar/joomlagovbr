<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_finder
 *
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$input = '<input type="text" name="q" id="portal-searchbox-field" class="searchField js-finder-search-query" value="'
	. htmlspecialchars($app->getInput()->get('q', '', 'string'), ENT_COMPAT, 'UTF-8')
	. '" placeholder="Buscar no portal" aria-label="Buscar no portal" title="Buscar no portal">';

$finderHelper = $app->bootModule('mod_finder', 'site')->getHelper('FinderHelper');
$wa           = $app->getDocument()->getWebAssetManager();

$wa->getRegistry()->addExtensionRegistryFile('com_finder');
$wa->useScript('com_finder.finder');
$wa->usePreset('awesomplete');
$app->getDocument()->addScriptOptions(
	'finder-search',
	[
		'url' => Route::_('index.php?option=com_finder&task=suggestions.suggest&format=json&tmpl=component', false),
	]
);

Text::script('MOD_FINDER_SEARCH_VALUE');
Text::script('COM_FINDER_SEARCH_FORM_LIST_LABEL');
Text::script('JLIB_JS_AJAX_ERROR_OTHER');
Text::script('JLIB_JS_AJAX_ERROR_PARSE');
?>
<form action="<?php echo Route::_($route); ?>" method="get" class="pull-right mod-finder js-finder-searchform" aria-label="Busca">
	<fieldset>
		<legend class="hide">Busca</legend>
		<h2 class="hidden">Buscar no portal</h2>
		<div class="input-append">
			<?php echo $input; ?>
			<button type="submit" class="btn searchButton" aria-label="Buscar">
				<span class="hide">Buscar</span><i class="icon-search"></i>
			</button>
		</div>
		<?php echo $finderHelper->getHiddenFields($route); ?>
	</fieldset>
</form>
