<?php

/**
 * @package     Joomlagovbr
 * @subpackage  tmpl_padraogoverno01
 *
 * @copyright   Copyright (C) 2013 Comunidade Joomla Calango e Grupo de Trabalho de Ministérios
 * @license     GNU General Public License version 2
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;

class TmplPadraoGoverno01Helper
{
	static function init(&$tmpl)
	{
		self::addHeaders();
		self::clearDefaultScripts($tmpl);
		self::setGenerator($tmpl->params->get('meta_generator', ''));

		$user = Factory::getApplication()->getIdentity();
		$clear_default_javascript = $tmpl->params->get('clear_default_javascript', 0);
		if ($user->guest && $clear_default_javascript == 1)
			self::clearDefaultScripts($tmpl);

		if ($tmpl->params->get('allow_set_color', 0) == 0)
			return;

		$cor = Factory::getApplication()->getInput()->get('cor', '');
		if ($cor == 'verde' || $cor == 'azul' || $cor == 'branco' || $cor == 'amarelo')
			$tmpl->params->set('cor', $cor);
	}

	static function getScripts(&$tmpl)
	{
		return array('scripts' => array(), 'script' => array());
	}

	static function clearDefaultScripts(&$tmpl, $return = false)
	{
		return $return ? array('scripts' => array(), 'script' => array()) : null;
	}

	static function getJqueryScripts(&$tmpl)
	{
		?>
		<script src="<?php echo $tmpl->baseurl; ?>/templates/<?php echo $tmpl->template; ?>/js/jquery.min.js" type="text/javascript"></script><noscript>&nbsp;
			<!-- item para fins de acessibilidade --></noscript>
		<script src="<?php echo $tmpl->baseurl; ?>/templates/<?php echo $tmpl->template; ?>/js/jquery-noconflict.min.js" type="text/javascript"></script><noscript>&nbsp;
			<!-- item para fins de acessibilidade --></noscript>
	<?php
		}

		static function getTemplateMainScripts(&$tmpl)
		{
			?>
		<script src="<?php echo $tmpl->baseurl; ?>/templates/<?php echo $tmpl->template; ?>/bootstrap/js/bootstrap.min.js" type="text/javascript"></script><noscript>&nbsp;
			<!-- item para fins de acessibilidade --></noscript>
		<script src="<?php echo $tmpl->baseurl; ?>/templates/<?php echo $tmpl->template; ?>/js/jquery.cookie.min.js" type="text/javascript"></script><noscript>&nbsp;
			<!-- item para fins de acessibilidade --></noscript>
		<script src="<?php echo $tmpl->baseurl; ?>/templates/<?php echo $tmpl->template; ?>/js/template.min.js" type="text/javascript"></script><noscript>&nbsp;
			<!-- item para fins de acessibilidade --></noscript>
	<?php
		}

		static function beforeHead($param, &$tmpl)
		{
			if ($tmpl->params->get($param, 'footer') == 'before_head') {
				return true;
			}
			return false;
		}

		static function afterHead($param, &$tmpl)
		{
			if ($tmpl->params->get($param, 'footer') == 'after_head') {
				return true;
			}
			return false;
		}

		static function inFooter($param, &$tmpl)
		{
			if ($tmpl->params->get($param, 'footer') == 'footer') {
				return true;
			}
			return false;
		}

		static function getBarra2014Script(&$tmpl)
		{
			?>
		<script src="<?php echo $tmpl->params->get('endereco_js_barra2014'); ?>" type="text/javascript"></script><noscript>&nbsp;
			<!-- item para fins de acessibilidade --></noscript>
		<?php
			}

			static function getFontStyle(&$tmpl)
			{
				if ($tmpl->params->get('font_style_url', '') != 'NENHUM') :
					?>
			<link href='<?php echo str_replace(array('{SITE}', '{LOCALFONT}'), array(substr(Uri::root(), 0, -1), Uri::root() . 'templates/' . $tmpl->template . '/css/fontes.css'), $tmpl->params->get('font_style_url', 'http://fonts.googleapis.com/css?family=Open+Sans:400italic,400,600,800,700')); ?>' rel='stylesheet' type='text/css'>
		<?php
				endif;
			}

			static function getIconsStyle(&$tmpl)
			{
				if ($tmpl->params->get('icon_style', 'bitmap-portal-brasil') == 'bitmap-portal-brasil') :
					?>
			<link rel="stylesheet" href="<?php echo $tmpl->baseurl; ?>/templates/<?php echo $tmpl->template; ?>/css/icones-bmp-<?php echo $tmpl->params->get('cor', 'verde'); ?>.css" type='text/css' />
<?php
		endif;
	}

	static function getActiveItemid()
	{
		return Factory::getApplication()->getMenu()->getActive();
	}

	static function isBootstrapCssNeededForComponent()
	{
		$input  = Factory::getApplication()->getInput();
		$option = $input->get('option', '');
		$layout = $input->get('layout', '');
		$tmpl   = $input->get('tmpl', '');
		$view   = $input->get('view', '');

		if ($tmpl != 'component')
			return false;

		//inclusao de artigos em frontend
		if ($option == 'com_content' && ($layout == 'modal' || $layout == 'pagebreak'))
			return true;

		// inclusao de imagens em frontend
		if ($option == 'com_media' && ($view == 'imagesList' || $view == 'images'))
			return true;

		return false;
	}

	static function getItemidParam($activeItemid, $param)
	{
		if (!$activeItemid)
			return '';

		return $activeItemid->getParams()->get($param);
	}

	static function getPageClass($activeItemid, $only_class = false, $pageclass = false)
	{
		$class = self::getItemidParam($activeItemid, 'pageclass_sfx');

		if ($only_class)
			return $class;

		if (!empty($class) && $pageclass)
			$class = 'pagina-' . $class;

		if (!empty($class))
			$class = 'class="' . $class . '"';
		else
			$class = '';

		return $class;
	}

	static function getPagePositionPreffix($activeItemid)
	{
		$pos_preffix = self::getPageClass($activeItemid, true);
		if (empty($pos_preffix)) {
			$jinput = Factory::getApplication()->getInput();
			$option = $jinput->get('option', '', 'string');
			$view   = $jinput->get('view', '', 'string');
			$pos_preffix = $option . '-' . $view;
		} else {
			$pos_preffix = explode(' ', $pos_preffix);
			$pos_preffix = $pos_preffix[0];
		}
		return $pos_preffix;
	}

	static function isOnlyModulesPage()
	{
		$jinput = Factory::getApplication()->getInput();
		$option = $jinput->get('option', '', 'string');

		//informar aqui componentes que desejar utilizar para páginas internas de capa, que exibirão somente modulos:
		$onlyModules = array('com_blankcomponent', 'NOME_OUTRO_COMPONENTE');

		if (in_array($option, $onlyModules))
			return true;

		return false;
	}

	static function loadModuleByPosition($position = NULL, $attribs = array(), $modules = NULL) //self::loadModuleByPosition('')
	{
		if (is_null($modules))
			$modules = ModuleHelper::getModules($position);
		else if (is_null($position))
			return;

		foreach ($modules as $k => $mod) :
			if (count($attribs) > 0) {
				//correcoes utilizadas para menu de redes sociais, no rodape, por exemplo
				if (@$attribs['replaceHTMLentities'] == '1') {
					$mod = ModuleHelper::renderModule($mod, $attribs);
					$mod = str_replace(array('&lt;', '&gt;', '<i', 'i>'), array('<', '>', '<span', 'span>'), $mod);
					echo $mod;
				} else
					echo ModuleHelper::renderModule($mod, $attribs);
			} else
				echo ModuleHelper::renderModule($mod);

		endforeach;
	}

	static function getModules($position = NULL)
	{
		if (is_null($position))
			return array();

		$modules = ModuleHelper::getModules($position);
		return $modules;
	}

	static function hasMessage()
	{
		$message = Factory::getApplication()->getMessageQueue();

		if (count($message) > 0) {
			if (count($message) == 1 && is_null($message[0]['message']))
				return false;

			return true;
		}

		return false;
	}

	static function debug($preffix = '', $active_item = 0)
	{
		if (Factory::getApplication()->getConfig()->get('debug') == 1) {
			// var_dump($active_item);
			echo '<strong>Debug de template</strong><br />';
			echo '<strong>Prefixo de posicoes de modulo:</strong> ' . $preffix . '<br />';
			echo '<strong>ID Item de menu ativo:</strong> ' . $active_item->id . '<br />';
			echo '<strong>LINK Item de menu ativo:</strong> ' . $active_item->link . '<br />';
		}
	}

	static function addHeaders()
	{
		//inclusao de cabecalho para correcao de possivel problema de compatibilidade para IE9 abaixo
		header('X-UA-Compatible: IE=edge,chrome=1');
	}

	static function setGenerator($generator = '')
	{
		Factory::getApplication()->getDocument()->setGenerator($generator);
	}
}
