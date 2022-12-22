<?php
require 'Database.php';

/**
 * Class Task1
 * Description: Show all categories with total item and order categories by total Items
 */
class task1{
    // DB stuff
    private $conn;

    // set connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // get all products
    public function read() {
        $query = 'SELECT category.name, COUNT(item_category_relations.categoryId) 
                    AS num_items FROM item_category_relations
                    LEFT JOIN category 
                    ON category.id = item_category_relations.categoryId 
                    GROUP BY category.name ORDER BY num_items DESC';

        // prepare statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();
        return $stmt;
    }
}

// instantiate DB & connect
$database = new Database();
$db = $database->connect();

// instantiate product object
$task1 = new task1($db);

// using try catch to catch errors
try {
    // query products
    $result = $task1->read();
    $num = $result->rowCount();
    if($num > 0) {
        $task1_arr = array();
        $task1_arr['data'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $task1_item = array(
                'name' => $name,
                'num_items' => $num_items
            );
            // push to 'data'
            $task1_arr['data'][] = $task1_item;
        }
        // task1 array data output make a table with the data
        echo '<table border="1" width="100%">';
        echo '<tr>';
        echo '<th>Category Name</th>';
        echo '<th>Number of Items</th>';
        echo '</tr>';
        foreach($task1_arr['data'] as $task1_item) {
            echo '<tr>';
            echo '<td>' . $task1_item['name'] . '</td>';
            echo '<td>' . $task1_item['num_items'] . '</td>';
            echo '</tr>';
        }
    } else {
        // no products
        echo json_encode(
            array('message' => 'No products found')
        );
    }

} catch (PDOException $e) {
    echo $e->getMessage();
}


