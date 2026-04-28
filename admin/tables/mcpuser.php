<?php
defined('_JEXEC') or die;

class SamcpserverTableMcpuser extends JTable
{
    public function __construct(&$db)
    {
        parent::__construct('#__samcpserver_users', 'id', $db);
    }

    public function check()
    {
        if (empty($this->token))
        {
            $this->token = $this->generateToken();
        }

        if (empty($this->created))
        {
            $this->created = JFactory::getDate()->toSql();
        }

        // Verificar que no exista ya un token para ese usuario (excepto el propio registro)
        $db    = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('id')
            ->from($db->quoteName('#__samcpserver_users'))
            ->where($db->quoteName('joomla_user_id') . ' = ' . (int) $this->joomla_user_id);

        if ($this->id)
        {
            $query->where($db->quoteName('id') . ' != ' . (int) $this->id);
        }

        $db->setQuery($query);
        $existing = $db->loadResult();

        if ($existing)
        {
            $this->setError(JText::_('COM_SAMCPSERVER_ERROR_USER_EXISTS'));
            return false;
        }

        return true;
    }

    public function generateToken()
    {
        return bin2hex(random_bytes(32)); // 64 chars hex
    }
}
