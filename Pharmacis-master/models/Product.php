<?php
// Filename: models/Product.php
// Yeh file Products ke CRUD operations (Create, Read, Delete) handle karti hai.

class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $name;
    public $category;
    public $price;
    public $stock_quantity;

    public function __construct($db) {
        $this->conn = $db;
    }

    // READ (Saray products lana)
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // CREATE (Naya product add karna)
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET name=:name, category=:category, price=:price, stock_quantity=:stock";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->stock_quantity = htmlspecialchars(strip_tags($this->stock_quantity));

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":stock", $this->stock_quantity);

        return $stmt->execute();
    }

    // DELETE (Product remove karna)
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }
}
?>