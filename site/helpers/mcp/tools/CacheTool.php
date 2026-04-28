<?php
defined('_JEXEC') or die;

class SamcpserverToolCache
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
                'name'        => 'cache_clean',
                'description' => 'Limpia la caché de Joomla. Puede limpiar todos los grupos o uno específico.',
                'handler'     => 'clean',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'group' => [
                            'type'        => 'string',
                            'description' => 'Grupo de caché a limpiar (ej: com_content, com_menus). Si se omite, limpia toda la caché.',
                        ],
                    ],
                    'required' => [],
                ],
            ],
        ];
    }

    public function clean($args)
    {
        $group = isset($args['group']) ? trim($args['group']) : null;

        $config  = JFactory::getConfig();
        $options = [
            'defaultgroup' => $group ?: '',
            'cachebase'    => $config->get('cache_path', JPATH_SITE . '/cache'),
            'lifetime'     => $config->get('cachetime', 15),
            'language'     => $config->get('language', 'en-GB'),
            'storage'      => $config->get('cache_handler', 'file'),
            'locking'      => true,
            'locktime'     => 10,
            'checkTime'    => true,
            'caching'      => true,
        ];

        if ($group)
        {
            // Limpiar grupo específico
            $cache = JCache::getInstance('callback', $options);
            $cache->clean($group);

            return [
                'success' => true,
                'message' => 'Caché del grupo "' . $group . '" limpiada correctamente.',
                'group'   => $group,
            ];
        }

        // Limpiar toda la caché — iterar grupos conocidos + purge general
        $cleaned = [];
        $groups  = [
            'com_content', 'com_menus', 'com_modules', 'com_plugins',
            'com_categories', 'com_tags', 'com_users', 'com_config',
            '_system', 'mod_menu', 'page',
        ];

        foreach ($groups as $g)
        {
            try
            {
                $options['defaultgroup'] = $g;
                $cache = JCache::getInstance('callback', $options);
                $cache->clean($g);
                $cleaned[] = $g;
            }
            catch (Exception $e)
            {
                // ignorar grupos que no existen
            }
        }

        // Purge general del directorio de caché
        $options['defaultgroup'] = '';
        $cache = JCache::getInstance('callback', $options);
        $cache->gc();

        return [
            'success'        => true,
            'message'        => 'Caché limpiada correctamente.',
            'groups_cleaned' => $cleaned,
        ];
    }
}
