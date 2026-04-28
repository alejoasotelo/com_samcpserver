<?php
defined('_JEXEC') or die;

class McpserverViewMcpuser extends JViewLegacy
{
    protected $form;
    protected $item;
    protected $state;

    public function display($tpl = null)
    {
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        if (count($errors = $this->get('Errors')))
        {
            throw new Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $isNew = ($this->item->id == 0);

        JToolBarHelper::title(
            $isNew ? JText::_('COM_MCPSERVER_MCPUSER_TITLE_ADD') : JText::_('COM_MCPSERVER_MCPUSER_TITLE_EDIT'),
            'user'
        );

        JToolBarHelper::apply('mcpuser.apply');
        JToolBarHelper::save('mcpuser.save');
        JToolBarHelper::divider();
        JToolBarHelper::cancel('mcpuser.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
