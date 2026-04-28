<?php
defined('_JEXEC') or die;

class SamcpserverControllerMcpuser extends JControllerForm
{
    protected $view_list = 'mcpusers';
    protected $view_item = 'mcpuser';

    /**
     * Regenera el token del usuario MCP actual
     */
    public function regenerate()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $id  = $this->input->getInt('id');
        $app = JFactory::getApplication();

        if (!$id)
        {
            $app->redirect(JRoute::_('index.php?option=com_samcpserver&view=mcpusers', false));
            return;
        }

        /** @var SamcpserverModelMcpuser $model */
        $model = $this->getModel('Mcpuser', 'SamcpserverModel');

        if ($model->regenerateToken($id))
        {
            $app->enqueueMessage(JText::_('COM_SAMCPSERVER_TOKEN_REGENERATED'), 'success');
        }
        else
        {
            $app->enqueueMessage($model->getError(), 'error');
        }

        $app->redirect(JRoute::_('index.php?option=com_samcpserver&view=mcpuser&layout=edit&id=' . $id, false));
    }
}
