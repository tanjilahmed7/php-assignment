<?php
require './classes/Task2.php';

try {
    // instantiate DB & connect
    $database = new Database();
    $db = $database->connect();

    // instantiate product object
    $task2 = new Task2($db);

    // query products
    $result = $task2->read();

    // make a array
    $task2_arr = array();


    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $task2_item = array(
            'id' => $id,
            'name' => $name,
            'ParentcategoryId' => !empty($ParentcategoryId) ? $ParentcategoryId : 0
        );
        // push to 'data'
        $task2_arr[] = $task2_item;
    }
    $tree = $task2->buildTree($task2_arr, null, true);


    // make a menu
    echo '<ul>';
    echo $task2->makeMenu($tree);
    echo '</ul>';
} catch (Exception $e) {
    echo $e->getMessage();
}
