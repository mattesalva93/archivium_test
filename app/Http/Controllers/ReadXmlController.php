<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\XmlData;
use Carbon\Carbon;
use File;
use DateTime;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class ReadXmlController extends Controller
{
    public function index(Request $req)
    {
        //Caricamento del file tramite la funzionalità storage di laravel
        Storage::put('uploads', $req['file']);
        return view("xml-data");
    }

    public function unzip(Request $req)
    {
        //Utilizzo della funzionalità di PHP per poter aprire i file .zip e estrarne i contenuti nella cartella scelta
        $archive = $req['file'];
        $zip = new ZipArchive;
        $zip->open($archive);
        if ($zip->open($archive) === TRUE) {
            $zip->extractTo(public_path() . '/fatture/unzipped');
            $zip->close();
        }
        //Funzionalità di PHP per poter controllare la presenza di directory tra altri file e lavorare sui file in esse contenuti
        $rootpath = public_path() . '/fatture';
        $fileinfos = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootpath)
        );
        //Variabile di appoggio utile per tenere traccia di tutti i file che vengono correttamente caricati
        $arrayCaricamenti = [];
        //Ciclo foreach che prende ogni singolo file precedentemente rilevato con la funzionalità Recursive di PHP e ad rende ognuno di essi prima un file Json e successivamente un array multidimensionale con i valori contenuti nei file .xml
        foreach ($fileinfos as $fileinfo) {
            if (!$fileinfo->isFile()) continue;
            $xmlDataString = file_get_contents($fileinfo);
            $xmlObject = simplexml_load_string($xmlDataString);
            $json = json_encode($xmlObject);
            $phpDataArray = json_decode($json, true);
            $dataArray = array();
            //Assegnazione di tutte le colonne da riempire nel database
            $dataArray[] = [
                'File_Name' => basename($fileinfo),
                'File_Size' => filesize($fileinfo->getPathname()),
                'File_Hash' => Hash::make($fileinfo),
                'Hash_type' => 'BCrypt',
                'File_Extension' => pathinfo(basename($fileinfo), PATHINFO_EXTENSION),
                'DataIns' => Carbon::now()->format('Y-m-d H:i:s') ?? null,
                'NumeroFattura' => $phpDataArray['FatturaElettronicaBody']['DatiGenerali']['DatiGeneraliDocumento']['Numero'] ?? null,
                'DataFattura' => $phpDataArray['FatturaElettronicaBody']['DatiGenerali']['DatiGeneraliDocumento']['Data'] ?? null,
                'RagSocDest' => (is_string($phpDataArray['FatturaElettronicaHeader']['CessionarioCommittente']['DatiAnagrafici']['Anagrafica']['Denominazione'] ?? null)) ? $phpDataArray['FatturaElettronicaHeader']['CessionarioCommittente']['DatiAnagrafici']['Anagrafica']['Denominazione'] : null,
                'CodFiscDest' => $phpDataArray['FatturaElettronicaHeader']['CessionarioCommittente']['DatiAnagrafici']['IdFiscaleIVA']['IdCodice'] ?? null,
                'PIvaDest' => $phpDataArray['FatturaElettronicaHeader']['DatiTrasmissione']['CodiceDestinatario'] ?? null,
                'Importo' => $phpDataArray['FatturaElettronicaBody']['DatiPagamento']['DettaglioPagamento']['ImportoPagamento'] ?? null,
                'Imposta' => $phpDataArray['FatturaElettronicaBody']['DatiBeniServizi']['DatiRiepilogo']['Imposta'] ?? null,
                'EsigIVA' => $phpDataArray['FatturaElettronicaBody']['DatiBeniServizi']['DatiRiepilogo']['EsigibilitaIVA'] ?? null,
                'FilePath' => 'D:\copia_fatture',
                'TipoFattura' => $phpDataArray['FatturaElettronicaBody']['DatiGenerali']['DatiGeneraliDocumento']['TipoDocumento'] ?? null,
                'Nazione' => $phpDataArray['FatturaElettronicaHeader']['CessionarioCommittente']['Sede']['Nazione'] ?? null,
                'Anno' => isset($phpDataArray['FatturaElettronicaBody']['DatiGenerali']['DatiGeneraliDocumento']['Data']) ? DateTime::createFromFormat('Y-m-d', $phpDataArray['FatturaElettronicaBody']['DatiGenerali']['DatiGeneraliDocumento']['Data'])->format('Y') : null,
            ];
            //Controllo tramite query per verificare la presenza di un hash nel database: nel caso il file hash esista il file NON viene caricato, in quanto i file corrisponderebbero al 100% e sarebbe quindi un caricamento doppio;
            while (!XmlData::where('File_Hash', $dataArray[0]['File_Hash'])->first()) {
                XmlData::insert($dataArray);
            }
            //Creazione delle due variabili al fine di essere utilizzate nella funzione copy di PHP per copiare i file correttamente caricati in una directory locale
            $xmloriginal = $fileinfo->getPathname();
            $xmlcopy = 'D:\copia_fatture/' . basename($fileinfo);
            $contatore = 1;
            //Condizione che verifica la presenza di file con lo stesso nome. Se presente lo stesso nome viene creato una sorta di slug per evitare che le copie si sovrascrivano
            if (file_exists($xmlcopy)) {
                $xmlcopy = 'D:\copia_fatture/' . 'copy-' . $contatore . ' ' . basename($fileinfo);
                $contatore++;
            }
            File::copy($xmloriginal, $xmlcopy);
            //Caricamento nell'array precedentemente creato dei file correttamente caricati
            array_push($arrayCaricamenti, basename($fileinfo));
        }
        //Creazione di due variabili di appoggio: la prima per fornire elenco nel messaggio di successo con i file caricati, la seconda per segnalare in quale percorso locale risalire ai file .xml copiati
        $listaCaricamenti = implode(" ", $arrayCaricamenti);
        $percorsoCopie = 'D:\copia_fatture';
        return redirect()->route('xml-upload', compact('arrayCaricamenti'))->with('message', 'Caricati correttamente i seguenti file: '. $listaCaricamenti . '. ' . 'File correttamente copiati al percorso: '. $percorsoCopie);
    }
}
