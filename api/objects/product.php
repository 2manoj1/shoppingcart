<?php
class Product{
 
    // database connection and table name
    private $conn;
    private $table_name = "product";
 
    // object properties
    public $id;
    public $name;
    public $price;
    public $weight;

    public $listids;
 
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    // read products
function read(){
 
    // select all query
    $query = "SELECT
                p.id, p.name, p.price, p.weight
            FROM
                " . $this->table_name . " p
            ORDER BY
                p.name ASC";
 
    // prepare query statement
    $stmt = $this->conn->prepare($query);
 
    // execute query
    $stmt->execute();
 
    return $stmt;
}

// read products by id
function readByIds(){
 
    $query = "SELECT
                p.id, p.name, p.price, p.weight
            FROM
                " . $this->table_name . " p
            WHERE p.id in (" . $this->listids . ")
            ORDER BY
                p.price DESC";
 
    // prepare query statement
    $stmt = $this->conn->prepare($query);
 
    // execute query
    $stmt->execute();
 
    return $stmt;
}

// create product
function create(){
 
    // query to insert record
    $query = "INSERT INTO
                " . $this->table_name . "
            SET
                name=:name, price=:price, weight=:weight";
 
    // prepare query
    $stmt = $this->conn->prepare($query);
 
    // sanitize
    $this->name=htmlspecialchars(strip_tags($this->name));
    $this->price=htmlspecialchars(strip_tags($this->price));
    $this->weight=htmlspecialchars(strip_tags($this->weight));

 
    // bind values
    $stmt->bindParam(":name", $this->name);
    $stmt->bindParam(":price", $this->price);
    $stmt->bindParam(":weight", $this->weight);
 
    // execute query
    if($stmt->execute()){
        return true;
    }
 
    return false;
     
}

}