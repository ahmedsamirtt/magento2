<?php
// script.php

// Assume $block is available and contains the necessary methods, and adjust accordingly
if(isset($_POST['pageTitle'])) {
    $pageTitle = $_POST['pageTitle'];
    // Process $pageTitle and generate the necessary data
    $customData = $block->getCustomData($pageTitle);

    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode($customData);
}
?>