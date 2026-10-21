<?php

/**
 * Override de com_finder - cada item de resultado (era parte do com_search/default_results.php).
 * Lógica de descrição/taxonomia do core 6.2 com o markup do template antigo.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Taxonomy;
use Joomla\Component\Finder\Site\Helper\RouteHelper;
use Joomla\String\StringHelper;

/** @var \Joomla\Component\Finder\Site\View\Search\HtmlView $this */
$user             = $this->getCurrentUser();
$show_description = $this->params->get('show_description', 1);
$description      = '';

if ($show_description) {
    // Quantidade de caracteres exibidos ao redor do termo
    $term_length = StringHelper::strlen($this->query->input);
    $desc_length = $this->params->get('description_length', 255);
    $pad_length  = $term_length < $desc_length ? (int) floor(($desc_length - $term_length) / 2) : 0;

    // Destaca o termo no introtext e no fulltext
    $full_description = $this->result->description;
    if (!empty($this->result->summary) && !empty($this->result->body)) {
        $full_description = Helper::parse($this->result->summary . $this->result->body);
    }

    $pos   = $term_length ? StringHelper::strpos(StringHelper::strtolower($full_description), StringHelper::strtolower($this->query->input)) : false;
    $start = ($pos && $pos > $pad_length) ? $pos - $pad_length : 0;
    $space = StringHelper::strpos($full_description, ' ', $start > 0 ? $start - 1 : 0);
    $start = ($space && $space < $pos) ? $space + 1 : $start;

    $description = HTMLHelper::_('string.truncate', StringHelper::substr($full_description, $start), $desc_length, true);
}

// Taxonomias visíveis (Category -> "Registrado em", Tag -> "Assuntos (Tags)")
$levels     = $user->getAuthorisedViewLevels();
$taxonomies = $this->params->get('show_taxonomy', 1) ? $this->result->getTaxonomy() : [];
$visible    = static function (string $type) use ($taxonomies, $levels): array {
    if (empty($taxonomies[$type])) {
        return [];
    }

    $branch = Taxonomy::getBranch($type);

    if (!($branch->state == 1 && \in_array($branch->access, $levels))) {
        return [];
    }

    $titles = [];

    foreach ($taxonomies[$type] as $node) {
        if ($node->state == 1 && \in_array($node->access, $levels)) {
            $titles[] = $node->title;
        }
    }

    return $titles;
};

$categories = array_filter($visible('Category'), static fn ($c) => strtolower($c) !== 'uncategorised');
$tags       = $visible('Tag');
$number     = (int) $this->pagination->limitstart + (int) $this->result->counter;
?>
<div class="tileItem">
	<div class="span11 tileContent">
		<h2 class="tileHeadline result-title">
			<span class="hide"><?php echo $number . '. '; ?></span>
			<?php if ($this->result->route) : ?>
				<a href="<?php echo Route::_($this->result->route); ?>"><?php echo strip_tags($this->result->title); ?></a>
			<?php else : ?>
				<?php echo strip_tags($this->result->title); ?>
			<?php endif; ?>
		</h2>

		<?php if ($show_description && $description !== '') : ?>
			<div class="description result-text"><?php echo $description; ?></div>
		<?php endif; ?>

		<div class="keywords">
			<?php if ($categories) : ?>
				<p class="result-category">Registrado em: <?php echo $this->escape(implode(', ', $categories)); ?></p>
			<?php endif; ?>

			<?php if ($tags) : ?>
				<p class="result-tags">Assuntos (Tags):
					<?php foreach ($tags as $n => $tag) : ?>
						<span>
							<a href="<?php echo Route::_(RouteHelper::getSearchRoute(null, $tag) . '&o=date&od=desc&limit=20'); ?>" class="link-categoria"><?php echo $this->escape($tag); ?></a><?php if ($n < count($tags) - 1) : ?><span class="separator">,</span><?php endif; ?>
						</span>
					<?php endforeach; ?>
				</p>
			<?php endif; ?>

			<?php if ($this->params->get('show_date', 1) && $this->result->start_date) : ?>
				<p class="result-created<?php echo $this->pageclass_sfx; ?>">
					<?php $createdDate = '<time datetime="' . HTMLHelper::_('date', $this->result->start_date, 'c') . '">' . HTMLHelper::_('date', $this->result->start_date, Text::_('DATE_FORMAT_LC3')) . '</time>'; ?>
					<?php echo Text::sprintf('JGLOBAL_CREATED_DATE_ON', $createdDate); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>
	<div class="span1 tileInfo"><?php echo $number . '. '; ?></div>
</div>
