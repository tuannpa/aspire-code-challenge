<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Customer;

class CreateLoansTable extends Migration
{
    private string $table = 'loans';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            Schema::create($this->table, function (Blueprint $table) {
                $table->id();
                $table->foreignIdFor(Customer::class);
                $table->text('description')->nullable();
                $table->decimal('interest_rate', 5);
                $table->string('status');
                $table->string('duration');
                $table->integer('amount');
                $table->string('approved_by')->nullable();
                $table->unsignedInteger('product_id');

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->table);
    }
}
