<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_chamadas
 *
 * @copyright   Copyright (C) 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;
?>
<?php if ($params->get('titulo_alternativo')): ?>
	<h3 class="page-header"><?php echo $params->get('titulo_alternativo')?></h3>
<?php endif; ?>

<?php if ($params->get('link_saiba_mais')): ?>
	<div>
		<a href="<?php echo $params->get('link_saiba_mais')?>">
			<?php if ($params->get('texto_saiba_mais')): ?>
				<?php echo $params->get('texto_saiba_mais')?>
			<?php else: ?>
				<?php echo Text::_('COM_CONTENT_FEED_READMORE'); ?>
			<?php endif;?>
		</a>
	</div>
<?php endif; ?>

	<?php foreach ($lista_chamadas as $lista): ?>
		<?php
			$link = $lista->link;
		?>

		<?php if ($params->get('exibir_imagem')): ?>
			<?php
			$imagem = json_decode($lista->images);
			?>
			<?php if (!empty($imagem->image_intro)): ?>
				<div>
					<a href="<?php echo $link ?>">
						<img src="<?php echo $imagem->image_intro ?>" />
					</a>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ($params->get('exibir_title')): ?>
				<<?php echo $params->get('header_tag')?> <?php if ($params->get('header_class')): echo 'class="'.$params->get('header_class').'"'; endif; ?>>
					<a href="<?php echo $link ?>" <?php if ($params->get('header_class')): echo 'class="'.$params->get('header_class').'"'; endif; ?>>
						<?php echo $lista->title ?>
					</a>
				</<?php echo $params->get('header_tag')?>>
		<?php endif; ?>
		<?php if ($params->get('chapeu') && ($lista->chapeu)): ?>
			<div>
				<div>
					<a href="<?php echo $link ?>">
						<?php echo $lista->chapeu ?>
					</a>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($params->get('exibir_introtext') && $lista->introtext): ?>
			<?php if ($params->get('limitar_caractere')): ?>
				<p><?php echo ModChamadasHelper::getIntroLimiteCaracteres((string) $lista->introtext, $params); ?></p>
			<?php else: ?>
					<p><?php echo strip_tags($lista->introtext, '<b><i><strong><u><b>') ?></p>
			<?php endif; ?>
		<?php endif; ?>
	<?php endforeach; ?>
