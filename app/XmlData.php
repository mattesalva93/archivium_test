<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class XmlData extends Model
{
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $fillable = ['File_Name', 'File_Size', 'File_Has', 'Hash_Type', 'File_Extension', 'DataIns', 'NumeroFattura', 'DataFattura', 'RagSocDest', 'CodFiscDest', 'PIvaDest', 'Importo', 'Imposta', 'EsigIVA', 'FilePath', 'TipoFattura', 'Nazione', 'Anno'];
}
