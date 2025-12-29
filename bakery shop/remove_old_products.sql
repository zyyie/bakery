-- Remove old/default products and categories
-- Run this to delete the old items before adding the new bread products

USE DB_bakery;

-- Delete old/default items
DELETE FROM items WHERE packageName IN (
    'Chocolate Truffle Delicious Cake',
    'Pound Cake',
    'Baked Flourless Cake',
    'MYSORE PAK'
);

-- Delete old/default categories
DELETE FROM categories WHERE categoryName IN (
    'Choclate cake',
    'Base Cake',
    'Flourless Cake',
    'sweet'
);

-- Note: Make sure to run the bread products INSERT statements after running this
-- Or import the updated database.sql file which already has the bread products

