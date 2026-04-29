<?php
defined('_JEXEC') or die;

class SamcpserverToolMenus
{
    private $mcpUser;

    public function __construct($mcpUser)
    {
        $this->mcpUser = $mcpUser;
    }

    public function getDefinitions()
    {
        return [
            [
                'name'        => 'menus_list',
                'description' => 'Lista todos los menús disponibles en Joomla con su tipo y cantidad de ítems.',
                'handler'     => 'menusList',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => new stdClass(),
                    'required'   => [],
                ],
            ],
            [
                'name'        => 'menu_items_list',
                'description' => 'Lista los ítems de un menú con filtros opcionales.',
                'handler'     => 'itemsList',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'menu_type' => ['type' => 'string',  'description' => 'Tipo de menú (ej: mainmenu, topmenu). Si se omite, lista todos los ítems.'],
                        'parent_id' => ['type' => 'integer', 'description' => 'Filtrar por ID del ítem padre (0 = raíz)'],
                        'state'     => ['type' => 'integer', 'description' => '0=inactivo, 1=publicado. Si se omite, devuelve todos.'],
                        'language'  => ['type' => 'string',  'description' => 'Filtrar por idioma (ej: es-ES, *)'],
                        'limit'     => ['type' => 'integer', 'description' => 'Cantidad de resultados (default: 50)'],
                        'offset'    => ['type' => 'integer', 'description' => 'Desplazamiento (default: 0)'],
                    ],
                    'required' => [],
                ],
            ],
            [
                'name'        => 'menu_items_get',
                'description' => 'Obtiene el detalle completo de un ítem de menú por ID.',
                'handler'     => 'itemsGet',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer', 'description' => 'ID del ítem de menú'],
                    ],
                    'required' => ['id'],
                ],
            ],
            [
                'name'        => 'menu_items_create',
                'description' => 'Crea un nuevo ítem de menú en Joomla.',
                'handler'     => 'itemsCreate',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'title'      => ['type' => 'string',  'description' => 'Título del ítem'],
                        'menu_type'  => ['type' => 'string',  'description' => 'Tipo de menú donde se crea (ej: mainmenu)'],
                        'link'       => ['type' => 'string',  'description' => 'URL o link interno (ej: index.php?option=com_content&view=article&id=1)'],
                        'type'       => ['type' => 'string',  'description' => 'Tipo: component, url, alias, separator, heading (default: url)'],
                        'parent_id'  => ['type' => 'integer', 'description' => 'ID del ítem padre (default: 1 = raíz)'],
                        'state'      => ['type' => 'integer', 'description' => '0=inactivo, 1=publicado (default: 1)'],
                        'alias'      => ['type' => 'string',  'description' => 'Alias SEF (se genera automáticamente si se omite)'],
                        'language'   => ['type' => 'string',  'description' => 'Idioma (default: *)'],
                        'access'     => ['type' => 'integer', 'description' => 'Nivel de acceso (default: 1 = Public)'],
                        'target'     => ['type' => 'integer', 'description' => 'Target: 0=misma ventana, 1=nueva ventana, 2=popup (default: 0)'],
                        'note'       => ['type' => 'string',  'description' => 'Nota interna (opcional)'],
                    ],
                    'required' => ['title', 'menu_type', 'link'],
                ],
            ],
            [
                'name'        => 'menu_items_update',
                'description' => 'Actualiza un ítem de menú existente. Solo se modifican los campos enviados.',
                'handler'     => 'itemsUpdate',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'id'        => ['type' => 'integer', 'description' => 'ID del ítem a modificar'],
                        'title'     => ['type' => 'string',  'description' => 'Nuevo título'],
                        'link'      => ['type' => 'string',  'description' => 'Nueva URL o link interno'],
                        'state'     => ['type' => 'integer', 'description' => '0=inactivo, 1=publicado'],
                        'parent_id' => ['type' => 'integer', 'description' => 'Nuevo padre'],
                        'alias'     => ['type' => 'string',  'description' => 'Nuevo alias SEF'],
                        'access'    => ['type' => 'integer', 'description' => 'Nuevo nivel de acceso'],
                        'language'  => ['type' => 'string',  'description' => 'Nuevo idioma'],
                        'target'    => ['type' => 'integer', 'description' => 'Nuevo target'],
                        'note'      => ['type' => 'string',  'description' => 'Nueva nota interna'],
                    ],
                    'required' => ['id'],
                ],
            ],
            [
                'name'        => 'menu_items_delete',
                'description' => 'Envía un ítem de menú a la papelera o lo elimina definitivamente.',
                'handler'     => 'itemsDelete',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'id'    => ['type' => 'integer', 'description' => 'ID del ítem de menú'],
                        'force' => ['type' => 'boolean', 'description' => 'true = eliminar definitivamente, false = papelera (default: false)'],
                    ],
                    'required' => ['id'],
                ],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // HANDLERS
    // -------------------------------------------------------------------------

    public function menusList($args)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select([
                'm.id',
                'm.menutype',
                'm.title',
                'm.description',
                'COUNT(i.id) AS item_count',
            ])
            ->from($db->quoteName('#__menu_types', 'm'))
            ->leftJoin(
                $db->quoteName('#__menu', 'i')
                . ' ON i.menutype = m.menutype AND i.published != -2 AND i.client_id = 0'
            )
            ->group('m.id, m.menutype, m.title, m.description')
            ->order('m.title ASC');

        $db->setQuery($query);
        $menus = $db->loadAssocList();

        return [
            'total' => count($menus),
            'menus' => $menus ?: [],
        ];
    }

    public function itemsList($args)
    {
        $db     = JFactory::getDbo();
        $limit  = isset($args['limit'])  ? (int) $args['limit']  : 50;
        $offset = isset($args['offset']) ? (int) $args['offset'] : 0;

        $query = $db->getQuery(true)
            ->select([
                'a.id', 'a.menutype', 'a.title', 'a.alias', 'a.link',
                'a.type', 'a.published AS state', 'a.parent_id', 'a.level',
                'a.language', 'a.access', 'a.home', 'a.note',
                'a.browserNav AS target',
            ])
            ->from($db->quoteName('#__menu', 'a'))
            ->where('a.client_id = 0')   // solo frontend
            ->where('a.id > 1')          // excluir root
            ->where('a.published != -2') // excluir papelera
            ->order('a.menutype ASC, a.lft ASC');

        if (!empty($args['menu_type']))
        {
            $query->where('a.menutype = ' . $db->quote($args['menu_type']));
        }

        if (isset($args['parent_id']))
        {
            $query->where('a.parent_id = ' . (int) $args['parent_id']);
        }

        if (isset($args['state']))
        {
            $query->where('a.published = ' . (int) $args['state']);
        }

        if (!empty($args['language']))
        {
            $query->where('a.language = ' . $db->quote($args['language']));
        }

        // Total
        $countQuery = clone $query;
        $countQuery->clear('select')->clear('order')->select('COUNT(*)');
        $db->setQuery($countQuery);
        $total = (int) $db->loadResult();

        $db->setQuery($query, $offset, $limit);
        $items = $db->loadAssocList();

        return [
            'total'  => $total,
            'offset' => $offset,
            'limit'  => $limit,
            'items'  => $items ?: [],
        ];
    }

    public function itemsGet($args)
    {
        $id = (int) ($args['id'] ?? 0);

        if (!$id)
        {
            throw new InvalidArgumentException('Se requiere el parámetro id.');
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('a.*')
            ->from($db->quoteName('#__menu', 'a'))
            ->where('a.id = ' . $id)
            ->where('a.client_id = 0');

        $db->setQuery($query);
        $item = $db->loadAssoc();

        if (!$item)
        {
            throw new RuntimeException('Ítem de menú no encontrado: ' . $id);
        }

        // Decodificar params si es JSON
        if (!empty($item['params']) && is_string($item['params']))
        {
            $decoded = json_decode($item['params'], true);
            if (json_last_error() === JSON_ERROR_NONE)
            {
                $item['params'] = $decoded;
            }
        }

        return $item;
    }

    public function itemsCreate($args)
    {
        if (empty($args['title']))
        {
            throw new InvalidArgumentException('Se requiere el parámetro title.');
        }
        if (empty($args['menu_type']))
        {
            throw new InvalidArgumentException('Se requiere el parámetro menu_type.');
        }
        if (!isset($args['link']))
        {
            throw new InvalidArgumentException('Se requiere el parámetro link.');
        }

        // Verificar que el menu_type existe
        $db    = JFactory::getDbo();
        $check = $db->getQuery(true)
            ->select('id')
            ->from($db->quoteName('#__menu_types'))
            ->where($db->quoteName('menutype') . ' = ' . $db->quote($args['menu_type']));
        $db->setQuery($check);
        if (!$db->loadResult())
        {
            throw new RuntimeException('El menu_type "' . $args['menu_type'] . '" no existe.');
        }

        $table = JTable::getInstance('Menu', 'JTable');

        $alias = !empty($args['alias'])
            ? $args['alias']
            : JFilterOutput::stringURLSafe($args['title']);

        $alias = $this->uniqueAlias($alias, $args['menu_type']);

        $parentId = isset($args['parent_id']) ? (int) $args['parent_id'] : 1;

        $data = [
            'title'      => $args['title'],
            'alias'      => $alias,
            'menutype'   => $args['menu_type'],
            'link'       => $args['link'],
            'type'       => $args['type'] ?? 'url',
            'published'  => isset($args['state']) ? (int) $args['state'] : 1,
            'parent_id'  => $parentId,
            'language'   => $args['language'] ?? '*',
            'access'     => isset($args['access']) ? (int) $args['access'] : 1,
            'browserNav' => isset($args['target']) ? (int) $args['target'] : 0,
            'note'       => $args['note'] ?? '',
            'client_id'  => 0,
            'home'       => 0,
            'params'     => '{}',
        ];

        // Setear nodo padre para árbol nested sets
        $table->setLocation($parentId, 'last-child');

        if (!$table->bind($data))
        {
            throw new RuntimeException('Error bind: ' . $table->getError());
        }

        if (!$table->check())
        {
            throw new RuntimeException('Error check: ' . $table->getError());
        }

        if (!$table->store())
        {
            throw new RuntimeException('Error store: ' . $table->getError());
        }

        return [
            'success' => true,
            'id'      => (int) $table->id,
            'alias'   => $table->alias,
            'message' => 'Ítem de menú creado con ID ' . $table->id,
        ];
    }

    public function itemsUpdate($args)
    {
        $id = (int) ($args['id'] ?? 0);

        if (!$id)
        {
            throw new InvalidArgumentException('Se requiere el parámetro id.');
        }

        $table = JTable::getInstance('Menu', 'JTable');

        if (!$table->load($id))
        {
            throw new RuntimeException('Ítem de menú no encontrado: ' . $id);
        }

        $map = [
            'title'      => 'title',
            'link'       => 'link',
            'state'      => 'published',
            'language'   => 'language',
            'access'     => 'access',
            'note'       => 'note',
            'target'     => 'browserNav',
        ];

        foreach ($map as $arg => $col)
        {
            if (isset($args[$arg]))
            {
                $table->$col = $args[$arg];
            }
        }

        if (isset($args['alias']))
        {
            $alias = JFilterOutput::stringURLSafe($args['alias']);
            $table->alias = $this->uniqueAlias($alias, $table->menutype, $id);
        }

        if (isset($args['parent_id']) && (int) $args['parent_id'] !== (int) $table->parent_id)
        {
            $table->setLocation((int) $args['parent_id'], 'last-child');
        }

        if (!$table->check())
        {
            throw new RuntimeException('Error check: ' . $table->getError());
        }

        if (!$table->store())
        {
            throw new RuntimeException('Error store: ' . $table->getError());
        }

        return [
            'success' => true,
            'id'      => $id,
            'message' => 'Ítem de menú actualizado correctamente.',
        ];
    }

    public function itemsDelete($args)
    {
        $id    = (int) ($args['id'] ?? 0);
        $force = !empty($args['force']);

        if (!$id)
        {
            throw new InvalidArgumentException('Se requiere el parámetro id.');
        }

        $table = JTable::getInstance('Menu', 'JTable');

        if (!$table->load($id))
        {
            throw new RuntimeException('Ítem de menú no encontrado: ' . $id);
        }

        if ($force)
        {
            if (!$table->delete($id))
            {
                throw new RuntimeException('Error al eliminar: ' . $table->getError());
            }

            return [
                'success' => true,
                'id'      => $id,
                'message' => 'Ítem de menú eliminado definitivamente.',
            ];
        }

        // Papelera
        $table->published = -2;

        if (!$table->store())
        {
            throw new RuntimeException('Error al enviar a papelera: ' . $table->getError());
        }

        return [
            'success' => true,
            'id'      => $id,
            'message' => 'Ítem de menú enviado a la papelera.',
        ];
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------

    private function uniqueAlias($alias, $menuType, $excludeId = 0)
    {
        $db       = JFactory::getDbo();
        $original = $alias;
        $suffix   = 0;

        do
        {
            $query = $db->getQuery(true)
                ->select('id')
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('alias')    . ' = ' . $db->quote($alias))
                ->where($db->quoteName('menutype') . ' = ' . $db->quote($menuType));

            if ($excludeId)
            {
                $query->where('id != ' . (int) $excludeId);
            }

            $db->setQuery($query);
            $exists = $db->loadResult();

            if ($exists)
            {
                $suffix++;
                $alias = $original . '-' . $suffix;
            }
        }
        while ($exists);

        return $alias;
    }
}
