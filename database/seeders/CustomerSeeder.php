<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Xóa customers cũ và tạo lại với thông tin địa chỉ Việt Nam
        User::where('role_id', 2)->delete();
        
        // Tạo customer mẫu với thông tin địa chỉ Việt Nam
        $customers = [
            [
                'name' => 'Nguyen Van An',
                'email' => 'customer@gmail.com',
                'password' => Hash::make('password'),
                'role_id' => 2, // Customer role
                'email_verified_at' => now(),
                'is_activate' => 1,
                'phone_number' => '0901234567',
                'country' => 'Vietnam',
                'city' => 'Ha Noi',
                'district' => 'Ba Dinh',
                'ward' => 'Phuong Doi Can',
            ],
            [
                'name' => 'Tran Thi Binh',
                'email' => 'jane.smith@gmail.com', 
                'password' => Hash::make('password'),
                'role_id' => 2, // Customer role
                'email_verified_at' => now(),
                'is_activate' => 1,
                'phone_number' => '0912345678',
                'country' => 'Vietnam',
                'city' => 'Ho Chi Minh',
                'district' => 'District 1',
                'ward' => 'Phuong Ben Nghe',
            ],
            [
                'name' => 'Le Van Cuong',
                'email' => 'mike.johnson@gmail.com',
                'password' => Hash::make('password'),
                'role_id' => 2, // Customer role  
                'email_verified_at' => now(),
                'phone_number' => '0923456789',
                'country' => 'Vietnam',
                'city' => 'Da Nang',
                'district' => 'Hai Chau',
                'ward' => 'Phuong Hai Chau 1',
            ],
            [
                'name' => 'Pham Thi Dung',
                'email' => 'sarah.wilson@gmail.com',
                'password' => Hash::make('password'),
                'role_id' => 2, // Customer role
                'email_verified_at' => now(),
                'is_activate' => 1,
                'phone_number' => '0934567890',
                'country' => 'Vietnam',
                'city' => 'Can Tho',
                'district' => 'Ninh Kieu',
                'ward' => 'Phuong An Hoi',
            ],
            [
                'name' => 'Vo Van Duc',
                'email' => 'david.brown@gmail.com',
                'password' => Hash::make('password'),
                'role_id' => 2, // Customer role
                'email_verified_at' => now(),
                'is_activate' => 1,
                'phone_number' => '0945678901',
                'country' => 'Vietnam',
                'city' => 'Hai Phong',
                'district' => 'Hong Bang',
                'ward' => 'Phuong Dong Khe',
            ]
        ];

        foreach ($customers as $customer) {
            User::create($customer);
        }
    }
}
