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
        Schema::create('informations', function (Blueprint $table) {
            $table->uuid('id_information')->primary();
            $table->text('db_host')->nullable();
            $table->text('db_port')->nullable();
            $table->text('db_database');
            $table->text('db_username')->nullable();
            $table->text('db_password')->nullable();
            $table->text('token_communication');
            $table->text('cle_authentification');
            $table->text('cle_cryptage');
            $table->text('nom_dossier_storage');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informations');
    }
};
