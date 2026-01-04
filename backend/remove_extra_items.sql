-- Remove all items that are NOT in the specified bread list
-- This ensures only the bread items you mentioned remain in the database

USE DB_bakery;

-- Delete all items first (optional - uncomment if you want to start fresh)
-- DELETE FROM items;

-- Or delete only items that are NOT in your bread list
-- Keep only these exact items:

-- Classic & Basic Bread
-- Pandesal (plain)
-- Buttered pandesal
-- Malunggay pandesal
-- Wheat pandesal
-- Spanish bread
-- Cheese bread

-- Sweet Bread
-- Ensaymada (mini)
-- Ube cheese bread
-- Chocolate bread / Choco roll
-- Cream bread
-- Custard bread
-- Monggo bread
-- Strawberry bread
-- Pineapple bread

-- Filled / Stuffed Bread
-- Ham & cheese bread
-- Hotdog roll
-- Sausage roll
-- Tuna bread
-- Chicken bread
-- Cheese stick bread

-- Buns & Rolls
-- Mini burger bun (with filling)
-- Dinner rolls
-- Soft roll bread

-- Bread–Cake Combo
-- Mini banana bread slice
-- Mini chiffon cake slice
-- Mini pound cake
-- Cupcake

-- Special (Budget-Friendly)
-- Garlic bread sticks
-- Cheese garlic roll
-- Cinnamon roll (mini)
-- Pandesal bites (assorted flavors)

-- Delete items that don't match the exact names above
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

-- Also delete categories that are NOT bread categories
DELETE FROM categories WHERE categoryName NOT IN (
    'Classic & Basic Bread',
    'Sweet Bread',
    'Filled / Stuffed Bread',
    'Buns & Rolls',
    'Bread–Cake Combo',
    'Special (Budget-Friendly)'
);

