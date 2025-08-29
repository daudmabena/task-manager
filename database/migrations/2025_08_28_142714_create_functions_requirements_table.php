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
        Schema::create('functions_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained('processes')->onDelete('cascade');
            $table->string('name', 255);
            $table->text('requirement');
            $table->date('planned_start_date');
            $table->date('planned_end_date');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('functions_requirements');
    }
};
