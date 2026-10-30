<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     
    public function up(): void
    {
        if (!Schema::hasTable('account_ledgers')) {
            Schema::create('account_ledgers', function (Blueprint $table) {
                $table->id();
                $table->string('panel', 100)->default('Solitpay')->index();
                $table->unsignedBigInteger('provider_id')->nullable()->index();
                $table->string('txn_type', 100)->default('Commission');
                $table->enum('type', ['Credit', 'Debit'])->default('Credit')->index();
                $table->decimal('amount', 15, 2)->default(0.00);
                $table->string('mode', 50)->default('NA');
                $table->text('notes')->nullable();
                $table->decimal('opening', 15, 2)->default(0.00);
                $table->decimal('closing', 15, 2)->default(0.00);
                $table->date('date')->index();
                $table->timestamps();
            });
        }
    }

     
    public function down(): void
    {
        Schema::dropIfExists('account_ledgers');
    }
};
