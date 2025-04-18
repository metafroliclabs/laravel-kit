<?php

use App\Helpers\Constant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('role', [Constant::ADMIN, Constant::USER])->default(Constant::USER);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('country_code')->nullable();
            $table->string('dial_code')->nullable();
            $table->string('phone')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar')->default(Constant::DEFAULT_AVATAR);
            // $table->string('device_id')->nullable();
            // $table->string('device_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('status')->default(Constant::PENDING);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
