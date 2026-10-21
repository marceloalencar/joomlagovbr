<?php

defined('_JEXEC') or die;

$module = $displayData['module'];
$params = $displayData['params'];

if ((string) $module->content === '') {
	return;
}

$id = trim((string) $params->get('moduleclass_sfx', ''));
?>
<div<?php echo $id !== '' ? ' id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '"' : ''; ?> class="row">
	<h2 class="hidden"><?php echo htmlspecialchars($module->title, ENT_QUOTES, 'UTF-8'); ?></h2>
	<?php echo $module->content; ?>
</div>
