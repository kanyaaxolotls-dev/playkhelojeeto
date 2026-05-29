-- Run once on live DB to fix duplicate distributor/dealer sidebar menus
-- Keeps the lowest id per panel+route_key; disables duplicates

UPDATE tbl_task_manager t
INNER JOIN (
    SELECT panel, route_key, MIN(id) AS keep_id
    FROM tbl_task_manager
    WHERE status = 1 AND route_key IS NOT NULL AND route_key != ''
    GROUP BY panel, route_key
    HAVING COUNT(*) > 1
) d ON t.panel = d.panel AND t.route_key = d.route_key AND t.id != d.keep_id
SET t.status = 0;

-- Rebuild system role task lists (adjust ids if your system roles differ)
UPDATE tbl_roles SET tasks = (
    SELECT GROUP_CONCAT(id ORDER BY position SEPARATOR ',')
    FROM (
        SELECT MIN(id) AS id, position
        FROM tbl_task_manager
        WHERE panel = 'distributor' AND status = 1 AND url != '#'
        GROUP BY route_key
    ) x
) WHERE id = 2;

UPDATE tbl_roles SET tasks = (
    SELECT GROUP_CONCAT(id ORDER BY position SEPARATOR ',')
    FROM (
        SELECT MIN(id) AS id, position
        FROM tbl_task_manager
        WHERE panel = 'dealer' AND status = 1 AND url != '#'
        GROUP BY route_key
    ) x
) WHERE id = 3;
