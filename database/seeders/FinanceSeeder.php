<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\TaxSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        TaxSetting::query()->firstOrCreate(
            ['tax_name' => 'VAT'],
            [
                'vat_rate' => 7.00,
                'service_charge_rate' => 5.00,
                'is_active' => true,
                'notes' => 'Default rates applied on POS checkout and finance reports.',
            ]
        );

        if (Expense::query()->exists()) {
            return;
        }

        $userId = User::query()->value('id');

        $rows = [
            ['title' => 'Monthly kitchen rent', 'category' => 'rent', 'amount' => 85000, 'days' => 2, 'payment_method' => 'bank', 'vendor' => 'Plaza Properties'],
            ['title' => 'Electricity bill', 'category' => 'utilities', 'amount' => 18450, 'days' => 5, 'payment_method' => 'online', 'vendor' => 'DESCO'],
            ['title' => 'Staff wages (week 1)', 'category' => 'salaries', 'amount' => 62000, 'days' => 7, 'payment_method' => 'bank', 'vendor' => 'Payroll'],
            ['title' => 'Gas cylinder refill', 'category' => 'utilities', 'amount' => 4200, 'days' => 3, 'payment_method' => 'cash', 'vendor' => 'Local Supplier'],
            ['title' => 'Facebook ads boost', 'category' => 'marketing', 'amount' => 3500, 'days' => 1, 'payment_method' => 'card', 'vendor' => 'Meta Ads'],
            ['title' => 'Dishwasher repair', 'category' => 'maintenance', 'amount' => 2800, 'days' => 4, 'payment_method' => 'cash', 'vendor' => 'FixIt Tech'],
            ['title' => 'Takeaway boxes', 'category' => 'packaging', 'amount' => 5600, 'days' => 6, 'payment_method' => 'cash', 'vendor' => 'PackPro'],
            ['title' => 'Vegetable market run', 'category' => 'supplies', 'amount' => 9750, 'days' => 0, 'payment_method' => 'cash', 'vendor' => 'Kawran Bazar'],
        ];

        foreach ($rows as $row) {
            Expense::create([
                'title' => $row['title'],
                'category' => $row['category'],
                'amount' => $row['amount'],
                'expense_date' => now()->subDays($row['days'])->toDateString(),
                'payment_method' => $row['payment_method'],
                'vendor' => $row['vendor'],
                'reference' => 'EXP-'.now()->format('ymd').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
                'notes' => null,
                'recorded_by' => $userId,
            ]);
        }
    }
}
