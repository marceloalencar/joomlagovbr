<?php
/**
 * Author: Omar Muhammad
 * Copyright (C) 2012 http://omar84.com. All Rights Reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Joomla\CMS\Factory;

defined('_JEXEC') or die;

Factory::getApplication()->getDocument()->setTitle('Blank Component');

echo '<div class="container">';
echo '<p>This component lets you create a menu item that displays modules without a component view.</p>';
echo '<p>Create a menu item, select "Blank Component" as its type, then assign the modules you want to display to that menu item.</p>';
echo '</div>';