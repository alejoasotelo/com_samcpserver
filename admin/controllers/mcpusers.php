<?php
defined('_JEXEC') or die;

class SamcpserverControllerMcpusers extends JControllerAdmin
{
    public function getModel($name = 'Mcpuser', $prefix = 'SamcpserverModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}
