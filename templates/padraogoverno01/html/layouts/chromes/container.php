<?php

defined('_JEXEC') or die;

$module      = $displayData['module'];
$params      = $displayData['params'];
$headerLevel = (int) ($displayData['attribs']['headerLevel'] ?? 2);
$headerLevel = $headerLevel > 0 ? $headerLevel : 2;

if ((string) $module->content === '') {
	return;
}

if ($module->module === 'mod_container') {
	echo $module->content;
	return;
}

$class          = trim((string) $params->get('moduleclass_sfx', ''));
$containerClass = '';
$containerPos   = strpos($class, 'container-class-');

if ($containerPos !== false) {
	$containerClass = substr($class, $containerPos);
	$containerClass = str_replace(['container-class-', '--'], ['', ' '], $containerClass);
	$class          = str_replace('container-class-', '', $class);
}

$variation = (int) $params->get('variacao', 0);

if ($variation > 0) {
	$class .= ' variacao-module-' . str_pad((string) $variation, 2, '0', STR_PAD_LEFT);
}

$title     = (string) $params->get('titulo_alternativo', '') !== ''
	? $params->get('titulo_alternativo')
	: $module->title;
$layout    = explode(':', (string) $params->get('layout'));
$showTitle = $layout[1] !== 'manchete-principal' && (bool) $module->showtitle;
$hasHeader = strpos((string) $params->get('moduleclass_sfx'), 'no-outstanding-title') === false;
?>
<div class="row-fluid module <?php echo htmlspecialchars(trim($class), ENT_QUOTES, 'UTF-8'); ?>">
	<?php if ($showTitle) : ?>
		<?php if ($hasHeader) : ?><div class="outstanding-header"><?php endif; ?>
		<h<?php echo $headerLevel; ?><?php echo $hasHeader ? ' class="outstanding-title"' : ''; ?>><span><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></span></h<?php echo $headerLevel; ?>>
		<?php if ($hasHeader) : ?></div><?php endif; ?>
	<?php endif; ?>
	<?php if ($containerClass !== '') : ?><div class="<?php echo htmlspecialchars($containerClass, ENT_QUOTES, 'UTF-8'); ?>"><?php endif; ?>
	<?php echo $module->content; ?>
	<?php if ($containerClass !== '') : ?></div><?php endif; ?>
</div>
