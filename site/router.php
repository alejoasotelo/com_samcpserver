<?php
defined('_JEXEC') or die;

function McpserverBuildRoute(&$query)
{
    $segments = [];

    if (isset($query['task']))
    {
        $segments[] = $query['task'];
        unset($query['task']);
    }

    return $segments;
}

function McpserverParseRoute($segments)
{
    $vars = [];

    if (!empty($segments[0]))
    {
        $vars['task'] = $segments[0];
    }

    return $vars;
}
