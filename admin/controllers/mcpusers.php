<?php
defined('_JEXEC') or die;

class McpserverControllerMcpusers extends JControllerAdmin
{
    public function getModel($name = 'Mcpuser', $prefix = 'McpserverModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}
