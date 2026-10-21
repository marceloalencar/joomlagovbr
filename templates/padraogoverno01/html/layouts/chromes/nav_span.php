<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$module  = $displayData['module'];
$params  = $displayData['params'];

if ((string) $module->content === '') {
	return;
}

$moduleClass = trim((string) $params->get('moduleclass_sfx', ''));
$class       = trim($moduleClass . ' ' . $params->get('class_sfx', ''));
$headerLevel = (int) ($displayData['attribs']['headerLevel'] ?? 2);
$headerLevel = $headerLevel > 0 ? $headerLevel : 2;
$hideTitle   = $moduleClass === 'menu-de-apoio';
$showIcon    = strpos($moduleClass . ' ' . $params->get('class_sfx', ''), 'show-icon') === false;
?>
<nav<?php echo $class !== '' ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
	<h<?php echo $headerLevel; ?><?php echo $hideTitle ? ' class="hide"' : ''; ?>>
		<?php echo htmlspecialchars($module->title, ENT_QUOTES, 'UTF-8'); ?>
		<?php if (!$hideTitle && $showIcon) : ?>
			<i class="icon-chevron-down visible-phone visible-tablet pull-right"></i>
		<?php endif; ?>
	</h<?php echo $headerLevel; ?>>
	<?php echo $module->content; ?>
</nav>
