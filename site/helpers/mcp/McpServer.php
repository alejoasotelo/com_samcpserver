<?php
defined('_JEXEC') or die;

JLoader::register('SamcpserverToolArticles', JPATH_SITE . '/components/com_samcpserver/helpers/mcp/tools/ArticlesTool.php');
JLoader::register('SamcpserverToolCache',    JPATH_SITE . '/components/com_samcpserver/helpers/mcp/tools/CacheTool.php');
JLoader::register('SamcpserverToolMenus',    JPATH_SITE . '/components/com_samcpserver/helpers/mcp/tools/MenusTool.php');

class SamcpserverMcpServer
{
    const MCP_VERSION        = '2024-11-05';
    const SERVER_NAME        = 'joomla-mcp-server';
    const SERVER_VERSION     = '1.3.0';

    /** @var object Usuario MCP (registro de #__samcpserver_users) */
    private $mcpUser;

    /** @var array Tools registradas */
    private $tools = [];

    public function __construct($mcpUser)
    {
        $this->mcpUser = $mcpUser;
        $this->registerTools();
    }

    private function registerTools()
    {
        $tools = [
            new SamcpserverToolArticles($this->mcpUser),
            new SamcpserverToolCache($this->mcpUser),
            new SamcpserverToolMenus($this->mcpUser),
        ];

        foreach ($tools as $toolInstance)
        {
            foreach ($toolInstance->getDefinitions() as $def)
            {
                $this->tools[$def['name']] = [
                    'definition' => $def,
                    'handler'    => [$toolInstance, $def['handler']],
                ];
            }
        }
    }

    public function handle()
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS')
        {
            http_response_code(200);
            return;
        }

        $raw     = file_get_contents('php://input');
        $request = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($request['method']))
        {
            $this->sendError(null, -32700, 'Parse error');
            return;
        }

        $id     = isset($request['id']) ? $request['id'] : null;
        $method = $request['method'];
        $params = isset($request['params']) ? $request['params'] : [];

        switch ($method)
        {
            case 'initialize':
                $this->handleInitialize($id, $params);
                break;

            case 'notifications/initialized':
                // No-op, solo ACK
                http_response_code(204);
                break;

            case 'tools/list':
                $this->handleToolsList($id);
                break;

            case 'tools/call':
                $this->handleToolsCall($id, $params);
                break;

            case 'ping':
                $this->sendResult($id, []);
                break;

            default:
                $this->sendError($id, -32601, 'Method not found: ' . $method);
        }
    }

    private function handleInitialize($id, $params)
    {
        $this->sendResult($id, [
            'protocolVersion' => self::MCP_VERSION,
            'capabilities'    => [
                'tools' => ['listChanged' => false],
            ],
            'serverInfo' => [
                'name'    => self::SERVER_NAME,
                'version' => self::SERVER_VERSION,
            ],
        ]);
    }

    private function handleToolsList($id)
    {
        $tools = [];

        foreach ($this->tools as $name => $tool)
        {
            $def    = $tool['definition'];
            $tools[] = [
                'name'        => $name,
                'description' => $def['description'],
                'inputSchema' => $def['inputSchema'],
            ];
        }

        $this->sendResult($id, ['tools' => $tools]);
    }

    private function handleToolsCall($id, $params)
    {
        $name      = isset($params['name']) ? $params['name'] : '';
        $arguments = isset($params['arguments']) ? $params['arguments'] : [];

        if (!isset($this->tools[$name]))
        {
            $this->sendError($id, -32602, 'Unknown tool: ' . $name);
            return;
        }

        try
        {
            $handler = $this->tools[$name]['handler'];
            $result  = call_user_func($handler, $arguments);

            $this->sendResult($id, [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => is_string($result) ? $result : json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                    ]
                ],
            ]);
        }
        catch (Exception $e)
        {
            $this->sendResult($id, [
                'content' => [
                    ['type' => 'text', 'text' => 'Error: ' . $e->getMessage()]
                ],
                'isError' => true,
            ]);
        }
    }

    private function sendResult($id, $result)
    {
        echo json_encode([
            'jsonrpc' => '2.0',
            'id'      => $id,
            'result'  => $result,
        ], JSON_UNESCAPED_UNICODE);
    }

    private function sendError($id, $code, $message)
    {
        echo json_encode([
            'jsonrpc' => '2.0',
            'id'      => $id,
            'error'   => [
                'code'    => $code,
                'message' => $message,
            ],
        ], JSON_UNESCAPED_UNICODE);
    }
}
