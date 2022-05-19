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
        Storage::put('uploads', $req['file']);
        return view("xml-data");
    }

    public function unzip(Request $req)
    {
        $archive = $req['file'];
        $zip = new ZipArchive;
        $zip->open($archive);
        if ($zip->open($archive) === TRUE) {
            // Unzip Path
            $zip->extractTo(public_path() . '/fatture/unzipped');
            $zip->close();
            echo 'Unzipped Process Successful!' . '<br>';
        } else {
            echo 'Unzipped Process failed';
        }
        $rootpath = public_path() . '/fatture';
        $fileinfos = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootpath)
        );
        $arrayCaricamenti = [];
        foreach ($fileinfos as $fileinfo) {
            if (!$fileinfo->isFile()) continue;
            $xmlDataString = file_get_contents($fileinfo);
            $xmlObject = simplexml_load_string($xmlDataString);
            $json = json_encode($xmlObject);
            $phpDataArray = json_decode($json, true);
            $dataArray = array();
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
            while (!XmlData::where('File_Hash', $dataArray[0]['File_Hash'])->first()) {
                XmlData::insert($dataArray);
            }
            $xmloriginal = $fileinfo->getPathname();
            $xmlcopy = 'D:\copia_fatture/' . basename($fileinfo);
            $contatore = 1;
            if (file_exists($xmlcopy)) {
                $xmlcopy = 'D:\copia_fatture/' . 'copy-' . $contatore . ' ' . basename($fileinfo);
                $contatore++;
            }
            File::copy($xmloriginal, $xmlcopy);
            array_push($arrayCaricamenti, basename($fileinfo));
        }
        $listaCaricamenti = implode(" ", $arrayCaricamenti);
        return redirect()->route('xml-upload', compact('arrayCaricamenti'))->with('message', 'Caricati correttamente i seguenti file: '. $listaCaricamenti);
    }
}
