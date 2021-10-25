<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\User;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name')->unique();
            $table->text('event_description');
            //:XXX - evaludate if decimal is a better option
            //$table->decimal('latitude', $precision = 10, $scale = 8);
            //$table->decimal('longitude', $precision = 11, $scale = 8);
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->string('location_name')->nullable();
            $table->foreignIdFor(User::class, 'created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
