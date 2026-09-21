<?php

namespace Database\Seeders;

use App\Models\PaymentAccount;
use App\Models\PaymentTransaction;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $jazzCash = PaymentAccount::create([
            'name' => 'JazzCash',
            'type' => 'jazzcash',
            'account_number' => '0300-1234567',
            'holder_name' => 'POS Store',
            'opening_balance' => 150000,
            'balance' => 140000,
        ]);

        $easypaisa = PaymentAccount::create([
            'name' => 'Easypaisa',
            'type' => 'easypaisa',
            'account_number' => '0345-7654321',
            'holder_name' => 'POS Store',
            'opening_balance' => 100000,
            'balance' => 95000,
        ]);

        $bank = PaymentAccount::create([
            'name' => 'Bank Account',
            'type' => 'bank',
            'account_number' => 'PK00 POS 0000 1234',
            'holder_name' => 'POS Store',
            'opening_balance' => 250000,
            'balance' => 225000,
        ]);

        PaymentTransaction::create([
            'payment_account_id' => $jazzCash->id,
            'type' => 'send',
            'customer_name' => 'Ali Raza',
            'customer_phone' => '0301-1112233',
            'recipient_name' => 'Ahmed Raza',
            'amount' => 10000,
            'commission' => 100,
            'transaction_date' => now()->setTime(10, 15),
            'description' => 'Money transfer to Ahmed Raza',
        ]);

        PaymentTransaction::create([
            'payment_account_id' => $easypaisa->id,
            'type' => 'withdrawal',
            'customer_name' => 'Sana Khan',
            'customer_phone' => '0322-2223344',
            'amount' => 5000,
            'commission' => 50,
            'transaction_date' => now()->setTime(11, 40),
            'description' => 'Cash withdrawal service',
        ]);

        PaymentTransaction::create([
            'payment_account_id' => $bank->id,
            'type' => 'send',
            'customer_name' => 'Usman Tariq',
            'customer_phone' => '0333-4445566',
            'recipient_name' => 'Nadia Tariq',
            'amount' => 25000,
            'commission' => 250,
            'transaction_date' => now()->setTime(13, 20),
            'description' => 'Bank transfer to Nadia Tariq',
        ]);
    }
}
