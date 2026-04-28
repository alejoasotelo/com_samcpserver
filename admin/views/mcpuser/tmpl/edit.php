<?php
defined('_JEXEC') or die;

JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

$app    = JFactory::getApplication();
$isNew  = ($this->item->id == 0);
$token  = $this->item->token;
$mcpUrl = $token ? McpserverModelMcpuser::getMcpUrl($token) : '';
?>

<script type="text/javascript">
    Joomla.submitbutton = function(task) {
        if (task === 'mcpuser.cancel' || document.formvalidator.isValid(document.getElementById('adminForm'))) {
            Joomla.submitform(task, document.getElementById('adminForm'));
        }
    };

    function copyMcpUrl() {
        var url = document.getElementById('mcp-url-display');
        url.select();
        url.setSelectionRange(0, 99999);
        document.execCommand('copy');
        var btn = document.getElementById('btn-copy-url');
        btn.innerHTML = '<span class="icon-ok"></span> Copiado';
        setTimeout(function() {
            btn.innerHTML = '<span class="icon-copy"></span> <?php echo JText::_('COM_MCPSERVER_BTN_COPY'); ?>';
        }, 2000);
    }

    function confirmRegenerate() {
        if (confirm('<?php echo JText::_('COM_MCPSERVER_REGENERATE_CONFIRM'); ?>')) {
            Joomla.submitform('mcpuser.regenerate', document.getElementById('adminForm'));
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_mcpserver&layout=edit&id=' . $this->item->id); ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate">

    <div class="row-fluid">

        <?php /* Panel izquierdo - datos */ ?>
        <div class="span9">
            <fieldset class="adminform">
                <legend><?php echo JText::_('JDETAILS'); ?></legend>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('joomla_user_id'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('joomla_user_id'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('note'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('note'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('enabled'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('enabled'); ?>
                    </div>
                </div>

                <?php if (!$isNew && $token) : ?>
                <hr />

                <?php /* Token (readonly) */ ?>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('token'); ?>
                    </div>
                    <div class="controls">
                        <div class="input-append">
                            <?php echo $this->form->getInput('token'); ?>
                            <button type="button" class="btn btn-warning" onclick="confirmRegenerate()">
                                <span class="icon-refresh"></span>
                                <?php echo JText::_('COM_MCPSERVER_BTN_REGENERATE'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <?php /* URL del MCP */ ?>
                <div class="control-group">
                    <div class="control-label">
                        <label><?php echo JText::_('COM_MCPSERVER_MCP_URL_LABEL'); ?></label>
                    </div>
                    <div class="controls">
                        <p class="help-block"><?php echo JText::_('COM_MCPSERVER_MCP_URL_DESC'); ?></p>
                        <div class="input-append">
                            <input type="text"
                                   id="mcp-url-display"
                                   value="<?php echo $this->escape($mcpUrl); ?>"
                                   class="input-xxlarge"
                                   readonly
                                   onclick="this.select()"
                            />
                            <button type="button" class="btn btn-success" id="btn-copy-url" onclick="copyMcpUrl()">
                                <span class="icon-copy"></span>
                                <?php echo JText::_('COM_MCPSERVER_BTN_COPY'); ?>
                            </button>
                        </div>

                        <?php /* Instrucciones Claude Desktop */ ?>
                        <div class="alert alert-info" style="margin-top:15px;">
                            <strong>Claude Desktop (<code>claude_desktop_config.json</code>):</strong>
                            <pre style="margin-top:8px; background:#f5f5f5; padding:10px; border-radius:4px;">{
  "mcpServers": {
    "joomla": {
      "url": "<?php echo $this->escape($mcpUrl); ?>"
    }
  }
}</pre>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </fieldset>
        </div>

        <?php /* Panel derecho - info */ ?>
        <div class="span3">
            <?php if (!$isNew) : ?>
            <fieldset class="form-vertical">
                <legend><?php echo JText::_('JGLOBAL_FIELDSET_INFO'); ?></legend>
                <dl class="dl-horizontal">
                    <dt><?php echo JText::_('COM_MCPSERVER_COL_CREATED'); ?></dt>
                    <dd><?php echo $this->item->created ? JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC4')) : '&mdash;'; ?></dd>
                    <dt><?php echo JText::_('COM_MCPSERVER_COL_LAST_USED'); ?></dt>
                    <dd><?php echo $this->item->last_used ? JHtml::_('date', $this->item->last_used, JText::_('DATE_FORMAT_LC4')) : JText::_('COM_MCPSERVER_NEVER'); ?></dd>
                </dl>
            </fieldset>
            <?php else : ?>
            <div class="alert alert-info">
                <p><?php echo JText::_('COM_MCPSERVER_FIELD_TOKEN_DESC'); ?></p>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <input type="hidden" name="task"   value="" />
    <input type="hidden" name="id"     value="<?php echo $this->item->id; ?>" />
    <?php echo JHtml::_('form.token'); ?>
    <?php echo $this->form->getInput('id'); ?>
</form>
