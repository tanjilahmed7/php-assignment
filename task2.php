<?php
require 'Database.php';
class Task2{
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
        // prepare statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();
        return $stmt;
    }
    // Define a function to build the tree structure
    public function buildTree($categories, $parentId = 0) {
        // Initialize an empty array to store the tree structure
        $tree = array();

        // Loop through the categories
        foreach ($categories as $category) {

            // If the category's parent ID matches the current parent ID, add it to the tree
            if ($category['ParentcategoryId'] == $parentId) {
                // Initialize an array to store the category data
                $node = array(
                    'name' => $category['name'],
                    'id' => $category['id'],
                );

                // category id to find item_category_relations table join with category table and another join with
                // item table with itemsNumber with number get total items
                $query = 'SELECT category.name, COUNT(item_category_relations.categoryId) AS num_items FROM item_category_relations LEFT JOIN category ON category.id = item_category_relations.categoryId WHERE category.id = '. $category['id'].' GROUP BY category.name ORDER BY num_items DESC;';

                // prepare statement
                $stmt = $this->conn->prepare($query);
                // execute query
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $node['num_items'] = $result['num_items'];
                // total children num_items add to parent num_items
                $node['num_items'] += $this->getChildrenNumItems($categories, $category['id']);

                // if there no children then add to tree
                if ($this->hasChildren($categories, $category['id'])) {
                    // Recursively call the buildTree function to add the children
                    $node['children'] = $this->buildTree($categories, $category['id']);
                }


                // Add the category data to the tree
                $tree[] = $node;
            }
        }

        // Return the tree structure
        return $tree;
    }

    protected function  getChildrenNumItems($categories, $parentId = 0) {
        $num_items = 0;
        foreach ($categories as $category) {
            if ($category['ParentcategoryId'] == $parentId) {
                $query = 'SELECT category.name, COUNT(item_category_relations.categoryId) AS num_items FROM item_category_relations LEFT JOIN category ON category.id = item_category_relations.categoryId WHERE category.id = '. $category['id'].' GROUP BY category.name ORDER BY num_items DESC;';

                // prepare statement
                $stmt = $this->conn->prepare($query);
                // execute query
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $num_items += $result['num_items'];
                $num_items += $this->getChildrenNumItems($categories, $category['id']);
            }
        }
        return $num_items;
    }

    protected function hasChildren($categories, $parentId = 0) {
        foreach ($categories as $category) {
            if ($category['ParentcategoryId'] == $parentId) {
                return true;
            }
        }
        return false;
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
$tree = $task2->buildTree($task2_arr);

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


