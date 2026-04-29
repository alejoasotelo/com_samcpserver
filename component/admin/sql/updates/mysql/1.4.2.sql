-- 1.4.2: Repara articulos sin workflow association en Joomla 4.
-- Los articulos creados via MCP antes de 1.4.2 no tenian fila en #__workflow_associations
-- y por eso no aparecian en el administrador de articulos.

INSERT INTO `#__workflow_associations` (`item_id`, `stage_id`, `extension`)
SELECT
    c.id,
    (
        SELECT ws.id
        FROM `#__workflow_stages` ws
        INNER JOIN `#__workflows` w ON w.id = ws.workflow_id
        WHERE w.extension = 'com_content.article'
        ORDER BY ws.default DESC, ws.ordering ASC, ws.id ASC
        LIMIT 1
    ),
    'com_content.article'
FROM `#__content` c
WHERE NOT EXISTS (
    SELECT 1
    FROM `#__workflow_associations` wa
    WHERE wa.item_id = c.id
      AND wa.extension = 'com_content.article'
)
AND EXISTS (
    SELECT 1 FROM `#__workflow_stages` ws
    INNER JOIN `#__workflows` w ON w.id = ws.workflow_id
    WHERE w.extension = 'com_content.article'
);
