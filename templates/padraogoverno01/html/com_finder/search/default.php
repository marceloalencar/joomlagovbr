<?php

/**
 * Override de com_finder (Joomla 6.2) - substitui o antigo com_search/default.php.
 * Mantém o layout antigo: coluna principal (span9) com busca + resultados e
 * coluna lateral (span3) com filtros, ordenação e itens por página.
 * O default_form.php do core NÃO é usado: o formulário é montado aqui.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var \Joomla\Component\Finder\Site\View\Search\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useStyle('com_finder.finder')->useScript('com_finder.finder');

// Autocomplete desabilitado: o bloco do core (awesomplete + script option 'finder-search') foi removido.

$hasResults = $this->query->search === true && !empty($this->results);

// Itens por página: monta as URLs a partir da URL atual
$limits       = [5, 10, 15, 20, 25, 30, 50, 100];
$currentLimit = (int) $this->pagination->limit;
?>
<div class="search com-finder finder<?php echo $this->pageclass_sfx; ?>">
	<h1 class="documentFirstHeading">
		<?php if ($this->escape($this->params->get('page_heading'))) : ?>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		<?php else : ?>
			<?php echo $this->escape($this->params->get('page_title')); ?>
		<?php endif; ?>
	</h1>

	<form id="searchForm" action="<?php echo Route::_($this->query->toUri()); ?>" method="get" class="js-finder-searchform">
		<?php echo $this->getFields(); ?>

		<div class="row-fluid">
			<div class="span9">
				<div class="row-fluid">
					<fieldset class="word">
						<legend><?php echo Text::_('COM_FINDER_SEARCH_TERMS'); ?></legend>

						<label for="q" class="hide"><?php echo Text::_('COM_FINDER_SEARCH_TERMS'); ?></label>

						<div class="input-append">
							<input type="text" name="q" id="q" size="30" value="<?php echo $this->escape($this->query->input); ?>" class="js-finder-search-query input-xxlarge">
							<button type="submit" class="btn"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
						</div>
					</fieldset>

					<?php if ($this->query->search === true) : ?>
						<div id="search-results" class="com-finder__results">
							<?php echo $this->loadTemplate('results'); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="span3">
				<?php if ($hasResults && $this->params->get('show_sort_order', 0) && !empty($this->sortOrderFields)) : ?>
					<fieldset class="fieldset-ordering">
						<legend><?php echo Text::_('COM_FINDER_SORT_BY'); ?></legend>
						<label for="ordering" class="ordering hide"><?php echo Text::_('COM_FINDER_SORT_BY'); ?></label>
						<select id="ordering" onchange="window.location.href = this.value;">
							<?php foreach ($this->sortOrderFields as $field) : ?>
								<option value="<?php echo $this->escape(Route::_($field->url)); ?>"<?php echo $field->active ? ' selected="selected"' : ''; ?>>
									<?php echo $this->escape($field->label); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</fieldset>
				<?php endif; ?>

				<?php if ($this->params->get('show_advanced', 1)) : ?>
					<fieldset class="only js-finder-advanced">
						<legend><?php echo Text::_('COM_FINDER_SEARCH_ADVANCED_LEGEND'); ?></legend>
						<div id="finder-filter-window" class="com-finder__filter">
							<?php echo HTMLHelper::_('filter.select', $this->query, $this->params); ?>
						</div>
					</fieldset>
				<?php endif; ?>

				<br />
				<p class="pull-right">
					<button type="submit" class="btn"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				</p>

				<?php if ($hasResults && $this->params->get('show_pagination_limit', 1)) : ?>
					<fieldset class="fieldset-limitbox">
						<legend><?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?></legend>
						<label for="limit" class="hide"><?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?></label>
						<select id="limit" onchange="window.location.href = this.value;">
							<?php foreach ($limits as $n) : ?>
								<?php
								$uri = clone Uri::getInstance();
								$uri->setVar('limit', $n);
								$uri->delVar('limitstart');
								?>
								<option value="<?php echo $this->escape($uri->toString(['path', 'query'])); ?>"<?php echo $n === $currentLimit ? ' selected="selected"' : ''; ?>>
									<?php echo $n; ?>
								</option>
							<?php endforeach; ?>
						</select>
					</fieldset>
				<?php endif; ?>
			</div>
		</div>
	</form>
</div>
