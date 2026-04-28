<?php
defined('_JEXEC') or die;

class McpserverControllerMcpuser extends JControllerForm
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
            $app->redirect(JRoute::_('index.php?option=com_mcpserver&view=mcpusers', false));
            return;
        }

        /** @var McpserverModelMcpuser $model */
        $model = $this->getModel('Mcpuser', 'McpserverModel');

        if ($model->regenerateToken($id))
        {
            $app->enqueueMessage(JText::_('COM_MCPSERVER_TOKEN_REGENERATED'), 'success');
        }
        else
        {
            $app->enqueueMessage($model->getError(), 'error');
        }

        $app->redirect(JRoute::_('index.php?option=com_mcpserver&view=mcpuser&layout=edit&id=' . $id, false));
    }
}
