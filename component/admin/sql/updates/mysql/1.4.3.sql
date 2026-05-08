-- 1.4.3: Correccion de bug en 1.4.2 donde se usaba w.extension = 'com_content.article'
-- en lugar de 'com_content' al consultar #__workflows.
-- Repara articulos sin workflow association en instalaciones que ya ejecutaron 1.4.2.

INSERT INTO `#__workflow_associations` (`item_id`, `stage_id`, `extension`)
SELECT
    c.id,
    (
        SELECT ws.id
        FROM `#__workflow_stages` ws
        INNER JOIN `#__workflows` w ON w.id = ws.workflow_id
        WHERE w.extension = 'com_content'
          AND w.published = 1
          AND ws.published = 1
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
    WHERE w.extension = 'com_content'
      AND w.published = 1
      AND ws.published = 1
);
