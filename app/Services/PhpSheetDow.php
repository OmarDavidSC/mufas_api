<?php 

namespace App\Services;

use Illuminate\Database\Capsule\Manager as DB;
use App\Utilities\FG;
use App\Middlewares\Application;
use App\Dows\StorageDow;
use App\Models\Report;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class PhpSheetDow implements IReadFilter {
    
    private int $startRow = 0;

    private int $endRow = 0;

    /**
     * Set the list of rows that we want to read.
     */
    public function setRows(int $startRow, int $chunkSize): void
    {
        $this->startRow = $startRow;
        $this->endRow = $startRow + $chunkSize;
    }

    public function readCell($columnAddress, $row, $worksheetName = ''): bool
    {
        //  Only read the heading row, and the rows that are configured in $this->_startRow and $this->_endRow
        if (($row == 1) || ($row >= $this->startRow && $row < $this->endRow)) {
            return true;
        }

        return false;
    }

    public function load($inputFileType, $inputFileName,$sheetname,$rango = '', $bloque = null)
    {
        $rsp = FG::responseDefault();
        try {
            //leer excel
            //    $inputFileType = 'Xls';
            //    $inputFileType = 'Xlsx';
            //    $inputFileType = 'Xml';
            //    $inputFileType = 'Ods';
            //    $inputFileType = 'Slk';
            //    $inputFileType = 'Gnumeric';
            //    $inputFileType = 'Csv';
            //$inputFileName = __DIR__."/../../public".$str["data"]->path;
            
            /**  Cree un nuevo lector del tipo definido en $inputFileType  **/
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
           
            
            //Verificar si se leera en bloque
            if ($bloque) {
                 //  Definir cuantas filas queremos leer para cada "fragmento" 
                $chunkSize = (int)$bloque;
                //  Cree una nueva instancia de nuestro filtro de lectura 
                $chunkFilter = new PhpSheetDow();

                //  Decirle al lector que queremos usar el filtro de lectura. 
                $reader->setReadFilter($chunkFilter);
            }
            
            /**  Cargar $inputFileName en un objeto de hoja de cálculo  **/
            $spreadsheet = $reader->load($inputFileName);

            // Obtener la hoja activa del archivo (la que hemos especificado)
            $sheet = $spreadsheet->getSheetByName($sheetname);
            if (!$sheet) {
                throw new \Exception("No se encontró una hoja con el nombre '{$sheetname}' en el archivo Excel.");
            }
            $values = '';
            if ($rango) {
                $validRango = explode(':',$rango);
                switch (count($validRango)) {
                    case 1:
                        $values = $sheet->getCell($rango)->getValue();
                        
                        break;
                    case 2:
                        $values = $sheet->rangeToArray($rango);
                        $values = array_filter($values, function($row) {
                            // Eliminar fila si todas las celdas están vacías
                            return !empty(array_filter($row, function($cell) {
                                return $cell !== null && $cell !== '';
                            }));
                        });
                        break;
                    default:
                        # code...
                        break;
                }
            }
           
            /*
            //  Bucle para leer nuestra hoja de trabajo en bloques de "tamaño de fragmento" para minimizar el uso de la memoria 
            for ($startRow = 2; $startRow <= 1000; $startRow += $chunkSize) {
                //  Dígale al filtro de lectura qué filas queremos en esta iteración  
                $chunkFilter->setRows($startRow,$chunkSize);
                //  Cargue solo las filas que coincidan con nuestro filtro  
                $spreadsheet = $reader->load($inputFileName);
                $sheet = $spreadsheet->getSheetByName($sheetname);
                var_dump($sheet);
            $cellValue = $sheet->getCell('C21')->getValue();
                var_dump($cellValue);
                //   Haga un poco de procesamiento aquí
            }
            */




            $rsp['success'] = true;
            $rsp['data'] = $values;
            $rsp['message'] = 'Registro exitoso';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

}