<?php
require __DIR__ . '/models/ProductModel.php';
require_once 'Model/CategoryModel.php';
class CategoryApiController
{
 private $categoryModel;
private $db;
 public function __construct()
 {
 $this->db = (new Database())->getConnection();
 $this->categoryModel = new CategoryModel($this->db);
 }
}
?>