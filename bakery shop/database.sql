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
    social_id VARCHAR(255) NULL,
    social_provider VARCHAR(50) NULL,
    regDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_social_id (social_id),
    INDEX idx_social_provider (social_provider)
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

-- Product Reviews Table
CREATE TABLE IF NOT EXISTS product_reviews (
    reviewID INT AUTO_INCREMENT PRIMARY KEY,
    itemID INT NOT NULL,
    userID INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    reviewDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'Approved',
    FOREIGN KEY (itemID) REFERENCES items(itemID) ON DELETE CASCADE,
    FOREIGN KEY (userID) REFERENCES users(userID) ON DELETE CASCADE,
    INDEX idx_itemID (itemID),
    INDEX idx_userID (userID),
    INDEX idx_reviewDate (reviewDate)
);

-- Insert default admin
-- Note: Password is stored as MD5 for initial setup. It will be automatically upgraded to bcrypt
-- on first successful login. Default credentials: admin / admin123
-- To manually update to bcrypt, run: UPDATE admin SET password = '$2y$10$...' WHERE username = 'admin';
INSERT INTO admin (username, password) VALUES ('admin', MD5('admin123'));

-- Insert all categories
INSERT INTO categories (categoryName) VALUES 
('Classic & Basic Bread'),
('Sweet Bread'),
('Filled / Stuffed Bread'),
('Buns & Rolls'),
('Bread–Cake Combo'),
('Special (Budget-Friendly)'),
('Cookies'),
('Crinkles'),
('Brownies');

-- Insert bread items
-- Classic & Basic Bread
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Pandesal (plain)', 'Classic Filipino bread roll, 3-5 pcs per pack', 'Flour, Yeast, Sugar, Salt', (SELECT categoryID FROM categories WHERE categoryName = 'Classic & Basic Bread'), 50.00, 'Active'),
('Buttered pandesal', 'Soft pandesal with butter spread', 'Flour, Yeast, Sugar, Salt, Butter', (SELECT categoryID FROM categories WHERE categoryName = 'Classic & Basic Bread'), 60.00, 'Active'),
('Malunggay pandesal', 'Nutritious pandesal with malunggay leaves', 'Flour, Yeast, Sugar, Salt, Malunggay', (SELECT categoryID FROM categories WHERE categoryName = 'Classic & Basic Bread'), 55.00, 'Active'),
('Wheat pandesal', 'Healthy whole wheat pandesal', 'Whole Wheat Flour, Yeast, Sugar, Salt', (SELECT categoryID FROM categories WHERE categoryName = 'Classic & Basic Bread'), 60.00, 'Active'),
('Spanish bread', 'Sweet bread roll with butter and sugar filling', 'Flour, Yeast, Sugar, Butter', (SELECT categoryID FROM categories WHERE categoryName = 'Classic & Basic Bread'), 45.00, 'Active'),
('Cheese bread', 'Soft bread with cheese filling', 'Flour, Yeast, Sugar, Cheese', (SELECT categoryID FROM categories WHERE categoryName = 'Classic & Basic Bread'), 55.00, 'Active');

-- Sweet Bread
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Ensaymada (mini)', 'Mini sweet bread topped with butter, sugar, and cheese', 'Flour, Yeast, Sugar, Butter, Cheese', (SELECT categoryID FROM categories WHERE categoryName = 'Sweet Bread'), 40.00, 'Active'),
('Ube cheese bread', 'Purple yam bread with cheese topping', 'Flour, Yeast, Ube, Cheese', (SELECT categoryID FROM categories WHERE categoryName = 'Sweet Bread'), 50.00, 'Active'),
('Chocolate bread / Choco roll', 'Sweet bread with chocolate filling', 'Flour, Yeast, Sugar, Chocolate', (SELECT categoryID FROM categories WHERE categoryName = 'Sweet Bread'), 45.00, 'Active'),
('Cream bread', 'Soft bread with sweet cream filling', 'Flour, Yeast, Sugar, Cream', (SELECT categoryID FROM categories WHERE categoryName = 'Sweet Bread'), 50.00, 'Active'),
('Custard bread', 'Bread filled with creamy custard', 'Flour, Yeast, Sugar, Custard', (SELECT categoryID FROM categories WHERE categoryName = 'Sweet Bread'), 50.00, 'Active'),
('Monggo bread', 'Sweet bread with mung bean filling', 'Flour, Yeast, Sugar, Mung Beans', (SELECT categoryID FROM categories WHERE categoryName = 'Sweet Bread'), 45.00, 'Active'),
('Strawberry bread', 'Bread with strawberry jam filling', 'Flour, Yeast, Sugar, Strawberry Jam', (SELECT categoryID FROM categories WHERE categoryName = 'Sweet Bread'), 50.00, 'Active'),
('Pineapple bread', 'Bread with pineapple filling', 'Flour, Yeast, Sugar, Pineapple', (SELECT categoryID FROM categories WHERE categoryName = 'Sweet Bread'), 50.00, 'Active');

-- Filled / Stuffed Bread
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Ham & cheese bread', 'Bread stuffed with ham and cheese', 'Flour, Yeast, Ham, Cheese', (SELECT categoryID FROM categories WHERE categoryName = 'Filled / Stuffed Bread'), 65.00, 'Active'),
('Hotdog roll', 'Soft roll with hotdog filling', 'Flour, Yeast, Hotdog', (SELECT categoryID FROM categories WHERE categoryName = 'Filled / Stuffed Bread'), 45.00, 'Active'),
('Sausage roll', 'Bread roll with sausage', 'Flour, Yeast, Sausage', (SELECT categoryID FROM categories WHERE categoryName = 'Filled / Stuffed Bread'), 50.00, 'Active'),
('Tuna bread', 'Bread filled with tuna spread', 'Flour, Yeast, Tuna, Mayonnaise', (SELECT categoryID FROM categories WHERE categoryName = 'Filled / Stuffed Bread'), 55.00, 'Active'),
('Chicken bread', 'Bread stuffed with chicken filling', 'Flour, Yeast, Chicken', (SELECT categoryID FROM categories WHERE categoryName = 'Filled / Stuffed Bread'), 60.00, 'Active'),
('Cheese stick bread', 'Bread with cheese stick filling', 'Flour, Yeast, Cheese', (SELECT categoryID FROM categories WHERE categoryName = 'Filled / Stuffed Bread'), 50.00, 'Active');

-- Buns & Rolls
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Mini burger bun (with filling)', 'Small burger bun with filling', 'Flour, Yeast, Sugar, Filling', (SELECT categoryID FROM categories WHERE categoryName = 'Buns & Rolls'), 40.00, 'Active'),
('Dinner rolls', 'Soft dinner rolls, 3-4 pcs per pack', 'Flour, Yeast, Sugar, Butter', (SELECT categoryID FROM categories WHERE categoryName = 'Buns & Rolls'), 55.00, 'Active'),
('Soft roll bread', 'Soft and fluffy bread rolls', 'Flour, Yeast, Sugar, Milk', (SELECT categoryID FROM categories WHERE categoryName = 'Buns & Rolls'), 45.00, 'Active');

-- Bread–Cake Combo
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Mini banana bread slice', 'Moist banana bread slice', 'Flour, Banana, Sugar, Eggs', (SELECT categoryID FROM categories WHERE categoryName = 'Bread–Cake Combo'), 35.00, 'Active'),
('Mini chiffon cake slice', 'Light and airy chiffon cake slice', 'Flour, Eggs, Sugar, Oil', (SELECT categoryID FROM categories WHERE categoryName = 'Bread–Cake Combo'), 40.00, 'Active'),
('Mini pound cake', 'Rich and buttery pound cake', 'Flour, Butter, Sugar, Eggs', (SELECT categoryID FROM categories WHERE categoryName = 'Bread–Cake Combo'), 45.00, 'Active'),
('Cupcake', 'Delicious cupcakes, 1-2 pcs per pack', 'Flour, Sugar, Eggs, Frosting', (SELECT categoryID FROM categories WHERE categoryName = 'Bread–Cake Combo'), 50.00, 'Active');

-- Special (Budget-Friendly)
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Garlic bread sticks', 'Crispy bread sticks with garlic butter', 'Flour, Yeast, Garlic, Butter', (SELECT categoryID FROM categories WHERE categoryName = 'Special (Budget-Friendly)'), 35.00, 'Active'),
('Cheese garlic roll', 'Soft roll with cheese and garlic', 'Flour, Yeast, Cheese, Garlic', (SELECT categoryID FROM categories WHERE categoryName = 'Special (Budget-Friendly)'), 40.00, 'Active'),
('Cinnamon roll (mini)', 'Sweet mini cinnamon rolls', 'Flour, Yeast, Cinnamon, Sugar', (SELECT categoryID FROM categories WHERE categoryName = 'Special (Budget-Friendly)'), 35.00, 'Active'),
('Pandesal bites (assorted flavors)', 'Small pandesal bites in assorted flavors', 'Flour, Yeast, Sugar, Various Flavors', (SELECT categoryID FROM categories WHERE categoryName = 'Special (Budget-Friendly)'), 45.00, 'Active');

-- Insert Cookies items
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Chocolate Chip Cookie', 'Classic chocolate chip cookies with rich chocolate chunks', 'Flour, Butter, Sugar, Chocolate Chips, Eggs, Vanilla', (SELECT categoryID FROM categories WHERE categoryName = 'Cookies'), 75.00, 'Active'),
('Black Velvet Chunky Cookie', 'Rich black cocoa cookie with chunky texture', 'Flour, Black Cocoa, Butter, Sugar, Chocolate Chunks, Eggs', (SELECT categoryID FROM categories WHERE categoryName = 'Cookies'), 85.00, 'Active'),
('Double Chocolate White Chunk Cookie', 'Double chocolate cookie with white chocolate chunks', 'Flour, Cocoa, Butter, Sugar, White Chocolate Chunks, Eggs', (SELECT categoryID FROM categories WHERE categoryName = 'Cookies'), 80.00, 'Active'),
('Black Cocoa and White Chocolate Chips', 'Dark black cocoa cookie with white chocolate chips', 'Flour, Black Cocoa, Butter, Sugar, White Chocolate Chips, Eggs', (SELECT categoryID FROM categories WHERE categoryName = 'Cookies'), 80.00, 'Active'),
('Dark Chocolate Mint Cookies', 'Dark chocolate cookies with refreshing mint flavor', 'Flour, Dark Cocoa, Butter, Sugar, Mint Extract, Chocolate Chips, Eggs', (SELECT categoryID FROM categories WHERE categoryName = 'Cookies'), 85.00, 'Active'),
('Assorted Cookies', 'Mix of assorted cookie flavors', 'Flour, Butter, Sugar, Chocolate Chips, Various Flavors, Eggs, Vanilla', (SELECT categoryID FROM categories WHERE categoryName = 'Cookies'), 90.00, 'Active');

-- Insert Crinkles items
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Chocolate Crinkles', 'Rich chocolate crinkles with powdered sugar coating', 'Flour, Cocoa, Butter, Sugar, Powdered Sugar, Eggs, Vanilla', (SELECT categoryID FROM categories WHERE categoryName = 'Crinkles'), 80.00, 'Active'),
('Matcha Crinkles', 'Delicate matcha-flavored crinkles with powdered sugar', 'Flour, Matcha Powder, Butter, Sugar, Powdered Sugar, Eggs', (SELECT categoryID FROM categories WHERE categoryName = 'Crinkles'), 85.00, 'Active'),
('Red Velvet Crinkles', 'Classic red velvet crinkles with cream cheese flavor', 'Flour, Cocoa, Red Food Color, Butter, Sugar, Powdered Sugar, Eggs, Vanilla', (SELECT categoryID FROM categories WHERE categoryName = 'Crinkles'), 85.00, 'Active'),
('Ube Crinkles', 'Filipino ube-flavored crinkles with powdered sugar', 'Flour, Ube Extract, Butter, Sugar, Powdered Sugar, Eggs', (SELECT categoryID FROM categories WHERE categoryName = 'Crinkles'), 85.00, 'Active'),
('Vanilla Crinkles', 'Classic vanilla crinkles with powdered sugar coating', 'Flour, Butter, Sugar, Powdered Sugar, Eggs, Vanilla', (SELECT categoryID FROM categories WHERE categoryName = 'Crinkles'), 80.00, 'Active'),
('Assorted Crinkles', 'Mix of assorted crinkles flavors', 'Flour, Cocoa, Matcha, Ube Extract, Butter, Sugar, Powdered Sugar, Eggs, Vanilla', (SELECT categoryID FROM categories WHERE categoryName = 'Crinkles'), 90.00, 'Active');

-- Insert Brownies items
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Fudge Brownie', 'Rich and fudgy chocolate brownie', 'Flour, Cocoa, Butter, Sugar, Chocolate, Eggs, Vanilla', (SELECT categoryID FROM categories WHERE categoryName = 'Brownies'), 85.00, 'Active'),
('Oreo Fudge Brownie', 'Fudgy brownie with Oreo cookie pieces', 'Flour, Cocoa, Butter, Sugar, Chocolate, Oreo Cookies, Eggs, Vanilla', (SELECT categoryID FROM categories WHERE categoryName = 'Brownies'), 90.00, 'Active'),
('Walnut Fudge Brownie', 'Fudgy brownie with crunchy walnuts', 'Flour, Cocoa, Butter, Sugar, Chocolate, Walnuts, Eggs, Vanilla', (SELECT categoryID FROM categories WHERE categoryName = 'Brownies'), 90.00, 'Active'),
('Assorted Brownies', 'Mix of assorted brownie flavors', 'Flour, Cocoa, Butter, Sugar, Chocolate, Various Toppings, Eggs, Vanilla', (SELECT categoryID FROM categories WHERE categoryName = 'Brownies'), 95.00, 'Active');

-- Insert default pages
INSERT INTO pages (pageTitle, pageDescription, email, mobileNumber, pageType) VALUES
('About Us', 'We are known as the best catering company in Seattle for good reason. Our dedication and commitment to quality and sustainability has earned us a loyal following among our clientele, one that continues to grow based on enthusiastic referrals. For nearly two decades, we have bridged the gap between the land, the sea, and your table. We leverage the best ingredients Washington has to offer, preparing them mindfully and always from scratch.', '', '', 'aboutus'),
('Contact Us', 'Your Business Address Here', 'your-email@example.com', '+63 XXX XXX XXXX', 'contactus');

