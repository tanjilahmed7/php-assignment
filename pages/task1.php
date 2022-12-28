<?php
require '../classes/Task1.php';

try {
    // instantiate DB & connect
    $database = new Database();
    $db = $database->connect();

    // instantiate product object
    $task1 = new Task1($db);

    // query products
    $result = $task1->read();

    // make a array
    $task1_arr = array();


    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $task1_item = array(
            'id' => $id,
            'name' => $name,
            'ParentcategoryId' => !empty($ParentcategoryId) ? $ParentcategoryId : 0
        );
        // push to 'data'
        $task1_arr[] = $task1_item;
    }
    // make table name, num_items
    $tree = $task1->buildTree($task1_arr);
    // make table
    $task1->makeTable($tree);



} catch (Exception $e) {
    echo $e->getMessage();
}
