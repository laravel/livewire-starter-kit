<?php
// database/migrations/xxxx_create_contacts_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->string('value_type', 20)->unique()->primary();
            $table->string('value');
        });

        DB::table('contacts')->insert(collect([
            'name' => 'Suman Shrestha',
            'profile_pic' => 'https://avatars.githubusercontent.com/u/8534680?s=400&u=a2bd1c8f72fd01b296d953c3cf7cb12b8f8685b7&v=4',
            'email' => 'summonshr@gmail.com',
            'phone' => '9841145614',
            'address' => 'Dhapakhel Lalitpur',
            'linkedin' => 'https://www.linkedin.com/in/suman-shresth/',
            'github' => 'https://github.com/summonshr',
            'x' => 'https://x.com/sumfreelancer',
            'website' => 'https://sumanshresth.com.np',
        ])->map(function ($value, $key) {
            return [
                'value_type' => $key,
                'value' => $value,
            ];
        })->toArray());
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
