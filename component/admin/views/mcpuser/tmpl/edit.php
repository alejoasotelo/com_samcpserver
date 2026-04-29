<?php
defined('_JEXEC') or die;

$isJ4  = version_compare(JVERSION, '4.0.0', '>=');
$isNew = ($this->item->id == 0);
$token = $this->item->token;

JLoader::register('SamcpserverModelMcpuser', JPATH_ADMINISTRATOR . '/components/com_samcpserver/models/mcpuser.php');
$mcpUrl = $token ? SamcpserverModelMcpuser::getMcpUrl($token) : '';

if ($isJ4)
{
    // Joomla 4+ — Usa Web Assets
    /** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
    $wa = $this->document->getWebAssetManager();
    $wa->useScript('keepalive')
       ->useScript('form.validate');
}
else
{
    // Joomla 3
    JHtml::_('behavior.formvalidation');
    JHtml::_('formbehavior.chosen', 'select');
}
?>

<script type="text/javascript">
    <?php if ($isJ4): ?>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-toolbar [task]').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                Joomla.submitbutton(this.getAttribute('task'));
            });
        });
    });
    <?php endif; ?>

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
        btn.textContent = '✓ Copiado';
        setTimeout(function() { btn.textContent = '<?php echo JText::_('COM_SAMCPSERVER_BTN_COPY'); ?>'; }, 2000);
    }

    function confirmRegenerate() {
        if (confirm('<?php echo JText::_('COM_SAMCPSERVER_REGENERATE_CONFIRM'); ?>')) {
            Joomla.submitform('mcpuser.regenerate', document.getElementById('adminForm'));
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_samcpserver&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="adminForm"
      class="<?php echo $isJ4 ? 'form-validate' : 'form-validate form-horizontal'; ?>">

    <?php if ($isJ4): ?>
    <?php /* Layout Joomla 4 — usa grid nativo de Bootstrap 5 */ ?>
    <div class="row">
        <div class="col-lg-9">
            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="card-title"><?php echo JText::_('JDETAILS'); ?></h2>

                    <?php echo $this->form->renderField('joomla_user_id'); ?>
                    <?php echo $this->form->renderField('note'); ?>
                    <?php echo $this->form->renderField('enabled'); ?>

                    <?php if (!$isNew && $token): ?>
                    <hr />
                    <?php echo $this->form->renderField('token'); ?>

                    <div class="mb-3">
                        <label class="form-label fw-bold"><?php echo JText::_('COM_SAMCPSERVER_MCP_URL_LABEL'); ?></label>
                        <p class="form-text"><?php echo JText::_('COM_SAMCPSERVER_MCP_URL_DESC'); ?></p>
                        <div class="input-group">
                            <input type="text" id="mcp-url-display"
                                   class="form-control"
                                   value="<?php echo $this->escape($mcpUrl); ?>"
                                   readonly onclick="this.select()" />
                            <button type="button" class="btn btn-success" id="btn-copy-url" onclick="copyMcpUrl()">
                                <?php echo JText::_('COM_SAMCPSERVER_BTN_COPY'); ?>
                            </button>
                            <button type="button" class="btn btn-warning" onclick="confirmRegenerate()">
                                <?php echo JText::_('COM_SAMCPSERVER_BTN_REGENERATE'); ?>
                            </button>
                        </div>
                        <div class="alert alert-info mt-3">
                            <strong>Claude Desktop (<code>claude_desktop_config.json</code>):</strong>
                            <pre class="mt-2 p-2 bg-light rounded">{
  "mcpServers": {
    "joomla": {
      "url": "<?php echo $this->escape($mcpUrl); ?>"
    }
  }
}</pre>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <?php if (!$isNew): ?>
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title"><?php echo JText::_('JGLOBAL_FIELDSET_INFO'); ?></h3>
                    <dl>
                        <dt><?php echo JText::_('COM_SAMCPSERVER_COL_CREATED'); ?></dt>
                        <dd><?php echo $this->item->created ? JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC4')) : '&mdash;'; ?></dd>
                        <dt><?php echo JText::_('COM_SAMCPSERVER_COL_LAST_USED'); ?></dt>
                        <dd><?php echo $this->item->last_used ? JHtml::_('date', $this->item->last_used, JText::_('DATE_FORMAT_LC4')) : JText::_('COM_SAMCPSERVER_NEVER'); ?></dd>
                    </dl>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <p><?php echo JText::_('COM_SAMCPSERVER_FIELD_TOKEN_DESC'); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php else: ?>
    <?php /* Layout Joomla 3 — Bootstrap 2, form-horizontal */ ?>
    <div class="row-fluid">
        <div class="span9">
            <fieldset class="adminform">
                <legend><?php echo JText::_('JDETAILS'); ?></legend>

                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('joomla_user_id'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('joomla_user_id'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('note'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('note'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('enabled'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('enabled'); ?></div>
                </div>

                <?php if (!$isNew && $token): ?>
                <hr />
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('token'); ?></div>
                    <div class="controls">
                        <div class="input-append">
                            <?php echo $this->form->getInput('token'); ?>
                            <button type="button" class="btn btn-warning" onclick="confirmRegenerate()">
                                <span class="icon-refresh"></span>
                                <?php echo JText::_('COM_SAMCPSERVER_BTN_REGENERATE'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label><?php echo JText::_('COM_SAMCPSERVER_MCP_URL_LABEL'); ?></label>
                    </div>
                    <div class="controls">
                        <p class="help-block"><?php echo JText::_('COM_SAMCPSERVER_MCP_URL_DESC'); ?></p>
                        <div class="input-append">
                            <input type="text" id="mcp-url-display"
                                   value="<?php echo $this->escape($mcpUrl); ?>"
                                   class="input-xxlarge" readonly onclick="this.select()" />
                            <button type="button" class="btn btn-success" id="btn-copy-url" onclick="copyMcpUrl()">
                                <?php echo JText::_('COM_SAMCPSERVER_BTN_COPY'); ?>
                            </button>
                        </div>
                        <div class="alert alert-info" style="margin-top:15px;">
                            <strong>Claude Desktop (<code>claude_desktop_config.json</code>):</strong>
                            <pre style="margin-top:8px;background:#f5f5f5;padding:10px;border-radius:4px;">{
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

        <div class="span3">
            <?php if (!$isNew): ?>
            <fieldset class="form-vertical">
                <legend><?php echo JText::_('JGLOBAL_FIELDSET_INFO'); ?></legend>
                <dl class="dl-horizontal">
                    <dt><?php echo JText::_('COM_SAMCPSERVER_COL_CREATED'); ?></dt>
                    <dd><?php echo $this->item->created ? JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC4')) : '&mdash;'; ?></dd>
                    <dt><?php echo JText::_('COM_SAMCPSERVER_COL_LAST_USED'); ?></dt>
                    <dd><?php echo $this->item->last_used ? JHtml::_('date', $this->item->last_used, JText::_('DATE_FORMAT_LC4')) : JText::_('COM_SAMCPSERVER_NEVER'); ?></dd>
                </dl>
            </fieldset>
            <?php else: ?>
            <div class="alert alert-info">
                <p><?php echo JText::_('COM_SAMCPSERVER_FIELD_TOKEN_DESC'); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <input type="hidden" name="task"  value="" />
    <input type="hidden" name="id"    value="<?php echo (int) $this->item->id; ?>" />
    <?php echo JHtml::_('form.token'); ?>
    <?php echo $this->form->getInput('id'); ?>
</form>
