UPDATE `#__menu`
SET `link` = 'index.php?option=com_samcpserver'
WHERE `component_id` = (
    SELECT `extension_id`
    FROM `#__extensions`
    WHERE `element` = 'com_samcpserver'
);
