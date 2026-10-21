<?php
 /**
 * @package		
 * @subpackage	
 * @copyright	
 * @license		
 */

defined('_JEXEC') or die;

$cparams = \Joomla\CMS\Component\ComponentHelper::getParams ('com_media');
?>
<div class="contact<?php echo $this->pageclass_sfx?>">
<?php if ($this->params->get('show_page_heading')) : ?>
<h1 class="documentFirstHeading">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php endif; ?>
	
	<?php if (($this->item->name && $this->params->get('show_name') && (!$this->params->get('show_contact_list')) || ($this->params->get('show_contact_list') && count($this->contacts) < 1))) : ?>
	<div class="subtitle">
		<span class="contact-name">
			<?php echo $this->item->name; ?>
		</span>
	</div>
	<?php elseif ($this->params->get('show_contact_list') && count($this->contacts) > 1):  ?>
	<div class="subtitle">
		<form action="#" method="get" id="selectForm" class="pull-left">
			<label for="id" class="hide"><?php echo \Joomla\CMS\Language\Text::_('COM_CONTACT_SELECT_CONTACT'); ?></label>
			<?php echo \Joomla\CMS\HTML\HTMLHelper::_('select.genericlist',  $this->contacts, 'id', 'class="inputbox" onchange="document.location.href = this.value"', 'link', 'name', $this->item->link);?>
			
		</form>
		<br>
	</div>
	<br>
	<?php endif;  ?>

	<div class="row-fluid description">
		<p>
		<?php if ($this->params->get('show_contact_category') == 'show_no_link') : ?>
			<span class="contact-category">Categoria: <?php echo $this->item->category_title; ?>.</span>
		<?php endif; ?>
		<?php if ($this->params->get('show_contact_category') == 'show_with_link') : ?>
			<?php $contactLink = \Joomla\Component\Contact\Site\Helper\RouteHelper::getCategoryRoute($this->item->catid);?>
			<span class="contact-category">Categoria: <a href="<?php echo $contactLink; ?>">
				<?php echo $this->escape($this->item->category_title); ?></a>.
			</span>
		<?php endif; ?>
		<?php if ($this->item->con_position && $this->params->get('show_position')) : ?>
			<span class="contact-position"> | Cargo: <?php echo $this->item->con_position; ?></span>
		<?php endif; ?>
		</p>			
	</div>
	
	<div class="row-fluid">
		<div class="span8">
			<?php if ($this->params->get('show_email_form') && ($this->item->email_to || $this->item->user_id)) : ?>
			<div class="module">
				<div class="outstanding-header">
					<h2 class="outstanding-title"><span>Envie um e-mail</span></h2>
			 	</div>
			
			
				<?php  echo $this->loadTemplate('form');  ?>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('show_links')) : ?>
				<?php echo $this->loadTemplate('links'); ?>
			<?php endif; ?>
		</div>
		<div class="span4">
			<div class="module">
				<div class="outstanding-header">
					<h2 class="outstanding-title"><span>Contatos</span></h2>
			 	</div>
			
				<?php if ($this->item->image && $this->params->get('show_image')) : ?>
					<div class="contact-image row-fluid">
						<?php echo \Joomla\CMS\HTML\HTMLHelper::_('image', $this->item->image, \Joomla\CMS\Language\Text::_('COM_CONTACT_IMAGE_DETAILS'), array('align' => 'middle', 'class' => 'img-rounded')); ?>
					</div>
				<?php endif; ?>

				<div class="address row-fluid">
				<?php echo $this->loadTemplate('address'); ?>
				</div>
			</div>


			<?php if ($this->params->get('allow_vcard')) :	?>
			<div class="vcard row-fluid">			
				<?php echo \Joomla\CMS\Language\Text::_('COM_CONTACT_DOWNLOAD_INFORMATION_AS');?>
					<a href="<?php echo \Joomla\CMS\Router\Route::_('index.php?option=com_contact&amp;view=contact&amp;id='.$this->item->id . '&amp;format=vcf'); ?>">
					<?php echo \Joomla\CMS\Language\Text::_('COM_CONTACT_VCARD');?></a>
			</div>
			<?php endif; ?>
				
		</div>
	</div>
	<?php //comentado, pois provavelmente nao sera utilizado: ?>
	<?php /*if ($this->params->get('show_articles') && $this->item->user_id && $this->item->articles) : ?>
		<?php if ($this->params->get('presentation_style')!='plain'):?>
			<?php echo \Joomla\CMS\HTML\HTMLHelper::_($this->params->get('presentation_style').'.panel', \Joomla\CMS\Language\Text::_('JGLOBAL_ARTICLES'), 'display-articles'); ?>
			<?php endif; ?>
			<?php if  ($this->params->get('presentation_style')=='plain'):?>
			<?php echo '<h3>'. \Joomla\CMS\Language\Text::_('JGLOBAL_ARTICLES').'</h3>'; ?>
			<?php endif; ?>
			<?php echo $this->loadTemplate('articles'); ?>
	<?php endif; ?>
	<?php if ($this->params->get('show_profile') && $this->item->user_id && \Joomla\CMS\Plugin\PluginHelper::isEnabled('user', 'profile')) : ?>
		<?php if ($this->params->get('presentation_style')!='plain'):?>
			<?php echo \Joomla\CMS\HTML\HTMLHelper::_($this->params->get('presentation_style').'.panel', \Joomla\CMS\Language\Text::_('COM_CONTACT_PROFILE'), 'display-profile'); ?>
		<?php endif; ?>
		<?php if ($this->params->get('presentation_style')=='plain'):?>
			<?php echo '<h3>'. \Joomla\CMS\Language\Text::_('COM_CONTACT_PROFILE').'</h3>'; ?>
		<?php endif; ?>
		<?php echo $this->loadTemplate('profile'); ?>
	<?php endif;*/ ?>

	<?php /*if ($this->item->misc && $this->params->get('show_misc')) : ?>
		<?php if ($this->params->get('presentation_style')!='plain'){?>
			<?php echo \Joomla\CMS\HTML\HTMLHelper::_($this->params->get('presentation_style').'.panel', \Joomla\CMS\Language\Text::_('COM_CONTACT_OTHER_INFORMATION'), 'display-misc');} ?>
		<?php if ($this->params->get('presentation_style')=='plain'):?>
			<?php echo '<h3>'. \Joomla\CMS\Language\Text::_('COM_CONTACT_OTHER_INFORMATION').'</h3>'; ?>
		<?php endif; ?>
				<div class="contact-miscinfo">
					<div class="<?php echo $this->params->get('marker_class'); ?>">
						<?php echo $this->params->get('marker_misc'); ?>
					</div>
					<div class="contact-misc">
						<?php echo $this->item->misc; ?>
					</div>
				</div>
	<?php endif;*/ ?>
	<?php //fim comentado, pois provavelmente nao sera utilizado ?>

</div>
