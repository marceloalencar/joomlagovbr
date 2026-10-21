<?php

defined('_JEXEC') or die;

$module      = $displayData['module'];
$params      = $displayData['params'];
$headerLevel = (int) ($displayData['attribs']['headerLevel'] ?? 2);
$headerLevel = $headerLevel > 0 ? $headerLevel : 2;

if ((string) $module->content === '') {
	return;
}

$tagId = trim((string) $params->get('tag_id', ''));
$class = trim((string) $params->get('class_sfx', ''));
?>
<div class="row-fluid">
	<section<?php echo $tagId !== '' ? ' id="' . htmlspecialchars($tagId, ENT_QUOTES, 'UTF-8') . '"' : ''; ?><?php echo $class !== '' ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
		<?php if ($module->showtitle) : ?>
			<h<?php echo $headerLevel; ?> class="span2"><span><?php echo htmlspecialchars($module->title, ENT_QUOTES, 'UTF-8'); ?></span></h<?php echo $headerLevel; ?>>
		<?php endif; ?>
		<?php echo $module->content; ?>
	</section>
</div>
