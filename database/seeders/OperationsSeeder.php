<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use Illuminate\Database\Seeder;

class OperationsSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['seated', 'ordered', 'preparing', 'available', 'ready', 'seated', 'waiting', 'available', 'ordered', 'seated', 'available', 'preparing', 'ready', 'available', 'seated', 'ordered', 'available', 'preparing', 'seated', 'available'];
        foreach (range(1, 20) as $i) {
            RestaurantTable::query()->updateOrCreate(
                ['code' => str_pad((string) $i, 2, '0', STR_PAD_LEFT)],
                [
                    'capacity' => $i % 5 === 0 ? 8 : ($i % 2 === 0 ? 4 : 2),
                    'zone' => $i <= 10 ? 'Main Hall' : 'Patio',
                    'status' => $statuses[$i - 1],
                ]
            );
        }

        $menu = [
            // Homepage Popular Dishes (featured) — keep look/content of the marketing site
            ['name' => 'Creamy Alfredo Pasta', 'category' => 'Pasta', 'price' => 1299, 'badge' => 'Best Seller', 'is_favorite' => true, 'is_bestseller' => true, 'is_vegetarian' => true, 'rating' => 4.9, 'review_count' => 120, 'description' => 'Creamy alfredo tossed with fettuccine and finished with parmesan.', 'image_url' => 'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Grilled Chicken Steak', 'category' => 'Mains', 'price' => 1599, 'badge' => 'Hot', 'is_favorite' => true, 'is_bestseller' => true, 'is_vegetarian' => false, 'rating' => 4.9, 'review_count' => 98, 'description' => 'Flame-grilled chicken steak with house seasoning and sides.', 'image_url' => 'https://images.unsplash.com/photo-1532550907401-a500c9a57435?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Bynnas Special Burger', 'category' => 'Burger', 'price' => 1199, 'badge' => 'Popular', 'is_favorite' => true, 'is_bestseller' => true, 'is_vegetarian' => false, 'rating' => 4.7, 'review_count' => 156, 'description' => 'Signature smash burger with cheddar, pickles, and secret sauce.', 'image_url' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Margherita Pizza', 'category' => 'Pizza', 'price' => 1399, 'badge' => 'Chef Pick', 'is_favorite' => true, 'is_bestseller' => true, 'is_vegetarian' => true, 'rating' => 4.6, 'review_count' => 84, 'description' => 'Wood-fired pizza with tomato, mozzarella, and fresh basil.', 'image_url' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Grilled Salmon', 'category' => 'Mains', 'price' => 1450, 'badge' => 'Popular', 'is_favorite' => false, 'is_bestseller' => true, 'is_vegetarian' => false, 'description' => 'Atlantic salmon with lemon butter and seasonal greens.', 'image_url' => 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=400&q=80'],
            ['name' => 'Truffle Pasta', 'category' => 'Mains', 'price' => 980, 'badge' => 'Bestseller', 'is_favorite' => false, 'is_bestseller' => true, 'is_vegetarian' => true, 'description' => 'Fettuccine tossed in silky truffle cream with cracked pepper.', 'image_url' => 'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?auto=format&fit=crop&w=400&q=80'],
            ['name' => 'Beef Steak', 'category' => 'Mains', 'price' => 1650, 'badge' => null, 'is_favorite' => false, 'is_bestseller' => true, 'is_vegetarian' => false, 'description' => 'Char-grilled ribeye with garlic butter and roasted vegetables.', 'image_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=400&q=80'],
            ['name' => 'Mushroom Risotto', 'category' => 'Mains', 'price' => 920, 'badge' => 'Popular', 'is_favorite' => false, 'is_bestseller' => false, 'is_vegetarian' => true, 'description' => 'Creamy arborio rice with wild mushrooms and parmesan.', 'image_url' => 'https://images.unsplash.com/photo-1476124369491-e9554937164c?auto=format&fit=crop&w=400&q=80'],
            ['name' => 'Caesar Salad', 'category' => 'Appetizers', 'price' => 450, 'badge' => 'New', 'is_favorite' => false, 'is_bestseller' => false, 'is_vegetarian' => true, 'description' => 'Crisp romaine, parmesan, croutons, and classic dressing.', 'image_url' => 'https://images.unsplash.com/photo-1546793665-c74683f339c1?auto=format&fit=crop&w=400&q=80'],
            ['name' => 'Spicy Wings', 'category' => 'Appetizers', 'price' => 620, 'badge' => 'Spicy', 'is_favorite' => false, 'is_bestseller' => false, 'is_vegetarian' => false, 'description' => 'Crispy wings tossed in house buffalo sauce with ranch dip.', 'image_url' => 'https://images.unsplash.com/photo-1527477396000-e27163b481c2?auto=format&fit=crop&w=400&q=80'],
            ['name' => 'Veggie Spring Rolls', 'category' => 'Appetizers', 'price' => 380, 'badge' => null, 'is_favorite' => false, 'is_bestseller' => false, 'is_vegetarian' => true, 'description' => 'Golden rolls with mixed vegetables and sweet chili dip.', 'image_url' => 'https://images.unsplash.com/photo-1529006557810-274b0b2c86c3?auto=format&fit=crop&w=400&q=80'],
            ['name' => 'Fresh Lemonade', 'category' => 'Beverages', 'price' => 180, 'badge' => null, 'is_favorite' => false, 'is_bestseller' => false, 'is_vegetarian' => true, 'description' => 'Freshly squeezed lemons with mint and sparkling water.', 'image_url' => 'https://images.unsplash.com/photo-1621263764928-df1444c5e859?auto=format&fit=crop&w=400&q=80'],
            ['name' => 'Espresso', 'category' => 'Beverages', 'price' => 150, 'badge' => null, 'is_favorite' => false, 'is_bestseller' => false, 'is_vegetarian' => true, 'description' => 'Double shot of rich, aromatic espresso.', 'image_url' => 'https://images.unsplash.com/photo-1510590337119-c5b8c7b1d0c4?auto=format&fit=crop&w=400&q=80'],
            ['name' => 'Iced Latte', 'category' => 'Beverages', 'price' => 220, 'badge' => 'New', 'is_favorite' => false, 'is_bestseller' => false, 'is_vegetarian' => true, 'description' => 'Chilled espresso with milk over ice.', 'image_url' => 'https://images.unsplash.com/photo-1517701556297-2f5132a8b9d5?auto=format&fit=crop&w=400&q=80'],
            ['name' => 'Chocolate Lava', 'category' => 'Desserts', 'price' => 400, 'badge' => null, 'is_favorite' => false, 'is_bestseller' => false, 'is_vegetarian' => true, 'description' => 'Warm chocolate cake with a molten center and vanilla ice cream.', 'image_url' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=400&q=80'],
            ['name' => 'Tiramisu', 'category' => 'Desserts', 'price' => 420, 'badge' => 'Popular', 'is_favorite' => false, 'is_bestseller' => false, 'is_vegetarian' => true, 'description' => 'Classic Italian layers of espresso-soaked ladyfingers and mascarpone.', 'image_url' => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?auto=format&fit=crop&w=400&q=80'],
            ['name' => 'BBQ Chicken Pizza', 'category' => 'Pizza', 'price' => 1050, 'badge' => 'Spicy', 'is_favorite' => false, 'is_bestseller' => false, 'is_vegetarian' => false, 'description' => 'Smoky BBQ chicken with red onion and jalapeños.', 'image_url' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&w=400&q=80'],
            ['name' => 'Classic Burger', 'category' => 'Burger', 'price' => 750, 'badge' => 'Bestseller', 'is_favorite' => false, 'is_bestseller' => true, 'is_vegetarian' => false, 'description' => 'House smash burger with cheddar, pickles, and secret sauce.', 'image_url' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=400&q=80'],
            ['name' => 'Family Combo', 'category' => 'Combo', 'price' => 2490, 'badge' => 'New', 'is_favorite' => false, 'is_bestseller' => false, 'is_vegetarian' => false, 'description' => 'Pizza, wings, fries, and drinks — perfect for sharing.', 'image_url' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=400&q=80'],
        ];
        $menuMap = [];
        foreach ($menu as $i => $row) {
            $item = MenuItem::query()->updateOrCreate(
                ['name' => $row['name']],
                array_merge($row, ['is_available' => true, 'sort_order' => $i + 1])
            );
            $menuMap[$row['name']] = $item;
        }

        // Ensure only the homepage Popular Dishes stay featured
        MenuItem::query()->whereNotIn('name', [
            'Creamy Alfredo Pasta',
            'Grilled Chicken Steak',
            'Bynnas Special Burger',
            'Margherita Pizza',
        ])->update(['is_favorite' => false]);


        $customers = [
            ['name' => 'Sarah Ahmed', 'email' => 'sarah@example.com', 'phone' => '+8801711111111', 'membership_tier' => 'gold', 'loyalty_points' => 1240, 'lifetime_spend' => 28600],
            ['name' => 'James Carter', 'email' => 'james@example.com', 'phone' => '+8801711111112', 'membership_tier' => 'silver', 'loyalty_points' => 420, 'lifetime_spend' => 9800],
            ['name' => 'Nadia Khan', 'email' => 'nadia@example.com', 'phone' => '+8801711111113', 'membership_tier' => 'platinum', 'loyalty_points' => 2880, 'lifetime_spend' => 51200],
            ['name' => 'Michael Chen', 'email' => 'michael@example.com', 'phone' => '+8801711111114', 'membership_tier' => 'standard', 'loyalty_points' => 90, 'lifetime_spend' => 2400],
            ['name' => 'Emily Brooks', 'email' => 'emily@example.com', 'phone' => '+8801711111115', 'membership_tier' => 'gold', 'loyalty_points' => 1560, 'lifetime_spend' => 33400],
        ];
        $customerMap = [];
        foreach ($customers as $row) {
            $customerMap[$row['name']] = Customer::query()->updateOrCreate(
                ['email' => $row['email']],
                array_merge($row, ['joined_on' => now()->subMonths(rand(1, 12)), 'status' => 'active'])
            );
        }

        $table06 = RestaurantTable::where('code', '06')->first();
        $table12 = RestaurantTable::where('code', '12')->first();
        $table03 = RestaurantTable::where('code', '03')->first();

        $orders = [
            ['order_number' => 'ORD-1024', 'type' => 'dinein', 'status' => 'preparing', 'table_id' => $table06?->id, 'meta' => 'Table 06', 'minutes' => 2, 'items' => [['Grilled Salmon', 1], ['Espresso', 2]]],
            ['order_number' => 'ORD-1025', 'type' => 'delivery', 'status' => 'ready', 'meta' => 'Zone A', 'minutes' => 5, 'customer' => 'Nadia Khan', 'items' => [['Truffle Pasta', 2]]],
            ['order_number' => 'ORD-1026', 'type' => 'takeaway', 'status' => 'preparing', 'meta' => 'Counter', 'minutes' => 8, 'items' => [['Caesar Salad', 1], ['Fresh Lemonade', 1]]],
            ['order_number' => 'ORD-1027', 'type' => 'delivery', 'status' => 'on_the_way', 'meta' => 'Zone B', 'minutes' => 11, 'customer' => 'James Carter', 'items' => [['Beef Steak', 1]]],
            ['order_number' => 'ORD-1028', 'type' => 'dinein', 'status' => 'ready', 'table_id' => $table12?->id, 'meta' => 'Table 12', 'minutes' => 14, 'items' => [['Chocolate Lava', 2]]],
            ['order_number' => 'ORD-1029', 'type' => 'qr', 'status' => 'preparing', 'table_id' => $table03?->id, 'meta' => 'Table 03', 'minutes' => 17, 'items' => [['Truffle Pasta', 1], ['Fresh Lemonade', 2]]],
        ];

        foreach ($orders as $row) {
            $subtotal = 0;
            $lines = [];
            foreach ($row['items'] as [$name, $qty]) {
                $menuItem = $menuMap[$name];
                $line = $qty * (float) $menuItem->price;
                $subtotal += $line;
                $lines[] = [$menuItem, $qty, $line];
            }

            $order = Order::query()->updateOrCreate(
                ['order_number' => $row['order_number']],
                [
                    'type' => $row['type'],
                    'status' => $row['status'],
                    'table_id' => $row['table_id'] ?? null,
                    'customer_id' => isset($row['customer']) ? $customerMap[$row['customer']]->id : null,
                    'customer_name' => $row['customer'] ?? null,
                    'meta' => $row['meta'],
                    'subtotal' => $subtotal,
                    'total' => $subtotal,
                    'payment_status' => in_array($row['status'], ['ready', 'on_the_way'], true) ? 'paid' : 'unpaid',
                    'placed_at' => now()->subMinutes($row['minutes']),
                ]
            );

            OrderItem::query()->where('order_id', $order->id)->delete();
            foreach ($lines as [$menuItem, $qty, $line]) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'item_name' => $menuItem->name,
                    'quantity' => $qty,
                    'unit_price' => $menuItem->price,
                    'line_total' => $line,
                ]);
            }
        }

        $reservations = [
            ['guest_name' => 'Sarah Ahmed', 'phone' => '+8801711111111', 'guests' => 4, 'hour' => 11, 'status' => 'confirmed', 'table' => '04'],
            ['guest_name' => 'James Carter', 'phone' => '+8801711111112', 'guests' => 2, 'hour' => 13, 'status' => 'pending', 'table' => '08'],
            ['guest_name' => 'Nadia Khan', 'phone' => '+8801711111113', 'guests' => 6, 'hour' => 14, 'minute' => 30, 'status' => 'confirmed', 'table' => '10'],
            ['guest_name' => 'Michael Chen', 'phone' => '+8801711111114', 'guests' => 3, 'hour' => 19, 'status' => 'confirmed', 'table' => '15'],
            ['guest_name' => 'Emily Brooks', 'phone' => '+8801711111115', 'guests' => 5, 'hour' => 20, 'minute' => 15, 'status' => 'pending', 'table' => '18'],
        ];

        foreach ($reservations as $row) {
            Reservation::query()->updateOrCreate(
                [
                    'guest_name' => $row['guest_name'],
                    'reserved_at' => today()->setTime($row['hour'], $row['minute'] ?? 0),
                ],
                [
                    'customer_id' => $customerMap[$row['guest_name']]->id ?? null,
                    'phone' => $row['phone'],
                    'guests' => $row['guests'],
                    'table_id' => RestaurantTable::where('code', $row['table'])->value('id'),
                    'status' => $row['status'],
                ]
            );
        }

        LoyaltyTransaction::query()->updateOrCreate(
            ['customer_id' => $customerMap['Nadia Khan']->id, 'description' => 'Welcome bonus'],
            ['type' => 'earn', 'points' => 200]
        );
        LoyaltyTransaction::query()->updateOrCreate(
            ['customer_id' => $customerMap['Sarah Ahmed']->id, 'description' => 'Dessert redemption'],
            ['type' => 'redeem', 'points' => -150]
        );

        // Sample held order for POS resume demo
        $heldSubtotal = (float) $menuMap['Grilled Salmon']->price + (float) $menuMap['Truffle Pasta']->price * 2;
        $heldOrder = Order::query()->updateOrCreate(
            ['order_number' => 'ORD-'.now()->format('ymd').'-HELD1'],
            [
                'type' => 'dinein',
                'status' => 'pending',
                'table_id' => RestaurantTable::where('code', '05')->value('id'),
                'customer_name' => 'Walk-in Customer',
                'guest_count' => 4,
                'meta' => 'Table 05',
                'subtotal' => $heldSubtotal,
                'service_charge' => round($heldSubtotal * 0.05, 2),
                'tax_amount' => round($heldSubtotal * 0.07, 2),
                'total' => round($heldSubtotal * 1.12, 2),
                'payment_status' => 'unpaid',
                'is_held' => true,
                'placed_at' => now()->subMinutes(12),
            ]
        );
        OrderItem::query()->where('order_id', $heldOrder->id)->delete();
        foreach ([['Grilled Salmon', 1, 'No Butter'], ['Truffle Pasta', 2, 'Extra Cheese']] as [$name, $qty, $note]) {
            $menuItem = $menuMap[$name];
            OrderItem::create([
                'order_id' => $heldOrder->id,
                'menu_item_id' => $menuItem->id,
                'item_name' => $menuItem->name.' ('.$note.')',
                'quantity' => $qty,
                'unit_price' => $menuItem->price,
                'line_total' => $qty * (float) $menuItem->price,
            ]);
        }
    }
}
