<?php
require '../classes/Database.php';

trait Helper
{
    private $conn;
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function query($query)
    {
        // prepare statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();
        return $stmt;
    }

    /**
     * @param $categories
     * @param mixed $parentId
     * @param bool $hasChildren
     * @return array
     */
    public function buildTree($categories, mixed $parentId = 0, bool $hasChildren = false): array
    {
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
                $query = 'SELECT category.name, COUNT(DISTINCT item_category_relations.ItemNumber) AS num_items FROM item_category_relations LEFT JOIN category ON category.id = item_category_relations.categoryId WHERE category.id = ' . $category['id'] . ' GROUP BY category.name ORDER BY num_items DESC';

                // prepare statement
                $stmt = $this->conn->prepare($query);
                // execute query
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $node['num_items'] = $result['num_items'] ?? 0;
                // total children num_items add to parent num_items
                $node['num_items'] += $this->getChildrenNumItems($categories, $category['id']);

                // if there have no children then add to tree
                if ($hasChildren) {
                    // Recursively build the tree for the current category
                    $children = $this->buildTree($categories, $category['id'], true);
                    if ($children) {
                        $node['children'] = $children;
                    }
                }

                // Add the category data to the tree
                $tree[] = $node;
            }
        }

        // Return the tree structure
        return $tree;
    }


    /**
     * @param $categories
     * @param int $parentId
     * @return int|mixed
     */
    protected function  getChildrenNumItems($categories, int $parentId = 0): mixed
    {
        $num_items = 0;
        foreach ($categories as $category) {
            if ($category['ParentcategoryId'] == $parentId) {
                $query = 'SELECT category.name, 
                COUNT(DISTINCT item_category_relations.ItemNumber) AS num_items
                FROM item_category_relations 
                LEFT JOIN category ON category.id = item_category_relations.categoryId 
                WHERE category.id = ' . $category['id'] . ' GROUP BY category.name ORDER BY num_items DESC';

                // prepare statement
                $stmt = $this->conn->prepare($query);
                // execute query
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $num_items += $result['num_items'] ?? 0;
                $num_items += $this->getChildrenNumItems($categories, $category['id']);
            }
        }
        return $num_items;
    }

    public function makeMenu($tree): string
    {
        $html = '';
        foreach ($tree as $node) {
            $html .= '<li><a href="#">' . $node['name'] . ' (' . $node['num_items'] . ')</a>';
            if (isset($node['children'])) {
                $html .= '<ul>';
                $html .= $this->makeMenu($node['children']);
                $html .= '</ul>';
            }
            $html .= '</li>';
        }
        return $html;
    }

    /**
     * @param array $tree
     * @return void
     */
    public function makeTable(array $tree = []): void
    {
        echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
        echo '<tr>';
        echo '<th>Category Name</th>';
        echo '<th>Total Items</th>';
        echo '</tr>';
        foreach ($tree as $item) {
            echo '<tr>';
            echo '<td>' . $item['name'] . '</td>';
            echo '<td>' . $item['num_items'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
