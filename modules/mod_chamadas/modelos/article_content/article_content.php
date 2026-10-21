<?php
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

class ModeloArticle_content
{
	public function getListaModelo(Registry $params): array
	{
		$user   = Factory::getApplication()->getIdentity();
		$groups = array_map('intval', $user->getAuthorisedViewLevels());

		if ($groups === []) {
			return [];
		}

		$groupList = implode(',', $groups);
		$db       = Factory::getContainer()->get(DatabaseInterface::class);
		$nullDate = $db->getNullDate();
		$now      = Factory::getDate()->toSql();
		$query    = $db->getQuery(true);
		$query->select('cont.id, cont.catid, cont.alias, cont.created AS created_date, cont.modified AS modify_date, cont.publish_up AS publish_date, cont.metadesc');
		$query->from('#__content cont');
		$query->from('#__categories cat');

		$query->where('cont.catid = cat.id');
		$query->where('cont.state = 1');
		$query->where('cat.published = 1');
		$query->where('cont.access IN (' . $groupList . ')');
		$query->where('cat.access IN (' . $groupList . ')');
		$query->where('(cont.publish_up IS NULL OR cont.publish_up = ' . $db->quote($nullDate) . ' OR cont.publish_up <= ' . $db->quote($now) . ')');
		$query->where('(cont.publish_down IS NULL OR cont.publish_down = ' . $db->quote($nullDate) . ' OR cont.publish_down >= ' . $db->quote($now) . ')');
		$query->where('cont.language IN (' . $db->quote(Factory::getApplication()->getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		
		//Valor 1 = todos que não são destaque
		if($params->get('destaque') == 1){
			$query->where('cont.featured = 0');
		}

		//Valor 2 = somente destaque
		elseif($params->get('destaque') == 2){
			$query->where('cont.featured = 1');
		}


		//Traz o resultado do chapeu se existir
		$hatField = (string) $params->get('chapeu', '');

		if ($hatField !== '' && $hatField !== '0' && $hatField !== 'nenhum') {
			$hatField = str_starts_with($hatField, 'cont.') ? substr($hatField, 5) : $hatField;
			$allowedHatFields = ['created', 'metadesc', 'metakey', 'modified', 'publish_up', 'title'];

			if ($hatField === 'xreference') {
				$query->select($db->quote('') . ' AS chapeu');
			} elseif (!in_array($hatField, $allowedHatFields, true)) {
				throw new RuntimeException('Unsupported mod_chamadas article field: ' . $hatField);
			} else {
				$query->select($db->quoteName('cont.' . $hatField) . ' AS chapeu');
			}
		}

		//Traz o resultado do título ou não
		if($params->get('exibir_title')){
			$query->select('cont.title');
		}

		//Traz o resultado da imagem ou não: comentado para permitir overwrite.
		// if($params->get('exibir_imagem')){
			$query->select('cont.images');
		// }

		//Traz o resultado do introtext ou não
		if($params->get('exibir_introtext')){
			$query->select('cont.introtext');
		}

		if($params->get('somente_imagem')){
			$query->where('cont.images NOT LIKE \'{"image_intro":""%\'');
		}

		//obtem o valor de configuracao quando um unico artigo sera exibido...
		$id_unique_article = ($params->get('id_article_unico', '') != '')? $params->get('id_article_unico') : $params->get('id_item_unico', '');

		//se o valor para um unico artigo estiver vazio, executa as buscas e configura a consulta para as categorias ou tags
		if( empty($id_unique_article) )
		{
			//Implode nas categorias selecionadas
			$categoryIds = array_values(array_filter(
				array_map('intval', (array) $params->get('catid', [])),
				static fn (int $id): bool => $id > 0
			));

			//Verifica se irá filtrar por categoria
			if ($params->get('buscar_cat_tag') != '2' && $categoryIds !== []) {
				$categoryList = implode(',', $categoryIds);

				//Subquery para trazer os id's das categorias filhas
				if($params->get('visualizar_filho')){
					$subQuery = $db->getQuery(true);
					$subQuery->select('filho.id');
					$subQuery->from('#__categories AS pai');
					$subQuery->from('#__categories AS filho');
					$subQuery->where('pai.id IN (' . $categoryList . ')');
					$subQuery->where('filho.lft >= pai.lft');
					$subQuery->where('filho.rgt <= pai.rgt');
					$subQuery->where('filho.published = 1');
					$subQuery->where('pai.published = 1');

					//Define o nível máximo da categoria
					if((int) $params->get('nivel') > 0 && count($categoryIds) === 1){
						$subQuery->where('filho.level <= ' . (int) $params->get('nivel'));
					}

					//Filtra as categorias que deverão ser listadas.
					$query->where('cont.catid IN (' . $subQuery . ')');
				}else{
					$query->where('cont.catid IN (' . $categoryList . ')');
				}
			}

			 /* ATENÇÃO
			 * O CÓDIGO ABAIXO FUNCIONA APENAS NA VERSÃO 3.X.X
			 * NA VERSÃO 2.5 OU INFERIOR O COMANDO ABAIXO SERÁ DESCONSIDERADO
			 */

			$tagIds = array_values(array_filter(
				array_map('intval', (array) $params->get('tags', [])),
				static fn (int $id): bool => $id > 0
			));

			if ($params->get('buscar_cat_tag') != '1' && $tagIds !== []) {
				$query->from('#__contentitem_tag_map mtag');
				$query->from('#__tags tag');
				$query->where('cont.id = mtag.content_item_id');
				$query->where('tag.id = mtag.tag_id');
				$query->where('tag.published = 1');
				$query->where('tag.access IN (' . $groupList . ')');
				$query->where('tag.id IN (' . implode(',', $tagIds) . ')');
				$query->group('cont.id');
			}
		}
		else //se o valor de id_unique_article nao estiver vazio
		{
			$query->where('cont.id = ' . intval($id_unique_article) );
			$params->set('quantidade', 1);
		}

		$orderColumn = (string) $params->get('ordem', 'ordering');
		$orderDirection = strtoupper((string) $params->get('ordem_direction', 'ASC'));
		$allowedOrderColumns = ['alias', 'created', 'featured', 'hits', 'id', 'modified', 'ordering', 'publish_up', 'title'];

		if (!in_array($orderColumn, $allowedOrderColumns, true) || !in_array($orderDirection, ['ASC', 'DESC'], true)) {
			throw new RuntimeException('Invalid mod_chamadas ordering configuration.');
		}

		$query->order($db->quoteName('cont.' . $orderColumn) . ' ' . $orderDirection);

		$db->setQuery($query, 0, max(1, (int) $params->get('quantidade', 1)));
		$lista = $db->loadObjectList();

		//pre processa os itens do array para valores padrao e sobrescricao dos dados pelo modulo
		$lista_counter = count($lista);

		for ($i=0; $i < $lista_counter; $i++) {

			//chapeu e title
			if($params->get('chapeu') && $params->get('chapeu') != '0'  && $params->get('chapeu') != 'nenhum')
				$lista[$i]->chapeu = ($params->get('chapeu_item'.($i+1), '') != '')? $params->get('chapeu_item'.($i+1) ) : @$lista[$i]->chapeu;
			else
				$lista[$i]->chapeu = NULL;

			if($params->get('exibir_title')) {
				$lista[$i]->title = ($params->get('title_item'.($i+1), '') != '')? $params->get('title_item'.($i+1) ) : $lista[$i]->title;

				$tam_texto = strlen($lista[$i]->title);
				if($tam_texto > 80 ){
					$limite_palavra = strrpos(substr(strip_tags($lista[$i]->title), 0, 80), " ");
					$lista[$i]->title = trim(substr(strip_tags($lista[$i]->title), 0, $limite_palavra)).'...';
				}
			}
			else{
				$lista[$i]->title = NULL;
			}

			// DESCRICAO DO ARTIGO
			if($params->get('desc_item'.($i+1), '') != ''){
				$lista[$i]->introtext = $params->get('desc_item'.($i+1));
			}
			elseif($params->get('exibir_introtext')){
				$lista[$i]->introtext = '<p class="description">'.ModChamadasHelper::getIntroLimiteCaracteres($lista[$i]->introtext, $params).'</p>'	;
			}
			else{
				$lista[$i]->introtext = '';
			}

			// OPCOES DE IMAGEM DO ARTIGO


			if ((($params->get('exibir_imagem') && $params->get('show_image_item'.($i+1))=='') || $params->get('show_image_item'.($i+1))==1)) {
				$lista[$i]->images = json_decode($lista[$i]->images);
			}

			if( $params->get('image_item'.($i+1), '') != '') {
				$lista[$i]->image_url = $params->get('image_item'.($i+1) );
			}
			elseif((($params->get('exibir_imagem') && $params->get('show_image_item'.($i+1))=='') || $params->get('show_image_item'.($i+1))==1)) {
				$lista[$i]->image_url = @$lista[$i]->images->image_intro;
			}
			else {
				$lista[$i]->image_url = '';
			}

			if( $params->get('image_item'.($i+1).'_align', '') != '') {
				$lista[$i]->image_align = $params->get('image_item'.($i+1).'_align' );
			}
			elseif($params->get('exibir_imagem')) {
				$lista[$i]->image_align = @$lista[$i]->images->float_intro;
			}
			else {
				$lista[$i]->image_align = '';
			}

			if( $params->get('image_item'.($i+1).'_alt', '') != '') {
				$lista[$i]->image_alt = $params->get('image_item'.($i+1).'_alt' );
			}
			elseif($params->get('exibir_imagem')) {
				$lista[$i]->image_alt = @$lista[$i]->images->image_intro_alt;
			}
			else {
				$lista[$i]->image_alt = '';
			}

			if( $params->get('image_item'.($i+1).'_caption', '') != '') {
				$lista[$i]->image_caption = $params->get('image_item'.($i+1).'_caption' );
			}
			elseif($params->get('exibir_imagem')) {
				$lista[$i]->image_caption = @$lista[$i]->images->image_intro_caption;
			}
			else {
				$lista[$i]->image_caption = '';
			}

			// LINK DO ARTIGO
			$fields = array();
			$fields[] = 'url_simple_item'.($i+1);
			$fields[] = 'url_menu_item'.($i+1);
			$fields[] = 'url_article_item'.($i+1);
			$lista[$i]->link = ModChamadasHelper::getLink($params, $fields, $lista[$i]);

		}

		//retorna a lista ja processada
		return $lista;
	}
}
