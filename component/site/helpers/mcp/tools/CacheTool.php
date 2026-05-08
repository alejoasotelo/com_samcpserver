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
        $group  = isset($args['group']) ? trim($args['group']) : null;
        $config = JFactory::getConfig();

        $cachePaths = array_unique([
            $config->get('cache_path', JPATH_SITE . '/cache'),
            JPATH_ADMINISTRATOR . '/cache',
        ]);

        if ($group)
        {
            foreach ($cachePaths as $cachePath)
            {
                $this->cleanGroupInPath($group, $cachePath, $config);
            }

            return [
                'success' => true,
                'message' => 'Caché del grupo "' . $group . '" limpiada correctamente.',
                'group'   => $group,
            ];
        }

        // Limpiar toda la caché en ambas rutas (site + admin)
        $cleaned = [];
        $known   = [
            'com_content', 'com_menus', 'com_modules', 'com_plugins',
            'com_categories', 'com_tags', 'com_users', 'com_config',
            '_system', 'mod_menu', 'page',
        ];

        foreach ($cachePaths as $cachePath)
        {
            if (!is_dir($cachePath))
            {
                continue;
            }

            // Detectar grupos reales en el directorio de caché
            $dirs   = glob($cachePath . '/*', GLOB_ONLYDIR) ?: [];
            $groups = array_unique(array_merge($known, array_map('basename', $dirs)));

            foreach ($groups as $g)
            {
                try
                {
                    $this->cleanGroupInPath($g, $cachePath, $config);
                    $cleaned[] = $g . ' (' . basename($cachePath) . ')';
                }
                catch (Exception $e)
                {
                    // ignorar grupos que no existen
                }
            }

            // GC general del directorio
            try
            {
                $options = $this->buildCacheOptions('', $cachePath, $config);
                $cache   = JCache::getInstance('callback', $options);
                $cache->gc();
            }
            catch (Exception $e) {}
        }

        return [
            'success'        => true,
            'message'        => 'Caché limpiada correctamente.',
            'groups_cleaned' => $cleaned,
        ];
    }

    private function cleanGroupInPath($group, $cachePath, $config)
    {
        $options = $this->buildCacheOptions($group, $cachePath, $config);
        $cache   = JCache::getInstance('callback', $options);
        $cache->clean($group);
    }

    private function buildCacheOptions($group, $cachePath, $config)
    {
        return [
            'defaultgroup' => $group,
            'cachebase'    => $cachePath,
            'lifetime'     => $config->get('cachetime', 15),
            'language'     => $config->get('language', 'en-GB'),
            'storage'      => $config->get('cache_handler', 'file'),
            'locking'      => true,
            'locktime'     => 10,
            'checkTime'    => true,
            'caching'      => true,
        ];
    }
}
