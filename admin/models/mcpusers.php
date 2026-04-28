<?php
defined('_JEXEC') or die;

class SamcpserverModelMcpusers extends JModelList
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'joomla_user_id', 'a.joomla_user_id',
                'enabled', 'a.enabled',
                'created', 'a.created',
                'last_used', 'a.last_used',
                'note', 'a.note',
                'username', 'u.username',
            );
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = 'u.name', $direction = 'asc')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $enabled = $this->getUserStateFromRequest($this->context . '.filter.enabled', 'filter_enabled', '', 'string');
        $this->setState('filter.enabled', $enabled);

        parent::populateState($ordering, $direction);
    }

    protected function getListQuery()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(array(
            'a.id', 'a.joomla_user_id', 'a.token', 'a.enabled',
            'a.note', 'a.created', 'a.last_used',
            'u.name AS user_name', 'u.username AS user_username', 'u.email AS user_email',
        ))
        ->from($db->quoteName('#__samcpserver_users', 'a'))
        ->leftJoin($db->quoteName('#__users', 'u') . ' ON u.id = a.joomla_user_id');

        // Filtro búsqueda
        $search = $this->getState('filter.search');
        if (!empty($search))
        {
            if (stripos($search, 'id:') === 0)
            {
                $query->where('a.id = ' . (int) substr($search, 3));
            }
            else
            {
                $search = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where('(u.name LIKE ' . $search . ' OR u.username LIKE ' . $search . ' OR a.note LIKE ' . $search . ')');
            }
        }

        // Filtro estado
        $enabled = $this->getState('filter.enabled');
        if ($enabled !== '')
        {
            $query->where('a.enabled = ' . (int) $enabled);
        }

        // Ordering
        $orderCol  = $this->state->get('list.ordering', 'u.name');
        $orderDirn = $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
