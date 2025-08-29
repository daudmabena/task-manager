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
        Schema::create('tasks_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('function_id')->constrained('functions_requirements')->onDelete('cascade');
            $table->text('correspondence');
            $table->date('actual_start_date');
            $table->date('actual_end_date');
            $table->string('status', 50);
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
        Schema::dropIfExists('tasks_tracking');
    }
};
