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

$class = trim((string) $params->get('class_sfx', ''));
?>
<nav<?php echo $class !== '' ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
	<h2 class="hide"><?php echo htmlspecialchars($module->title, ENT_QUOTES, 'UTF-8'); ?></h2>
	<?php echo $module->content; ?>
</nav>
