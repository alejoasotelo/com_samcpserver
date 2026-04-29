<?php

namespace Joomla\CMS\Installer\Adapter;

use Joomla\CMS\Installer\Adapter\ComponentAdapter;
use Joomla\CMS\Table\Table;

defined('JPATH_PLATFORM') or die;

class UpdateComponentAdapter extends ComponentAdapter
{

    public function __construct($parent, $db, array $options = array())
    {
        parent::__construct($parent, $db, $options);
        $this->type = 'component';
    }

	/**
	 * Generic install method for extensions
	 *
	 * @return  boolean|integer  The extension ID on success, boolean false on failure
	 *
	 * @since   3.4
	 */
	public function install()
	{
		// Get the extension's description
		$description = (string) $this->getManifest()->description;

		if ($description)
		{
			$this->parent->message = \JText::_($description);
		}
		else
		{
			$this->parent->message = '';
		}

		// Set the extension's name and element
		$this->name    = $this->getName();
		$this->element = $this->getElement();

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Extension Precheck and Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Setup the install paths and perform other prechecks as necessary
		try
		{
			$this->setupInstallPaths();
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		// Check to see if an extension by the same name is already installed.
		try
		{
			$this->checkExistingExtension();
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		// Check if the extension is present in the filesystem
		try
		{
			$this->checkExtensionInFilesystem();
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		// If we are on the update route, run any custom setup routines
		if ($this->route === 'update')
		{
			try
			{
				$this->setupUpdates();
			}
			catch (\RuntimeException $e)
			{
				// Install failed, roll back changes
				$this->parent->abort($e->getMessage());

				return false;
			}
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Installer Trigger Loading
		 * ---------------------------------------------------------------------------------------------
		 */

		$this->setupScriptfile();

		try
		{
			$this->triggerManifestScript('preflight');
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		try
		{
			$this->storeExtension();
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		try
		{
			$this->parseQueries();
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		// Run the custom method based on the route
		try
		{
			$this->triggerManifestScript($this->route);
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Finalization and Cleanup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		try
		{
			$this->finaliseInstall();
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		// And now we run the postflight
		try
		{
			$this->triggerManifestScript('postflight');
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		return $this->extension->extension_id;
	}

	/**
	 * Method to finalise the installation processing
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  \RuntimeException
	 */
	protected function finaliseInstall()
	{
		/** @var Update $update */
		$update = Table::getInstance('update');

		// Clobber any possible pending updates
		$uid = $update->find(
			array(
				'element'   => $this->element,
				'type'      => $this->extension->type,
				'client_id' => 1,
			)
		);

		if ($uid)
		{
			$update->delete($uid);
		}

		// Time to build the admin menus
		if (!$this->_buildAdminMenus($this->extension->extension_id))
		{
			\JLog::add(\JText::_('JLIB_INSTALLER_ABORT_COMP_BUILDADMINMENUS_FAILED'), \JLog::WARNING, 'jerror');
		}

		// Make sure that menu items pointing to the component have correct component id assigned to them.
		// Prevents message "Component 'com_extension' does not exist." after uninstalling / re-installing component.
		if (!$this->_updateMenus($this->extension->extension_id))
		{
			\JLog::add(\JText::_('JLIB_INSTALLER_ABORT_COMP_UPDATESITEMENUS_FAILED'), \JLog::WARNING, 'jerror');
		}

		/** @var Asset $asset */
		$asset = Table::getInstance('Asset');

		// Check if an asset already exists for this extension and create it if not
		if (!$asset->loadByName($this->extension->element))
		{
			// Register the component container just under root in the assets table.
			$asset->name      = $this->extension->element;
			$asset->parent_id = 1;
			$asset->rules     = '{}';
			$asset->title     = $this->extension->name;
			$asset->setLocation(1, 'last-child');

			if (!$asset->store())
			{
				// Install failed, roll back changes
				throw new \RuntimeException(
					\JText::sprintf(
						'JLIB_INSTALLER_ABORT_ROLLBACK',
						\JText::_('JLIB_INSTALLER_' . strtoupper($this->route)),
						$this->extension->getError()
					)
				);
			}
		}
	}
    
}