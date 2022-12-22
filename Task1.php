<?php
require 'Helper.php';
class Task1  {
    use Helper;
    // get all products
    public function read() {
        return $this->query('SELECT category.id, category.name, 
                    catetory_relations.ParentcategoryId FROM category 
                    LEFT JOIN catetory_relations  ON 
                    category.id = catetory_relations.categoryId');
    }


}

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
echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
echo '<tr>';
echo '<th>Category Name</th>';
echo '<th>Total Items</th>';
echo '</tr>';
foreach ($tree as $item) {
    echo '<tr>';
    echo '<td>'.$item['name'].'</td>';
    echo '<td>'.$item['num_items'].'</td>';
    echo '</tr>';
}

