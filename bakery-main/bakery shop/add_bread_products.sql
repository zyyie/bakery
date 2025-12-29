-- Add Bread Categories and Products to DB_bakery

USE DB_bakery;

-- Insert new bread categories
INSERT INTO categories (categoryName) VALUES 
('Classic & Basic Bread'),
('Sweet Bread'),
('Filled / Stuffed Bread'),
('Buns & Rolls'),
('Bread–Cake Combo'),
('Special (Budget-Friendly)');

-- Get category IDs (adjust these based on your database)
SET @classic_bread = (SELECT categoryID FROM categories WHERE categoryName = 'Classic & Basic Bread');
SET @sweet_bread = (SELECT categoryID FROM categories WHERE categoryName = 'Sweet Bread');
SET @filled_bread = (SELECT categoryID FROM categories WHERE categoryName = 'Filled / Stuffed Bread');
SET @buns_rolls = (SELECT categoryID FROM categories WHERE categoryName = 'Buns & Rolls');
SET @bread_cake = (SELECT categoryID FROM categories WHERE categoryName = 'Bread–Cake Combo');
SET @special = (SELECT categoryID FROM categories WHERE categoryName = 'Special (Budget-Friendly)');

-- Classic & Basic Bread
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Pandesal (plain)', 'Classic Filipino bread roll, 3-5 pcs per pack', 'Flour, Yeast, Sugar, Salt', @classic_bread, 50.00, 'Active'),
('Buttered pandesal', 'Soft pandesal with butter spread', 'Flour, Yeast, Sugar, Salt, Butter', @classic_bread, 60.00, 'Active'),
('Malunggay pandesal', 'Nutritious pandesal with malunggay leaves', 'Flour, Yeast, Sugar, Salt, Malunggay', @classic_bread, 55.00, 'Active'),
('Wheat pandesal', 'Healthy whole wheat pandesal', 'Whole Wheat Flour, Yeast, Sugar, Salt', @classic_bread, 60.00, 'Active'),
('Spanish bread', 'Sweet bread roll with butter and sugar filling', 'Flour, Yeast, Sugar, Butter', @classic_bread, 45.00, 'Active'),
('Cheese bread', 'Soft bread with cheese filling', 'Flour, Yeast, Sugar, Cheese', @classic_bread, 55.00, 'Active');

-- Sweet Bread
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Ensaymada (mini)', 'Mini sweet bread topped with butter, sugar, and cheese', 'Flour, Yeast, Sugar, Butter, Cheese', @sweet_bread, 40.00, 'Active'),
('Ube cheese bread', 'Purple yam bread with cheese topping', 'Flour, Yeast, Ube, Cheese', @sweet_bread, 50.00, 'Active'),
('Chocolate bread / Choco roll', 'Sweet bread with chocolate filling', 'Flour, Yeast, Sugar, Chocolate', @sweet_bread, 45.00, 'Active'),
('Cream bread', 'Soft bread with sweet cream filling', 'Flour, Yeast, Sugar, Cream', @sweet_bread, 50.00, 'Active'),
('Custard bread', 'Bread filled with creamy custard', 'Flour, Yeast, Sugar, Custard', @sweet_bread, 50.00, 'Active'),
('Monggo bread', 'Sweet bread with mung bean filling', 'Flour, Yeast, Sugar, Mung Beans', @sweet_bread, 45.00, 'Active'),
('Strawberry bread', 'Bread with strawberry jam filling', 'Flour, Yeast, Sugar, Strawberry Jam', @sweet_bread, 50.00, 'Active'),
('Pineapple bread', 'Bread with pineapple filling', 'Flour, Yeast, Sugar, Pineapple', @sweet_bread, 50.00, 'Active');

-- Filled / Stuffed Bread
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Ham & cheese bread', 'Bread stuffed with ham and cheese', 'Flour, Yeast, Ham, Cheese', @filled_bread, 65.00, 'Active'),
('Hotdog roll', 'Soft roll with hotdog filling', 'Flour, Yeast, Hotdog', @filled_bread, 45.00, 'Active'),
('Sausage roll', 'Bread roll with sausage', 'Flour, Yeast, Sausage', @filled_bread, 50.00, 'Active'),
('Tuna bread', 'Bread filled with tuna spread', 'Flour, Yeast, Tuna, Mayonnaise', @filled_bread, 55.00, 'Active'),
('Chicken bread', 'Bread stuffed with chicken filling', 'Flour, Yeast, Chicken', @filled_bread, 60.00, 'Active'),
('Cheese stick bread', 'Bread with cheese stick filling', 'Flour, Yeast, Cheese', @filled_bread, 50.00, 'Active');

-- Buns & Rolls
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Mini burger bun (with filling)', 'Small burger bun with filling', 'Flour, Yeast, Sugar, Filling', @buns_rolls, 40.00, 'Active'),
('Dinner rolls', 'Soft dinner rolls, 3-4 pcs per pack', 'Flour, Yeast, Sugar, Butter', @buns_rolls, 55.00, 'Active'),
('Soft roll bread', 'Soft and fluffy bread rolls', 'Flour, Yeast, Sugar, Milk', @buns_rolls, 45.00, 'Active');

-- Bread–Cake Combo
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Mini banana bread slice', 'Moist banana bread slice', 'Flour, Banana, Sugar, Eggs', @bread_cake, 35.00, 'Active'),
('Mini chiffon cake slice', 'Light and airy chiffon cake slice', 'Flour, Eggs, Sugar, Oil', @bread_cake, 40.00, 'Active'),
('Mini pound cake', 'Rich and buttery pound cake', 'Flour, Butter, Sugar, Eggs', @bread_cake, 45.00, 'Active'),
('Cupcake', 'Delicious cupcakes, 1-2 pcs per pack', 'Flour, Sugar, Eggs, Frosting', @bread_cake, 50.00, 'Active');

-- Special (Budget-Friendly)
INSERT INTO items (packageName, foodDescription, itemContains, categoryID, price, status) VALUES
('Garlic bread sticks', 'Crispy bread sticks with garlic butter', 'Flour, Yeast, Garlic, Butter', @special, 35.00, 'Active'),
('Cheese garlic roll', 'Soft roll with cheese and garlic', 'Flour, Yeast, Cheese, Garlic', @special, 40.00, 'Active'),
('Cinnamon roll (mini)', 'Sweet mini cinnamon rolls', 'Flour, Yeast, Cinnamon, Sugar', @special, 35.00, 'Active'),
('Pandesal bites (assorted flavors)', 'Small pandesal bites in assorted flavors', 'Flour, Yeast, Sugar, Various Flavors', @special, 45.00, 'Active');

