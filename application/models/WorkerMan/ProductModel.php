<?php


class ProductModel extends CI_Model
{

    private $table = 'workerman_product';

    public function getProductByPrimaryKey($primaryKey) {
        $res = $this->db->get_where($this->table,array ('id' => $primaryKey));
        return $res->row_array();
    }
}