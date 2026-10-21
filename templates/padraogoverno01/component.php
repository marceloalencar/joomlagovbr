<?php

/**
 * @package
 * @subpackage
 * @copyright
 * @license
 */

// No direct access.
defined('_JEXEC') or die;
require_once JPATH_SITE . '/templates/' . $this->template . '/helper.php';
TmplPadraoGoverno01Helper::init($this); //inicializacao de funcoes do template, como configuracao de cor, se alterada via get, limpeza do head padrao do joomla e outras providencias.
$active_item = TmplPadraoGoverno01Helper::getActiveItemid();
if (TmplPadraoGoverno01Helper::isBootstrapCssNeededForComponent()) {
	$webAssetManager = $this->getWebAssetManager();
	$webAssetManager
		->registerAndUseStyle('template.padraogoverno01.component.bootstrap', 'templates/padraogoverno01/bootstrap/css/bootstrap.min.css')
		->registerAndUseStyle(
			'template.padraogoverno01.component',
			'templates/padraogoverno01/css/template-' . $this->params->get('cor', 'verde') . '.css'
		);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">

<head>
	<jdoc:include type="head" />
</head>

<body class="contentpane">
	<div id="all">
		<div id="main">
			<jdoc:include type="message" />
			<jdoc:include type="component" />
		</div>
	</div>
</body>

</html>
