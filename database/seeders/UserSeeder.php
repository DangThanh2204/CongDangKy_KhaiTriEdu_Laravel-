<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo admin user
        User::create([
            'username' => 'admin',
            'fullname' => 'Admin User',
            'email' => 'admin@khaitriedu.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_verified' => true,
        ]);

        // Tạo instructor user
        User::create([
            'username' => 'instructor',
            'fullname' => 'Giảng viên Nguyễn Văn A',
            'email' => 'instructor@khaitriedu.com',
            'password' => Hash::make('password'),
            'role' => 'instructor',
            'is_verified' => true,
        ]);

        // Tạo student users
        User::create([
            'username' => 'student1',
            'fullname' => 'Học viên Trần Thị B',
            'email' => 'student1@khaitriedu.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'is_verified' => true,
        ]);

        User::create([
            'username' => 'student2',
            'fullname' => 'Học viên Lê Văn C',
            'email' => 'student2@khaitriedu.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'is_verified' => true,
        ]);

        User::create([
            'username' => 'student3',
            'fullname' => 'Học viên Phạm Thị D',
            'email' => 'student3@khaitriedu.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'is_verified' => true,
        ]);

        $this->command->info('Đã tạo thành công 5 user mẫu: 1 admin, 1 instructor, 3 students!');
    }
}
