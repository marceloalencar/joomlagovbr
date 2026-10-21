<?php

defined('_JEXEC') or die;

$module      = $displayData['module'];
$params      = $displayData['params'];
$headerLevel = (int) ($displayData['attribs']['headerLevel'] ?? 2);
$headerLevel = $headerLevel > 0 ? $headerLevel : 2;

if ((string) $module->content === '') {
	return;
}

$outerClass = trim((string) $params->get('class_sfx', ''));
$navClass   = trim('row ' . $params->get('moduleclass_sfx', '') . ' nav');
?>
<div<?php echo $outerClass !== '' ? ' class="' . htmlspecialchars($outerClass, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
	<nav class="<?php echo htmlspecialchars($navClass, ENT_QUOTES, 'UTF-8'); ?>">
		<?php if ($module->showtitle) : ?>
			<h<?php echo $headerLevel; ?>><?php echo htmlspecialchars($module->title, ENT_QUOTES, 'UTF-8'); ?></h<?php echo $headerLevel; ?>>
		<?php endif; ?>
		<?php echo $module->content; ?>
	</nav>
</div>
