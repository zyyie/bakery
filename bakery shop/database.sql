-- Bakery Management System Database

CREATE DATABASE IF NOT EXISTS DB_bakery;
USE DB_bakery;

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    categoryID INT AUTO_INCREMENT PRIMARY KEY,
    categoryName VARCHAR(255) NOT NULL,
    creationDate DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Items/Food Packages Table
CREATE TABLE IF NOT EXISTS items (
    itemID INT AUTO_INCREMENT PRIMARY KEY,
    packageName VARCHAR(255) NOT NULL,
    foodDescription TEXT,
    itemContains TEXT,
    categoryID INT,
    itemImage VARCHAR(255),
    size VARCHAR(50),
    status VARCHAR(20) DEFAULT 'Active',
    suitableFor INT,
    price DECIMAL(10,2) NOT NULL,
    creationDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoryID) REFERENCES categories(categoryID)
);

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    fullName VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mobileNumber VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    regDate DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Admin Table
CREATE TABLE IF NOT EXISTS admin (
    adminID INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    orderID INT AUTO_INCREMENT PRIMARY KEY,
    orderNumber VARCHAR(50) UNIQUE NOT NULL,
    userID INT,
    fullName VARCHAR(255) NOT NULL,
    contactNumber VARCHAR(20) NOT NULL,
    orderDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    deliveryDate DATE,
    flatNumber VARCHAR(50),
    streetName VARCHAR(255),
    area VARCHAR(255),
    landmark VARCHAR(255),
    city VARCHAR(255),
    zipcode VARCHAR(20),
    state VARCHAR(100),
    orderStatus VARCHAR(50) DEFAULT 'Still Pending',
    remark TEXT,
    FOREIGN KEY (userID) REFERENCES users(userID)
);

-- Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    orderItemID INT AUTO_INCREMENT PRIMARY KEY,
    orderID INT,
    itemID INT,
    quantity INT NOT NULL,
    unitPrice DECIMAL(10,2) NOT NULL,
    totalPrice DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (orderID) REFERENCES orders(orderID),
    FOREIGN KEY (itemID) REFERENCES items(itemID)
);

-- Enquiries Table
CREATE TABLE IF NOT EXISTS enquiries (
    enquiryID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    mobileNumber VARCHAR(20),
    message TEXT,
    enquiryDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'Unread'
);

-- Subscribers Table
CREATE TABLE IF NOT EXISTS subscribers (
    subscriberID INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    subscribingDate DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Pages Table (About Us, Contact Us)
CREATE TABLE IF NOT EXISTS pages (
    pageID INT AUTO_INCREMENT PRIMARY KEY,
    pageTitle VARCHAR(255) NOT NULL,
    pageDescription TEXT,
    email VARCHAR(255),
    mobileNumber VARCHAR(20),
    pageType VARCHAR(50) UNIQUE NOT NULL
);

-- Insert default admin
INSERT INTO admin (username, password) VALUES ('admin', MD5('hotdog12345678'));

-- Insert bread categories
INSERT INTO categories (categoryName) VALUES 
('Classic & Basic Bread'),
('Sweet Bread'),
('Filled / Stuffed Bread'),
('Buns & Rolls'),
('Bread–Cake Combo'),
('Special (Budget-Friendly)');

-- Insert bread items
-- Classic & Basic Bread (categoryID = 1)
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Pandesal (plain)', 'Classic Filipino bread roll, 3-5 pcs per pack', 'Flour, Yeast, Sugar, Salt', 1, 50.00, 'Active'),
('Buttered pandesal', 'Soft pandesal with butter spread', 'Flour, Yeast, Sugar, Salt, Butter', 1, 60.00, 'Active'),
('Malunggay pandesal', 'Nutritious pandesal with malunggay leaves', 'Flour, Yeast, Sugar, Salt, Malunggay', 1, 55.00, 'Active'),
('Wheat pandesal', 'Healthy whole wheat pandesal', 'Whole Wheat Flour, Yeast, Sugar, Salt', 1, 60.00, 'Active'),
('Spanish bread', 'Sweet bread roll with butter and sugar filling', 'Flour, Yeast, Sugar, Butter', 1, 45.00, 'Active'),
('Cheese bread', 'Soft bread with cheese filling', 'Flour, Yeast, Sugar, Cheese', 1, 55.00, 'Active');

-- Sweet Bread (categoryID = 2)
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Ensaymada (mini)', 'Mini sweet bread topped with butter, sugar, and cheese', 'Flour, Yeast, Sugar, Butter, Cheese', 2, 40.00, 'Active'),
('Ube cheese bread', 'Purple yam bread with cheese topping', 'Flour, Yeast, Ube, Cheese', 2, 50.00, 'Active'),
('Chocolate bread / Choco roll', 'Sweet bread with chocolate filling', 'Flour, Yeast, Sugar, Chocolate', 2, 45.00, 'Active'),
('Cream bread', 'Soft bread with sweet cream filling', 'Flour, Yeast, Sugar, Cream', 2, 50.00, 'Active'),
('Custard bread', 'Bread filled with creamy custard', 'Flour, Yeast, Sugar, Custard', 2, 50.00, 'Active'),
('Monggo bread', 'Sweet bread with mung bean filling', 'Flour, Yeast, Sugar, Mung Beans', 2, 45.00, 'Active'),
('Strawberry bread', 'Bread with strawberry jam filling', 'Flour, Yeast, Sugar, Strawberry Jam', 2, 50.00, 'Active'),
('Pineapple bread', 'Bread with pineapple filling', 'Flour, Yeast, Sugar, Pineapple', 2, 50.00, 'Active');

-- Filled / Stuffed Bread (categoryID = 3)
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Ham & cheese bread', 'Bread stuffed with ham and cheese', 'Flour, Yeast, Ham, Cheese', 3, 65.00, 'Active'),
('Hotdog roll', 'Soft roll with hotdog filling', 'Flour, Yeast, Hotdog', 3, 45.00, 'Active'),
('Sausage roll', 'Bread roll with sausage', 'Flour, Yeast, Sausage', 3, 50.00, 'Active'),
('Tuna bread', 'Bread filled with tuna spread', 'Flour, Yeast, Tuna, Mayonnaise', 3, 55.00, 'Active'),
('Chicken bread', 'Bread stuffed with chicken filling', 'Flour, Yeast, Chicken', 3, 60.00, 'Active'),
('Cheese stick bread', 'Bread with cheese stick filling', 'Flour, Yeast, Cheese', 3, 50.00, 'Active');

-- Buns & Rolls (categoryID = 4)
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Mini burger bun (with filling)', 'Small burger bun with filling', 'Flour, Yeast, Sugar, Filling', 4, 40.00, 'Active'),
('Dinner rolls', 'Soft dinner rolls, 3-4 pcs per pack', 'Flour, Yeast, Sugar, Butter', 4, 55.00, 'Active'),
('Soft roll bread', 'Soft and fluffy bread rolls', 'Flour, Yeast, Sugar, Milk', 4, 45.00, 'Active');

-- Bread–Cake Combo (categoryID = 5)
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Mini banana bread slice', 'Moist banana bread slice', 'Flour, Banana, Sugar, Eggs', 5, 35.00, 'Active'),
('Mini chiffon cake slice', 'Light and airy chiffon cake slice', 'Flour, Eggs, Sugar, Oil', 5, 40.00, 'Active'),
('Mini pound cake', 'Rich and buttery pound cake', 'Flour, Butter, Sugar, Eggs', 5, 45.00, 'Active'),
('Cupcake', 'Delicious cupcakes, 1-2 pcs per pack', 'Flour, Sugar, Eggs, Frosting', 5, 50.00, 'Active');

-- Special (Budget-Friendly) (categoryID = 6)
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Garlic bread sticks', 'Crispy bread sticks with garlic butter', 'Flour, Yeast, Garlic, Butter', 6, 35.00, 'Active'),
('Cheese garlic roll', 'Soft roll with cheese and garlic', 'Flour, Yeast, Cheese, Garlic', 6, 40.00, 'Active'),
('Cinnamon roll (mini)', 'Sweet mini cinnamon rolls', 'Flour, Yeast, Cinnamon, Sugar', 6, 35.00, 'Active'),
('Pandesal bites (assorted flavors)', 'Small pandesal bites in assorted flavors', 'Flour, Yeast, Sugar, Various Flavors', 6, 45.00, 'Active');

-- Insert default pages
INSERT INTO pages (pageTitle, pageDescription, email, mobileNumber, pageType) VALUES
('About Us', 'We are known as the best catering company in Seattle for good reason. Our dedication and commitment to quality and sustainability has earned us a loyal following among our clientele, one that continues to grow based on enthusiastic referrals. For nearly two decades, we have bridged the gap between the land, the sea, and your table. We leverage the best ingredients Washington has to offer, preparing them mindfully and always from scratch.', '', '', 'aboutus'),
('Contact Us', 'Your Business Address Here', 'your-email@example.com', '+63 XXX XXX XXXX', 'contactus');

