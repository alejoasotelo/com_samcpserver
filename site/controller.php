<?php
defined('_JEXEC') or die;

class McpserverController extends JControllerLegacy
{
    public function mcp()
    {
        // Deshabilitar buffering y sesiones para JSON-RPC puro
        @ob_end_clean();

        $token = JFactory::getApplication()->input->getString('token', '');

        // Validar token y obtener usuario
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__mcpserver_users'))
            ->where($db->quoteName('token')   . ' = ' . $db->quote($token))
            ->where($db->quoteName('enabled') . ' = 1');

        $db->setQuery($query);
        $mcpUser = $db->loadObject();

        if (!$mcpUser)
        {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            jexit();
        }

        // Actualizar last_used
        $db->setQuery(
            $db->getQuery(true)
                ->update($db->quoteName('#__mcpserver_users'))
                ->set($db->quoteName('last_used') . ' = ' . $db->quote(JFactory::getDate()->toSql()))
                ->where($db->quoteName('id') . ' = ' . (int) $mcpUser->id)
        );
        $db->execute();

        // Despachar al servidor MCP
        JLoader::register('McpserverMcpServer', JPATH_SITE . '/components/com_mcpserver/helpers/mcp/McpServer.php');

        $server = new McpserverMcpServer($mcpUser);
        $server->handle();

        jexit();
    }
}
