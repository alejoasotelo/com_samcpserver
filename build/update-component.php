<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * This is a CRON script which should be called from the command-line, not the
 * web. For example something like:
 * /usr/bin/php /path/to/site/cli/update_cron.php
 */

// Set flag that this is a parent file.
const _JEXEC = 1;

error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_LIBRARIES . '/import.legacy.php';
require_once JPATH_LIBRARIES . '/cms.php';

// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';

require_once __DIR__ . '/UpdateComponentAdapter.php';

use Joomla\CMS\Plugin\PluginHelper;

/**
 * This script will fetch the update information for all extensions and store
 * them in the database, speeding up your administrator.
 *
 * @since  2.5
 */
class UpdateComponent extends JApplicationCli
{
	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function doExecute()
	{      
        JFactory::$application = $this;  
        $_SERVER['HTTP_HOST'] = '127.0.0.1';
        // $app = JFactory::getApplication('administrator');
        $path = '/var/www/html/administrator/components/com_samcpserver';

		$this->out('Updating Component DB...');
		$installer = JInstaller::getInstance();
        $installer->setPath('source', $path);

        $adapter = $installer->setupInstall('install', true);

        $adapter = $this->getAdapter($installer);

		if (!is_object($adapter))
		{
			return false;
		}

		// Add the languages from the package itself
		if (method_exists($adapter, 'loadLanguage'))
		{
			$adapter->loadLanguage($path);
		}

        try{

            // Fire the onExtensionBeforeUpdate event.
            PluginHelper::importPlugin('extension');
            $dispatcher = \JEventDispatcher::getInstance();
            $dispatcher->trigger('onExtensionBeforeUpdate', array('type' => $installer->manifest->attributes()->type, 'manifest' => $installer->manifest));

            // Run the update
            $result = $adapter->update();

            // Fire the onExtensionAfterUpdate
            $dispatcher->trigger(
                'onExtensionAfterUpdate',
                array('installer' => clone $installer, 'eid' => $result)
            );

        } catch(Exception $e) {
            $this->out('(!) Error al actualizar el componente: ');
            $this->out($e->getMessage());
            die();
        }

		if ($result === false)
		{
            $this->out('(!) No se pudo actualizar el componente');
            die();
		}

        $this->out('Actualización finalizada (OK)');
	}

    public function getAdapter($installer) {
		// We need to find the installation manifest file
		if (!$installer->findManifest())
		{
			return false;
		}

		// Load the adapter(s) for the install manifest
		$type   = (string) $installer->manifest->attributes()->type;
		$params = array('route' => 'install', 'manifest' => $installer->getManifest(), 'type' => 'component');

        $adapter = $installer->loadAdapter('updatecomponent', $params);
        if (!$installer->setAdapter('component', $adapter)) {
            return false;
        }

		return $adapter;
    }

    public function isClient($client = '')
    {
        return false;
    }
}

JApplicationCli::getInstance('UpdateComponent')->execute();