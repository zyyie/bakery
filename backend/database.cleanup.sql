-- Cleanup script for DB_bakery
-- Destructive operations: removes non-catalog items and old/default categories/items
-- Run only when you want to enforce the curated catalog.

USE DB_bakery;

-- Remove old/default products if present
DELETE FROM items WHERE packageName IN (
    'Chocolate Truffle Delicious Cake',
    'Pound Cake',
    'Baked Flourless Cake',
    'MYSORE PAK'
);

-- Remove old/default categories if present
DELETE FROM categories WHERE categoryName IN (
    'Choclate cake',
    'Base Cake',
    'Flourless Cake',
    'sweet'
);

-- Keep only the curated bread items listed in database.sql seed section
DELETE FROM items WHERE packageName NOT IN (
    'Pandesal (plain)',
    'Buttered pandesal',
    'Malunggay pandesal',
    'Wheat pandesal',
    'Spanish bread',
    'Cheese bread',
    'Ensaymada (mini)',
    'Ube cheese bread',
    'Chocolate bread / Choco roll',
    'Cream bread',
    'Custard bread',
    'Monggo bread',
    'Strawberry bread',
    'Pineapple bread',
    'Ham & cheese bread',
    'Hotdog roll',
    'Sausage roll',
    'Tuna bread',
    'Chicken bread',
    'Cheese stick bread',
    'Mini burger bun (with filling)',
    'Dinner rolls',
    'Soft roll bread',
    'Mini banana bread slice',
    'Mini chiffon cake slice',
    'Mini pound cake',
    'Cupcake',
    'Garlic bread sticks',
    'Cheese garlic roll',
    'Cinnamon roll (mini)',
    'Pandesal bites (assorted flavors)'
);

-- Keep only the curated bread categories
DELETE FROM categories WHERE categoryName NOT IN (
    'Classic & Basic Bread',
    'Sweet Bread',
    'Filled / Stuffed Bread',
    'Buns & Rolls',
    'Bread–Cake Combo',
    'Special (Budget-Friendly)'
);
