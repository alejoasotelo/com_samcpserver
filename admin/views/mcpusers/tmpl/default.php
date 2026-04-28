<?php
defined('_JEXEC') or die;

$isJ4      = version_compare(JVERSION, '4.0.0', '>=');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

if ($isJ4)
{
    /** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
    $wa = $this->document->getWebAssetManager();
    $wa->useScript('multiselect');
}
else
{
    JHtml::_('bootstrap.tooltip');
    JHtml::_('behavior.multiselect');
    JHtml::_('formbehavior.chosen', 'select');
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_samcpserver&view=mcpusers'); ?>"
      method="post" name="adminForm" id="adminForm">

    <?php if ($isJ4): ?>
    <?php /* Barra de búsqueda Joomla 4 */ ?>
    <div class="row mb-3">
        <div class="col-md-6 d-flex gap-2">
            <input type="text" name="filter_search" id="filter_search"
                   class="form-control"
                   placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>"
                   value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
            <button type="submit" class="btn btn-primary">
                <span class="icon-search" aria-hidden="true"></span>
            </button>
            <button type="button" class="btn btn-secondary"
                    onclick="document.getElementById('filter_search').value='';this.form.submit();">
                <span class="icon-times" aria-hidden="true"></span>
            </button>
        </div>
        <div class="col-md-3 ms-auto">
            <select name="filter_enabled" class="form-select" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
                <option value="1" <?php echo $this->state->get('filter.enabled') === '1' ? 'selected' : ''; ?>><?php echo JText::_('JENABLED'); ?></option>
                <option value="0" <?php echo $this->state->get('filter.enabled') === '0' ? 'selected' : ''; ?>><?php echo JText::_('JDISABLED'); ?></option>
            </select>
        </div>
    </div>

    <table class="table" id="samcpuserList">
        <thead>
            <tr>
                <th class="w-1 text-center"><?php echo JHtml::_('grid.checkall'); ?></th>
                <th class="w-1 text-center"><?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.enabled', $listDirn, $listOrder); ?></th>
                <th><?php echo JHtml::_('grid.sort', 'COM_SAMCPSERVER_COL_USER', 'u.name', $listDirn, $listOrder); ?></th>
                <th><?php echo JText::_('COM_SAMCPSERVER_COL_NOTE'); ?></th>
                <th class="d-none d-md-table-cell"><?php echo JHtml::_('grid.sort', 'COM_SAMCPSERVER_COL_LAST_USED', 'a.last_used', $listDirn, $listOrder); ?></th>
                <th class="d-none d-md-table-cell"><?php echo JHtml::_('grid.sort', 'COM_SAMCPSERVER_COL_CREATED', 'a.created', $listDirn, $listOrder); ?></th>
                <th class="w-1 text-center d-none d-md-table-cell"><?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($this->items)): ?>
            <tr><td colspan="7" class="text-center"><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></td></tr>
        <?php else: ?>
            <?php foreach ($this->items as $i => $item): ?>
            <tr>
                <td class="text-center"><?php echo JHtml::_('grid.id', $i, $item->id); ?></td>
                <td class="text-center"><?php echo JHtml::_('jgrid.published', $item->enabled, $i, 'mcpusers.', true, 'cb'); ?></td>
                <td>
                    <a href="<?php echo JRoute::_('index.php?option=com_samcpserver&task=mcpuser.edit&id=' . $item->id); ?>">
                        <?php echo $this->escape($item->user_name); ?>
                    </a>
                    <div><small class="text-muted"><?php echo $this->escape($item->user_username); ?></small></div>
                </td>
                <td><?php echo $item->note ? $this->escape($item->note) : '<span class="text-muted">&mdash;</span>'; ?></td>
                <td class="d-none d-md-table-cell small">
                    <?php echo $item->last_used ? JHtml::_('date', $item->last_used, JText::_('DATE_FORMAT_LC4')) : '<span class="text-muted">' . JText::_('COM_SAMCPSERVER_NEVER') . '</span>'; ?>
                </td>
                <td class="d-none d-md-table-cell small"><?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?></td>
                <td class="text-center d-none d-md-table-cell"><?php echo $item->id; ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7"><?php echo $this->pagination->getListFooter(); ?></td>
            </tr>
        </tfoot>
    </table>

    <?php else: ?>
    <?php /* Barra de búsqueda Joomla 3 */ ?>
    <div id="filter-bar" class="btn-toolbar">
        <div class="filter-search btn-group pull-left">
            <label for="filter_search" class="element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
            <input type="text" name="filter_search" id="filter_search"
                   placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>"
                   value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                   class="hasTooltip" />
        </div>
        <div class="btn-group pull-left">
            <button type="submit" class="btn hasTooltip">
                <span class="icon-search"></span>
            </button>
            <button type="button" class="btn hasTooltip"
                    onclick="document.getElementById('filter_search').value='';this.form.submit();">
                <span class="icon-remove"></span>
            </button>
        </div>
        <div class="btn-group pull-right hidden-phone">
            <?php echo JHtml::_('select.genericlist',
                [
                    JHtml::_('select.option', '',  JText::_('JOPTION_SELECT_PUBLISHED')),
                    JHtml::_('select.option', '1', JText::_('JENABLED')),
                    JHtml::_('select.option', '0', JText::_('JDISABLED')),
                ],
                'filter_enabled', '', 'value', 'text', $this->state->get('filter.enabled')
            ); ?>
        </div>
    </div>
    <div class="clearfix"></div>

    <table class="table table-striped" id="samcpuserList">
        <thead>
            <tr>
                <th width="1%"><?php echo JHtml::_('grid.checkall'); ?></th>
                <th width="1%"><?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.enabled', $listDirn, $listOrder); ?></th>
                <th><?php echo JHtml::_('grid.sort', 'COM_SAMCPSERVER_COL_USER', 'u.name', $listDirn, $listOrder); ?></th>
                <th><?php echo JText::_('COM_SAMCPSERVER_COL_NOTE'); ?></th>
                <th class="hidden-phone"><?php echo JHtml::_('grid.sort', 'COM_SAMCPSERVER_COL_LAST_USED', 'a.last_used', $listDirn, $listOrder); ?></th>
                <th class="hidden-phone" width="8%"><?php echo JHtml::_('grid.sort', 'COM_SAMCPSERVER_COL_CREATED', 'a.created', $listDirn, $listOrder); ?></th>
                <th width="1%" class="nowrap hidden-phone"><?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?></th>
            </tr>
        </thead>
        <tfoot>
            <tr><td colspan="7"><?php echo $this->pagination->getListFooter(); ?></td></tr>
        </tfoot>
        <tbody>
        <?php if (empty($this->items)): ?>
            <tr><td colspan="7" class="center"><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></td></tr>
        <?php else: ?>
            <?php foreach ($this->items as $i => $item): ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td class="center"><?php echo JHtml::_('grid.id', $i, $item->id); ?></td>
                <td class="center"><?php echo JHtml::_('jgrid.published', $item->enabled, $i, 'mcpusers.', true, 'cb'); ?></td>
                <td>
                    <a href="<?php echo JRoute::_('index.php?option=com_samcpserver&task=mcpuser.edit&id=' . $item->id); ?>">
                        <?php echo $this->escape($item->user_name); ?>
                    </a>
                    <small class="muted"><?php echo $this->escape($item->user_username); ?></small>
                </td>
                <td><?php echo $item->note ? $this->escape($item->note) : '<span class="muted">&mdash;</span>'; ?></td>
                <td class="hidden-phone small">
                    <?php echo $item->last_used ? JHtml::_('date', $item->last_used, JText::_('DATE_FORMAT_LC4')) : '<span class="muted">' . JText::_('COM_SAMCPSERVER_NEVER') . '</span>'; ?>
                </td>
                <td class="hidden-phone small"><?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?></td>
                <td class="hidden-phone center"><?php echo $item->id; ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <input type="hidden" name="task"       value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo JHtml::_('form.token'); ?>
</form>
