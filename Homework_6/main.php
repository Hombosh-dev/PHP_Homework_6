<?php

class DatabaseHandler{
    public $pdo;

    private $db_info = [
        'host' => '127.0.0.1',
        'login' => 'root',
        'password' => '',
        'db_name' => 'mysql'
    ];
    public function __construct()
    {
        try{
            $this->pdo = new PDO('mysql:host=' . $this->db_info['host'] . ';dbname=' . $this->db_info['db_name'], $this->db_info['login'], $this->db_info['password']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e){
            echo $e->getMessage();
        }
    }

    public function __destruct()
    {
        $this->pdo = null;
    }

    public function query($sql, $params = [])
    {
        $StatementHandle = $this->pdo->prepare($sql);
        $StatementHandle->execute($params);
        return $StatementHandle->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createTable($tableName, $columns, $foreignKeys = [])
    {
        $columnsSql = [];
        foreach ($columns as $column => $type) {
            $columnsSql[] = "$column $type";
        }

        foreach ($foreignKeys as $foreignKey) {
            $columnsSql[] = "FOREIGN KEY ({$foreignKey['column']}) REFERENCES {$foreignKey['referencedTable']} ({$foreignKey['referencedColumn']})";
        }

        $columnsSql = implode(", ", $columnsSql);
        $sql = "CREATE TABLE IF NOT EXISTS $tableName ($columnsSql) ENGINE=InnoDB";
        $this->query($sql);
    }

    public function addColumns($tableName, $columns)
    {
        $existingColumns = $this->getTableColumns($tableName);

        foreach ($columns as $column => $type) {
            if (!in_array($column, $existingColumns)) {
                $sql = "ALTER TABLE $tableName ADD COLUMN $column $type";
                $this->query($sql);
            }
        }
    }

    public function addRow($tableName, $columns = [], $values = [])
    {
        if (count($columns) !== count($values)) {
            throw new InvalidArgumentException("Кількість стовпців і значень не відповідає.");
        }

        $columnsSql = implode(", ", $columns);

        $quotedValues = array_map(function($value) {
            return "'".$value."'";
        }, $values);

        $placeholders = implode(", ", $quotedValues);

        $sql = "INSERT INTO $tableName ($columnsSql) VALUES ($placeholders)";

        $this->query($sql);
    }

    private function getTableColumns($tableName)
    {
        $sql = "SHOW COLUMNS FROM $tableName";
        $statement = $this->pdo->query($sql);
        $columns = $statement->fetchAll(PDO::FETCH_ASSOC);

        $columnNames = [];
        foreach ($columns as $column) {
            $columnNames[] = $column['Field'];
        }
        return $columnNames;
    }

    public function setTestData(){
        # Додавання секторів | Add sectors
        $this->addRow('Sector', ['Name',], ['Home',]);
        $this->addRow('Sector', ['Name',], ['Electronics',]);
        $this->addRow('Sector', ['Name',], ['Drugs',]);

        # Додавання категорій | Add categories
        $this->addRow('Category', ['Name', 'idSector'], ['Lawn mover', 1]);
        $this->addRow('Category', ['Name', 'idSector'], ['Furniture', 1]);
        $this->addRow('Category', ['Name', 'idSector'], ['Phones', 2]);
        $this->addRow('Category', ['Name', 'idSector'], ['PC`s', 2]);
        $this->addRow('Category', ['Name', 'idSector'], ['With receipt', 3]);
        $this->addRow('Category', ['Name', 'idSector'], ['Without receipt', 3]);

        # Додавання продуктів | Add products
        $this->addRow('Product', ['Name', 'Price', 'idCategory', 'Make', 'Model', 'Country', 'Description'],
            ['Electric Lawn Mower', 15000, 1, 'Bosch', 'ARM 37', 'Germany', 'Powerful electric lawn mower']);

        $this->addRow('Product', ['Name', 'Price', 'idCategory', 'Make', 'Model', 'Country', 'Description'],
            ['Manual Lawn Mower', 7000, 1, 'Fiskars', 'StaySharp', 'Finland', 'Eco-friendly manual lawn mower']);

        $this->addRow('Product', ['Name', 'Price', 'idCategory', 'Make', 'Model', 'Country', 'Description'],
            ['Sofa', 20000, 2, 'IKEA', 'Klippan', 'Sweden', 'Comfortable and stylish sofa']);

        $this->addRow('Product', ['Name', 'Price', 'idCategory', 'Make', 'Model', 'Country', 'Description'],
            ['Dining Table', 15000, 2, 'IKEA', 'Bjursta', 'Sweden', 'Extendable dining table']);

        $this->addRow('Product', ['Name', 'Price', 'idCategory', 'Make', 'Model', 'Country', 'Description'],
            ['Smartphone', 30000, 3, 'Apple', 'iPhone 13', 'USA', 'Latest model smartphone with advanced features']);

        $this->addRow('Product', ['Name', 'Price', 'idCategory', 'Make', 'Model', 'Country', 'Description'],
            ['Android Phone', 15000, 3, 'Samsung', 'Galaxy S21', 'South Korea', 'High-end smartphone with Android OS']);

        $this->addRow('Product', ['Name', 'Price', 'idCategory', 'Make', 'Model', 'Country', 'Description'],
            ['Gaming PC', 60000, 4, 'Alienware', 'Aurora R12', 'USA', 'High-performance gaming PC']);

        $this->addRow('Product', ['Name', 'Price', 'idCategory', 'Make', 'Model', 'Country', 'Description'],
            ['Laptop', 40000, 4, 'Dell', 'XPS 13', 'USA', 'Compact and powerful laptop']);

        $this->addRow('Product', ['Name', 'Price', 'idCategory', 'Make', 'Model', 'Country', 'Description'],
            ['Aspirin', 100, 5, 'Bayer', 'N/A', 'Germany', 'Pain reliever and anti-inflammatory drug available with a receipt']);

        $this->addRow('Product', ['Name', 'Price', 'idCategory', 'Make', 'Model', 'Country', 'Description'],
            ['Antibiotic', 200, 5, 'Pfizer', 'N/A', 'USA', 'Broad-spectrum antibiotic available with a receipt']);

        $this->addRow('Product', ['Name', 'Price', 'idCategory', 'Make', 'Model', 'Country', 'Description'],
            ['Vitamin C', 50, 6, 'Nature Made', 'N/A', 'USA', 'Vitamin supplement available without a receipt']);

        $this->addRow('Product', ['Name', 'Price', 'idCategory', 'Make', 'Model', 'Country', 'Description'],
            ['Cough Syrup', 150, 6, 'Robitussin', 'N/A', 'USA', 'Cough suppressant available without a receipt']);


        # Додавання користувачів | Add users
        $this->addRow('Users', ['Surname', 'Name', 'Login', 'Password', 'Phone', 'Country', 'City'],
            ['Smith', 'Alex', 'Smith', 'password1', '1234567890', 'USA', 'New York']);

        $this->addRow('Users', ['Surname', 'Name', 'Login', 'Password', 'Phone', 'Country', 'City'],
            ['Doe', 'John', 'Don-John', 'password2', '0987654321', 'Canada', 'Toronto']);

        $this->addRow('Users', ['Surname', 'Name', 'Login', 'Password', 'Phone', 'Country', 'City'],
            ['Ivanov', 'Petro', 'Ivanov', 'password3', '1122334455', 'Ukraine', 'Kyiv']);

        # Додавання продуктів у корзину | Adding products to cart.
        $this->addRow('Cart', ['ProductID', 'UserID'], [1, 1]);
        $this->addRow('Cart', ['ProductID', 'UserID'], [2, 2]);
        $this->addRow('Cart', ['ProductID', 'UserID'], [3, 3]);
        $this->addRow('Cart', ['ProductID', 'UserID'], [4, 1]);
        $this->addRow('Cart', ['ProductID', 'UserID'], [5, 2]);
        $this->addRow('Cart', ['ProductID', 'UserID'], [6, 3]);
        $this->addRow('Cart', ['ProductID', 'UserID'], [1, 1]);
        $this->addRow('Cart', ['ProductID', 'UserID'], [3, 2]);
        $this->addRow('Cart', ['ProductID', 'UserID'], [2, 3]);
        $this->addRow('Cart', ['ProductID', 'UserID'], [5, 1]);
        $this->addRow('Cart', ['ProductID', 'UserID'], [6, 2]);
        $this->addRow('Cart', ['ProductID', 'UserID'], [7, 3]);
    }
}

$db = new DatabaseHandler();
$db->createTable('Users', [
    'id' => 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY',
    'Surname' => 'TEXT',
    'Login' => 'TEXT',
    'Password' => 'TEXT',
    'Phone' => 'TEXT',
    'Country' => 'TEXT',
    'City' => 'TEXT',
]);

$db->addColumns('Users', [
    'Name' => 'TEXT'
]);

$db->createTable('Sector', [
    'id' => 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY',
    'Name' => 'TEXT NOT NULL',
]);

$db->createTable('Category', [
    'id' => 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY',
    'Name' => 'TEXT',
    'idSector' => 'INT NOT NULL',
], [
    [
        'column' => 'idSector',
        'referencedTable' => 'Sector',
        'referencedColumn' => 'id'
    ]
]);

$db->createTable('Product', [
    'id' => 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY',
    'Name' => 'TEXT',
    'Price' => 'INT NOT NULL',
    'idCategory' => 'INT NOT NULL',
    'Description' => 'TEXT',
    'Make' => 'TEXT',
    'Model' => 'TEXT',
    'Country' => 'TEXT',
], [
    [
        'column' => 'idCategory',
        'referencedTable' => 'Category',
        'referencedColumn' => 'id'
    ]
]);

$db->createTable('Cart', [
    'id' => 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY',
    'ProductID' => 'INT NOT NULL',
    'UserID' => 'INT NOT NULL',
], [
    [
        'column' => 'ProductID',
        'referencedTable' => 'Product',
        'referencedColumn' => 'id'
    ],
    [
        'column' => 'UserID',
        'referencedTable' => 'Users',
        'referencedColumn' => 'id'
    ]
]);

//$db->setTestData();

// Most popular sector
var_dump($db->query("SELECT 
    s.id AS SectorID,
    s.Name AS SectorName,
    COUNT(ca.id) AS TotalPurchases
FROM 
    Cart ca
JOIN 
    Product p ON ca.ProductID = p.id
JOIN 
    Category cat ON p.idCategory = cat.id
JOIN 
    Sector s ON cat.idSector = s.id
GROUP BY 
    s.id, s.Name
ORDER BY 
    TotalPurchases DESC
LIMIT 1;
"));

// Users who uses electronic products
var_dump($db->query("SELECT 
    u.id AS UserID,
    u.Surname,
    u.Login,
    u.Phone,
    u.Country,
    u.City,
    s.id AS SectorID,
    s.Name AS SectorName
FROM 
    Cart ca
JOIN 
    Product p ON ca.ProductID = p.id
JOIN 
    Category cat ON p.idCategory = cat.id
JOIN 
    Sector s ON cat.idSector = s.id
JOIN 
    Users u ON ca.UserID = u.id
WHERE 
    s.id = 2
GROUP BY 
    u.id, u.Surname, u.Login, u.Phone, u.Country, u.City, s.id, s.Name;
"));


// Count products on category
var_dump($db->query("SELECT 
    c.id AS CategoryID,
    c.Name AS CategoryName,
    COUNT(p.id) AS ProductCount
FROM 
    Category c
LEFT JOIN 
    Product p ON c.id = p.idCategory
GROUP BY 
    c.id, c.Name
ORDER BY 
    ProductCount DESC;
"));


// Sort categories by total prices
var_dump($db->query("SELECT 
    c.id AS CategoryID,
    c.Name AS CategoryName,
    SUM(p.Price) AS AveragePrice
FROM 
    Category c
LEFT JOIN 
    Product p ON c.id = p.idCategory
GROUP BY 
    c.id, c.Name
ORDER BY 
    AveragePrice ASC;
"));


// Sort categories by avg prices
var_dump($db->query("SELECT 
    c.id AS CategoryID,
    c.Name AS CategoryName,
    AVG(p.Price) AS AveragePrice
FROM 
    Category c
LEFT JOIN 
    Product p ON c.id = p.idCategory
GROUP BY 
    c.id, c.Name
ORDER BY 
    AveragePrice ASC;
"));


// The most expensive product purchased by user with ID = 1
var_dump($db->query("SELECT 
    u.id AS UserID,
    u.Surname,
    u.Login,
    u.Phone,
    u.Country,
    u.City,
    MAX(p.Price) AS MaxPrice,
    p.Name AS ProductName
FROM 
    Users u
JOIN 
    Cart ca ON u.id = ca.UserID
JOIN 
    Product p ON ca.ProductID = p.id
JOIN 
    Category cat ON p.idCategory = cat.id
WHERE 
    u.id = 1
GROUP BY 
    u.id, u.Surname, u.Login, u.Phone, u.Country, u.City
ORDER BY 
    MaxPrice DESC
LIMIT 1;"));