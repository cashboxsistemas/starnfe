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
        Schema::table('remessa_nves', function (Blueprint $table) {
            $table->string('tipo_forma_pagamento', 20)->default('a_vista')->after('forma_pagamento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remessa_nves', function (Blueprint $table) {
            $table->dropColumn('tipo_forma_pagamento');
        });
    }
};
