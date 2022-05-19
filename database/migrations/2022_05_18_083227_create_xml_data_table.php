<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateXmlDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Creazione di tutte le tabelle del database con le informazioni delle fatture
        Schema::create('xml_data', function (Blueprint $table) {
            $table->id();
            $table->string('File_Name', 192)->collation('utf8mb4_unicode_ci')->nullable();
            $table->integer('File_Size')->nullable();
            $table->string('File_Hash', 384)->collation('utf8mb4_unicode_ci')->nullable();
            $table->string('Hash_Type', 30)->collation('utf8mb4_unicode_ci')->nullable();
            $table->string('File_Extension', 30)->collation('utf8mb4_unicode_ci')->nullable();
            $table->timestamp('DataIns')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->string('NumeroFattura', 60)->collation('utf8mb4_unicode_ci')->nullable();
            $table->date('DataFattura')->nullable();
            $table->string('RagSocDest', 300)->collation('utf8mb4_unicode_ci')->nullable();
            $table->string('CodFiscDest', 48)->collation('utf8mb4_unicode_ci')->nullable();
            $table->string('PIvaDest', 84)->collation('utf8mb4_unicode_ci')->nullable();
            $table->decimal('Importo', 13, 0)->nullable();
            $table->decimal('Imposta', 13, 0)->nullable();
            $table->string('EsigIVA', 15)->collation('utf8mb4_unicode_ci')->nullable();
            $table->binary('FilePath')->nullable();
            $table->string('TipoFattura', 30)->collation('utf8mb4_unicode_ci')->nullable();
            $table->string('Nazione', 15)->collation('utf8mb4_unicode_ci')->nullable();
            $table->integer('Anno')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xml_data');
    }
}
