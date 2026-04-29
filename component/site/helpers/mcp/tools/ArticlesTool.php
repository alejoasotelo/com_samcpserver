<?php
defined('_JEXEC') or die;

class SamcpserverToolArticles
{
    private $mcpUser;

    public function __construct($mcpUser)
    {
        $this->mcpUser = $mcpUser;
    }

    /**
     * Retorna las definiciones de todas las tools
     */
    public function getDefinitions()
    {
        return [
            [
                'name'        => 'articles_list',
                'description' => 'Lista artículos de Joomla con filtros opcionales.',
                'handler'     => 'list',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'limit'  => ['type' => 'integer', 'description' => 'Cantidad de resultados (default: 20)'],
                        'offset' => ['type' => 'integer', 'description' => 'Desplazamiento para paginación (default: 0)'],
                        'catid'  => ['type' => 'integer', 'description' => 'Filtrar por ID de categoría'],
                        'state'  => ['type' => 'integer', 'description' => '0=inactivo, 1=publicado, 2=archivado, -2=papelera'],
                        'search' => ['type' => 'string',  'description' => 'Buscar en título'],
                    ],
                    'required' => [],
                ],
            ],
            [
                'name'        => 'articles_get',
                'description' => 'Obtiene el detalle completo de un artículo por ID.',
                'handler'     => 'get',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer', 'description' => 'ID del artículo'],
                    ],
                    'required' => ['id'],
                ],
            ],
            [
                'name'        => 'articles_create',
                'description' => 'Crea un nuevo artículo en Joomla.',
                'handler'     => 'create',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'title'     => ['type' => 'string',  'description' => 'Título del artículo'],
                        'text'      => ['type' => 'string',  'description' => 'Contenido HTML del artículo'],
                        'catid'     => ['type' => 'integer', 'description' => 'ID de categoría (default: 1 = Uncategorised)'],
                        'state'     => ['type' => 'integer', 'description' => '0=inactivo, 1=publicado (default: 0)'],
                        'alias'     => ['type' => 'string',  'description' => 'Alias SEF (se genera automáticamente si se omite)'],
                        'introtext' => ['type' => 'string',  'description' => 'Texto introductorio (opcional)'],
                        'metadesc'  => ['type' => 'string',  'description' => 'Meta descripción SEO (opcional)'],
                        'language'  => ['type' => 'string',  'description' => 'Idioma, ej: es-ES, en-GB, * (default: *)'],
                    ],
                    'required' => ['title', 'text'],
                ],
            ],
            [
                'name'        => 'articles_update',
                'description' => 'Actualiza un artículo existente. Solo se modifican los campos enviados.',
                'handler'     => 'update',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'id'        => ['type' => 'integer', 'description' => 'ID del artículo a modificar'],
                        'title'     => ['type' => 'string',  'description' => 'Nuevo título'],
                        'text'      => ['type' => 'string',  'description' => 'Nuevo contenido HTML'],
                        'catid'     => ['type' => 'integer', 'description' => 'Nueva categoría'],
                        'state'     => ['type' => 'integer', 'description' => '0=inactivo, 1=publicado, 2=archivado, -2=papelera'],
                        'alias'     => ['type' => 'string',  'description' => 'Nuevo alias SEF'],
                        'introtext' => ['type' => 'string',  'description' => 'Nuevo texto introductorio'],
                        'metadesc'  => ['type' => 'string',  'description' => 'Nueva meta descripción'],
                    ],
                    'required' => ['id'],
                ],
            ],
            [
                'name'        => 'articles_delete',
                'description' => 'Envía un artículo a la papelera o lo elimina definitivamente.',
                'handler'     => 'delete',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'id'    => ['type' => 'integer', 'description' => 'ID del artículo'],
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

    public function list($args)
    {
        $db     = JFactory::getDbo();
        $limit  = isset($args['limit'])  ? (int) $args['limit']  : 20;
        $offset = isset($args['offset']) ? (int) $args['offset'] : 0;

        $query = $db->getQuery(true)
            ->select([
                'a.id', 'a.title', 'a.alias', 'a.state', 'a.catid',
                'a.created', 'a.modified', 'a.hits', 'a.language',
                'c.title AS category_title',
                'u.name AS author',
            ])
            ->from($db->quoteName('#__content', 'a'))
            ->leftJoin($db->quoteName('#__categories', 'c') . ' ON c.id = a.catid')
            ->leftJoin($db->quoteName('#__users', 'u') . ' ON u.id = a.created_by')
            ->order('a.created DESC');

        if (isset($args['catid']))
        {
            $query->where('a.catid = ' . (int) $args['catid']);
        }

        if (isset($args['state']))
        {
            $query->where('a.state = ' . (int) $args['state']);
        }
        else
        {
            $query->where('a.state != -2'); // excluir papelera por defecto
        }

        if (!empty($args['search']))
        {
            $search = $db->quote('%' . $db->escape($args['search'], true) . '%');
            $query->where('a.title LIKE ' . $search);
        }

        $db->setQuery($query, $offset, $limit);
        $items = $db->loadAssocList();

        // Total para info de paginación
        $countQuery = clone $query;
        $countQuery->clear('select')->clear('order')->select('COUNT(*)');
        $db->setQuery($countQuery);
        $total = (int) $db->loadResult();

        return [
            'total'  => $total,
            'offset' => $offset,
            'limit'  => $limit,
            'items'  => $items ?: [],
        ];
    }

    public function get($args)
    {
        $id = (int) ($args['id'] ?? 0);

        if (!$id)
        {
            throw new InvalidArgumentException('Se requiere el parámetro id.');
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select([
                'a.*',
                'c.title AS category_title',
                'u.name AS author',
            ])
            ->from($db->quoteName('#__content', 'a'))
            ->leftJoin($db->quoteName('#__categories', 'c') . ' ON c.id = a.catid')
            ->leftJoin($db->quoteName('#__users', 'u') . ' ON u.id = a.created_by')
            ->where('a.id = ' . $id);

        $db->setQuery($query);
        $item = $db->loadAssoc();

        if (!$item)
        {
            throw new RuntimeException('Artículo no encontrado: ' . $id);
        }

        return $item;
    }

    public function create($args)
    {
        if (empty($args['title']))
        {
            throw new InvalidArgumentException('Se requiere el parámetro title.');
        }

        if (!isset($args['text']))
        {
            throw new InvalidArgumentException('Se requiere el parámetro text.');
        }

        JTable::addIncludePath(JPATH_ROOT . '/libraries/legacy/table');

        /** @var JTableContent $table */
        $table = JTable::getInstance('Content');

        $now    = JFactory::getDate()->toSql();
        $userId = (int) $this->mcpUser->joomla_user_id;

        $alias = !empty($args['alias'])
            ? $args['alias']
            : JFilterOutput::stringURLSafe($args['title']);

        // Asegurar alias único
        $alias = $this->uniqueAlias($alias);

        $data = [
            'title'        => $args['title'],
            'alias'        => $alias,
            'introtext'    => $args['introtext'] ?? '',
            'fulltext'     => $args['text'],
            'state'        => isset($args['state']) ? (int) $args['state'] : 0,
            'catid'        => isset($args['catid']) ? (int) $args['catid'] : 1,
            'language'     => $args['language'] ?? '*',
            'created'      => $now,
            'created_by'   => $userId,
            'modified'     => $now,
            'modified_by'  => $userId,
            'publish_up'   => $now,
            'publish_down' => null,
            'access'       => 1,
            'metadata'     => json_encode(['robots' => '', 'author' => '', 'rights' => '', 'xreference' => '']),
            'metadesc'     => $args['metadesc'] ?? '',
            'metakey'      => '',
            'params'       => '{}',
            'featured'     => 0,
            'hits'         => 0,
            'version'      => 1,
        ];

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
            'message' => 'Artículo creado con ID ' . $table->id,
        ];
    }

    public function update($args)
    {
        $id = (int) ($args['id'] ?? 0);

        if (!$id)
        {
            throw new InvalidArgumentException('Se requiere el parámetro id.');
        }

        JTable::addIncludePath(JPATH_ROOT . '/libraries/legacy/table');

        /** @var JTableContent $table */
        $table = JTable::getInstance('Content');

        if (!$table->load($id))
        {
            throw new RuntimeException('Artículo no encontrado: ' . $id);
        }

        $userId = (int) $this->mcpUser->joomla_user_id;
        $now    = JFactory::getDate()->toSql();

        // Solo pisar los campos que vienen en $args
        $map = [
            'title'     => 'title',
            'text'      => 'fulltext',
            'introtext' => 'introtext',
            'catid'     => 'catid',
            'state'     => 'state',
            'metadesc'  => 'metadesc',
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
            $table->alias = $this->uniqueAlias($alias, $id);
        }

        $table->modified    = $now;
        $table->modified_by = $userId;
        $table->version++;

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
            'message' => 'Artículo actualizado correctamente.',
        ];
    }

    public function delete($args)
    {
        $id    = (int) ($args['id'] ?? 0);
        $force = !empty($args['force']);

        if (!$id)
        {
            throw new InvalidArgumentException('Se requiere el parámetro id.');
        }

        JTable::addIncludePath(JPATH_ROOT . '/libraries/legacy/table');

        /** @var JTableContent $table */
        $table = JTable::getInstance('Content');

        if (!$table->load($id))
        {
            throw new RuntimeException('Artículo no encontrado: ' . $id);
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
                'message' => 'Artículo eliminado definitivamente.',
            ];
        }

        // Papelera (state = -2)
        $table->state = -2;

        if (!$table->store())
        {
            throw new RuntimeException('Error al enviar a papelera: ' . $table->getError());
        }

        return [
            'success' => true,
            'id'      => $id,
            'message' => 'Artículo enviado a la papelera.',
        ];
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------

    /**
     * Genera un alias único verificando duplicados en #__content
     */
    private function uniqueAlias($alias, $excludeId = 0)
    {
        $db       = JFactory::getDbo();
        $original = $alias;
        $suffix   = 0;

        do
        {
            $query = $db->getQuery(true)
                ->select('id')
                ->from($db->quoteName('#__content'))
                ->where($db->quoteName('alias') . ' = ' . $db->quote($alias));

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
