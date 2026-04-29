<?php
defined('_JEXEC') or die;

$user = JFactory::getUser();

if (!$user->authorise('core.manage', 'com_samcpserver') && !$user->authorise('core.admin'))
{
    throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
}

$controller = JControllerLegacy::getInstance('Samcpserver');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
