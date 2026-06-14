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
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('isbn');
            $table->dropColumn('published_date');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->string('isbn', 13)->nullable();
            $table->date('published_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('isbn');
            $table->dropColumn('published_date');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->string('isbn', 13);
            $table->date('published_date');
        });
    }
};
