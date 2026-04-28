<?php
defined('_JEXEC') or die;

class SamcpserverModelMcpuser extends JModelAdmin
{
    protected $text_prefix = 'COM_SAMCPSERVER';

    public function getTable($type = 'Mcpuser', $prefix = 'SamcpserverTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            'com_samcpserver.mcpuser',
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
        $data = $app->getUserState('com_samcpserver.edit.mcpuser.data', array());

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
            $this->setError(JText::_('COM_SAMCPSERVER_ERROR_NOT_FOUND'));
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
        return rtrim($uri, '/') . '/index.php?option=com_samcpserver&task=mcp&token=' . $token;
    }
}
