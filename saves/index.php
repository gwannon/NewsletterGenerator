<?php

$files = scandir(__DIR__, SCANDIR_SORT_DESCENDING);
$files = array_diff($files, [".", "..", "index.php"]);
echo json_encode($files);