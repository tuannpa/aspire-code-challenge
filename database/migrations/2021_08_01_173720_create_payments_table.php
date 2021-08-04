<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Customer;
use App\Models\Loan;

class CreatePaymentsTable extends Migration
{
    private string $table = 'payments';

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
                $table->foreignIdFor(Loan::class)->nullable();
                $table->string('state');
                $table->dateTime('due_date');
                $table->dateTime('repaid_date');
                $table->integer('paid_amount');
                $table->integer('remaining_amount');

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
