<?php
defined('_JEXEC') or die;

class SamcpserverViewMcpuser extends JViewLegacy
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
        $isJ4  = version_compare(JVERSION, '4.0.0', '>=');

        JToolBarHelper::title(
            $isNew ? JText::_('COM_SAMCPSERVER_MCPUSER_TITLE_ADD') : JText::_('COM_SAMCPSERVER_MCPUSER_TITLE_EDIT'),
            'user'
        );

        if ($isJ4)
        {
            $toolbar = JToolbar::getInstance();
            $toolbar->apply('mcpuser.apply');
            $toolbar->save('mcpuser.save');
            $toolbar->divider();
            $toolbar->cancel('mcpuser.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
        }
        else
        {
            JToolBarHelper::apply('mcpuser.apply');
            JToolBarHelper::save('mcpuser.save');
            JToolBarHelper::divider();
            JToolBarHelper::cancel('mcpuser.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
        }
    }
}
