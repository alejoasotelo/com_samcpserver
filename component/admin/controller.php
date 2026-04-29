<?php
defined('_JEXEC') or die;

class SamcpserverController extends JControllerLegacy
{
    protected $default_view = 'mcpusers';

    public function display($cachable = false, $urlparams = array())
    {
        $view   = $this->input->get('view', 'mcpusers');
        $layout = $this->input->get('layout', 'default');
        $id     = $this->input->getInt('id');

        parent::display($cachable, $urlparams);

        return $this;
    }
}
