<?php

/**
 * Override de com_finder - substitui com_search/default_results.php e default_error.php.
 * A lógica é a do core 6.2; só o markup foi trocado pelo do template antigo.
 * A ordenação foi movida para a coluna lateral (default.php).
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var \Joomla\Component\Finder\Site\View\Search\HtmlView $this */
?>
<?php // Consulta sugerida / explicada ?>
<?php if (($this->suggested && $this->params->get('show_suggested_query', 1)) || ($this->explained && $this->params->get('show_explained_query', 1))) : ?>
	<div id="search-query-explained" class="searchintro<?php echo $this->pageclass_sfx; ?>">
		<?php if ($this->suggested && $this->params->get('show_suggested_query', 1)) : ?>
			<?php $uri = Uri::getInstance($this->query->toUri()); ?>
			<?php $uri->setVar('q', $this->suggested); ?>
			<?php $linkUrl = Route::_($uri->toString(['path', 'query'])); ?>
			<?php $link = '<a href="' . $linkUrl . '">' . $this->escape($this->suggested) . '</a>'; ?>
			<p class="description"><?php echo Text::sprintf('COM_FINDER_SEARCH_SIMILAR', $link); ?></p>
		<?php elseif ($this->explained && $this->params->get('show_explained_query', 1)) : ?>
			<p class="description" role="alert">
				<?php echo Text::plural('COM_FINDER_QUERY_RESULTS', $this->total, $this->explained); ?>
			</p>
		<?php endif; ?>
	</div>
<?php endif; ?>
<?php // Sem resultados (era o default_error.php) ?>
<?php if (($this->total === 0) || ($this->total === null)) : ?>
	<div id="search-result-empty" class="error">
		<h2><?php echo Text::_('COM_FINDER_SEARCH_NO_RESULTS_HEADING'); ?></h2>
		<?php $multilang = Factory::getApplication()->getLanguageFilter() ? '_MULTILANG' : ''; ?>
		<span><?php echo Text::sprintf('COM_FINDER_SEARCH_NO_RESULTS_BODY' . $multilang, $this->escape($this->query->input)); ?></span>
	</div>
	<?php return; ?>
<?php endif; ?>
<?php // Destaque dos termos ?>
<?php if (!empty($this->query->highlight) && $this->params->get('highlight_terms', 1)) : ?>
	<?php
	$this->getDocument()->getWebAssetManager()->useScript('highlight');
	$this->getDocument()->addScriptOptions(
		'highlight',
		[[
			'class'     => 'js-highlight',
			'highLight' => array_slice($this->query->highlight, 0, 10),
		]]
	);
	?>
<?php endif; ?>
<div class="tile-list-1">
	<div id="search-result-list" class="js-highlight search-results search-results<?php echo $this->pageclass_sfx; ?>">
		<?php $this->baseUrl = Uri::getInstance()->toString(['scheme', 'host', 'port']); ?>
		<?php foreach ($this->results as $i => $result) : ?>
			<?php $this->result = &$result; ?>
			<?php $this->result->counter = $i + 1; ?>
			<?php $layout = $this->getLayoutFile($this->result->layout); ?>
			<?php echo $this->loadTemplate($layout); ?>
		<?php endforeach; ?>
	</div>
</div>

<?php if ($this->params->get('show_pagination', 1) > 0) : ?>
	<div class="pagination text-center">
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
<?php endif; ?>
<?php if ($this->params->get('show_pagination_results', 1) > 0) : ?>
	<div class="row-fluid text-center">
		<p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
	</div>
<?php endif; ?>
