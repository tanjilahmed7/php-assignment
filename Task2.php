<?php
require 'Helper.php';
class Task2{
    use Helper;
    // DB stuff
    private $conn;

    // set connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // get all products
    public function read() {
        $query = 'SELECT category.id, category.name, 
                    catetory_relations.ParentcategoryId FROM category 
                    LEFT JOIN catetory_relations  ON 
                    category.id = catetory_relations.categoryId';

        return $this->query($query);
    }
}

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

// make a menu anchor
function makeMenu($tree) {
    $html = '';
    foreach ($tree as $node) {
        $html .= '<li><a href="#">' . $node['name'] . ' (' . $node['num_items'] . ')</a>';
        if (isset($node['children'])) {
            $html .= '<ul>';
            $html .= makeMenu($node['children']);
            $html .= '</ul>';
        }
        $html .= '</li>';
    }
    return $html;
}

// make a menu
echo '<ul>';
echo makeMenu($tree);
echo '</ul>';

