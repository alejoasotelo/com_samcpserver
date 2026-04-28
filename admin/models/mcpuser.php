<?php
defined('_JEXEC') or die;

class McpserverModelMcpuser extends JModelAdmin
{
    protected $text_prefix = 'COM_MCPSERVER';

    public function getTable($type = 'Mcpuser', $prefix = 'McpserverTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            'com_mcpserver.mcpuser',
            'mcpuser',
            array('control' => 'jform', 'load_data' => $loadData)
        );

        if (empty($form))
        {
            return false;
        }

        return $form;
    }

    protected function loadFormData()
    {
        $app  = JFactory::getApplication();
        $data = $app->getUserState('com_mcpserver.edit.mcpuser.data', array());

        if (empty($data))
        {
            $data = $this->getItem();
        }

        return $data;
    }

    protected function prepareTable($table)
    {
        if (empty($table->id))
        {
            $table->token   = $table->generateToken();
            $table->created = JFactory::getDate()->toSql();
        }
    }

    public function regenerateToken($id)
    {
        $table = $this->getTable();

        if (!$table->load($id))
        {
            $this->setError(JText::_('COM_MCPSERVER_ERROR_NOT_FOUND'));
            return false;
        }

        $table->token = $table->generateToken();

        if (!$table->store())
        {
            $this->setError($table->getError());
            return false;
        }

        return true;
    }

    /**
     * Devuelve la URL MCP completa para un token dado
     */
    public static function getMcpUrl($token)
    {
        $uri = JUri::root();
        return rtrim($uri, '/') . '/index.php?option=com_mcpserver&task=mcp&token=' . $token;
    }
}
