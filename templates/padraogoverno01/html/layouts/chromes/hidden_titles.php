<?php

defined('_JEXEC') or die;

$module      = $displayData['module'];
$headerLevel = (int) ($displayData['attribs']['headerLevel'] ?? 2);
$headerLevel = $headerLevel > 0 ? $headerLevel : 2;

if ((string) $module->content === '') {
	return;
}
?>
<h<?php echo $headerLevel; ?> class="hide"><?php echo htmlspecialchars($module->title, ENT_QUOTES, 'UTF-8'); ?></h<?php echo $headerLevel; ?>>
<?php echo $module->content; ?>
