<?php

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
        Schema::create('credentials', function (Blueprint $table) {
            $table->id();
            $table->integer('merchant_id')->nullable();
            // $table->string('merchant_name')->nullable();
            // $table->string('merchant_email')->nullable();
            // $table->integer('merchant_store_id')->nullable();
            // $table->string('merchant_store_url')->nullable();


            // $table->timestamp('email_verified_at')->nullable();
            // $table->string('password');
            // $table->rememberToken();


            $table->string('iCARRYStoreURL')->nullable();
            $table->string('iCARRYEmail')->nullable();
            $table->string('iCARRYPassword')->nullable();
            $table->string('iCARRYEnableRates')->nullable();

            $table->string('token_type')->nullable();
            $table->string('access_token', 500)->nullable();
            $table->string('refresh_token', 2000)->nullable();
            $table->integer('expires')->nullable();
            $table->string('scope', 2000)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credentials');
    }
};
