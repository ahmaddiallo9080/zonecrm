<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['particulier', 'entreprise'])->default('particulier');
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone2')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('location')->nullable()->comment('Repère / coordonnées GPS du site');
            $table->string('status', 30)->default('prospect')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('client_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30)->index(); // mikrotik, starlink, wifi, routeur, autre
            $table->string('label');
            $table->string('host')->nullable()->comment('IP / domaine / URL');
            $table->unsignedInteger('port')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable()->comment('Chiffré (APP_KEY)');
            $table->string('serial_number')->nullable()->comment('N° kit Starlink, N° série routeur...');
            $table->string('plan')->nullable()->comment('Abonnement Starlink, version RouterOS...');
            $table->date('renewal_date')->nullable();
            $table->text('extra')->nullable()->comment('Notes sensibles chiffrées');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_accesses');
        Schema::dropIfExists('clients');
    }
};
