<?php
defined('_JEXEC') or die;

class McpserverViewMcpusers extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;

    public function display($tpl = null)
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        // Errores
        if (count($errors = $this->get('Errors')))
        {
            throw new Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JToolBarHelper::title(JText::_('COM_MCPSERVER_MCPUSERS_TITLE'), 'users');
        JToolBarHelper::addNew('mcpuser.add');
        JToolBarHelper::editList('mcpuser.edit');
        JToolBarHelper::divider();
        JToolBarHelper::publish('mcpusers.publish', 'JTOOLBAR_ENABLE', true);
        JToolBarHelper::unpublish('mcpusers.unpublish', 'JTOOLBAR_DISABLE', true);
        JToolBarHelper::divider();
        JToolBarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'mcpusers.delete');
    }
}
