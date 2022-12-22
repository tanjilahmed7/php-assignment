<?php
require './utilities/Helper.php';
class Task1  {
    use Helper;

    /**
     * @return mixed
     */
    public function read() {
        return $this->query('SELECT category.id, category.name, 
                    catetory_relations.ParentcategoryId FROM category 
                    LEFT JOIN catetory_relations  ON 
                    category.id = catetory_relations.categoryId');
    }


}

