<?php
/**
 * @package     Joomlagovbr
 * @subpackage  mod_videosdestaque
 *
 * @copyright   Copyright (C) 2013 Comunidade Joomla Calango e Grupo de Trabalho de Ministérios
 * @license     GNU General Public License version 2
 */

defined('_JEXEC') or die;
?>
<div class="row-fluid chamadas-secundarias">
<?php foreach ($items as $item): ?>
	<div class="module span<?php echo (int) (12 / max(1, count($items))); ?>">
		<div class="video">
			<iframe
				title="<?php echo htmlspecialchars($item['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
				width="100%"
				height="auto"
				frameborder="0"
				allowfullscreen
				src="<?php echo htmlspecialchars($item['embed_url'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
			></iframe>
		</div>
		<h2><strong><?php echo htmlspecialchars($item['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></strong></h2>
		<p class="description"><?php echo htmlspecialchars($item['description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
	</div>
<?php endforeach; ?>
	<?php if ($text_link_footer !== '' && $url_link_footer !== ''): ?>
	<div class="outstanding-footer">
		<a href="<?php echo htmlspecialchars($url_link_footer, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" class="outstanding-link">
			<span class="text"><?php echo htmlspecialchars($text_link_footer, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></span>
            <span class="icon-box">                                          
              <i class="icon-angle-right icon-light"><span class="hide">&nbsp;</span></i>
            </span>
        </a>
    </div>  
	<?php endif; ?>
</div>