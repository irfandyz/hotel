# Model Relationships Documentation

## Overview
This document describes the Eloquent model relationships that have been established in the property management system.

## Models Created

### 1. RestaurantCategory
- **Table**: `restaurant_categories`
- **Fillable**: `name`
- **Relationships**:
  - `menuItems()`: BelongsToMany with RestaurantMenuItem (through pivot table)

### 2. RestaurantMenuItem
- **Table**: `restaurant_menu_items`
- **Fillable**: `property_id`, `name`, `description`, `image`, `price`, `status`
- **Casts**: `price` as decimal:2
- **Relationships**:
  - `property()`: BelongsTo Property
  - `categories()`: BelongsToMany with RestaurantCategory (through pivot table)

### 3. Ownership
- **Table**: `ownerships`
- **Fillable**: `property_id`, `user_id`
- **Relationships**:
  - `property()`: BelongsTo Property
  - `user()`: BelongsTo User

## Updated Models

### Property Model
Added relationships:
- `restaurantMenuItems()`: HasMany RestaurantMenuItem
- `ownerships()`: HasMany Ownership

### User Model
Added relationships:
- `ownerships()`: HasMany Ownership

## Database Structure

### Tables
1. **properties** - Main property information
2. **restaurant_categories** - Food categories (Main Course, Appetizer, etc.)
3. **restaurant_menu_items** - Individual menu items
4. **restaurant_category_menu_items** - Pivot table for many-to-many relationship
5. **ownerships** - Property ownership records
6. **property_images** - Property images
7. **tv_managers** - TV manager records
8. **users** - User accounts

### Relationships
- User → Properties (One-to-Many)
- User → Ownerships (One-to-Many)
- Property → RestaurantMenuItems (One-to-Many)
- Property → Ownerships (One-to-Many)
- Property → PropertyImages (One-to-Many)
- Property → TvManagers (One-to-Many)
- RestaurantMenuItem → RestaurantCategory (Many-to-Many via pivot)

## Usage Examples

### Get all menu items for a property
```php
$property = Property::find(1);
$menuItems = $property->restaurantMenuItems;
```

### Get all categories for a menu item
```php
$menuItem = RestaurantMenuItem::find(1);
$categories = $menuItem->categories;
```

### Get all menu items in a category
```php
$category = RestaurantCategory::find(1);
$menuItems = $category->menuItems;
```

### Get property ownerships
```php
$user = User::find(1);
$ownerships = $user->ownerships;
```

## Seeders
- `RestaurantCategorySeeder`: Populates restaurant_categories with common food categories
- Available categories: Main Course, Appetizer, Dessert, Beverage, Snack, Breakfast, Lunch, Dinner, Salad, Soup, Side Dish, Kids Menu, Vegetarian, Vegan, Seafood, Grill, Pasta, Pizza, Asian, Western, Other
