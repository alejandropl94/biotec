<?php

    // Connection
    include_once 'connection.php';
    set_time_limit(300);
    $sql_e = connectDB();
    /////

    // Variables
    $limit = 300;
    define('PREFIJO', 'doli_');
    /////

    // Incluir la librería
    require_once 'class/PHPExcel.php';
    $objPHPExcel = new PHPExcel();
    /////
            
    // Información Excel
    $objPHPExcel->
    getProperties()
            ->setCreator("promaja.com.mx")
            ->setLastModifiedBy("promaja.com.mx")
            ->setTitle("Exportación Niveles de Precio Promaja")
            ->setSubject("Productos")
            ->setDescription("Reporte de los productos en el sistema con sus correspondientes precios.")
            ->setKeywords("promaja.com.mx")
            ->setCategory("Productos");
    /////

    // Encabezado
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', 'ID');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1', 'REF. PRODUCTO');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1', 'ETIQUETA');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D1', 'DESCRIPCIÓN');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E1', 'NIVEL DE PRECIO');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F1', 'PRECIO SIN IVA');  
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G1', 'PRECIO CON IVA');  
    /////

    if(!isset($sql_e)) {

        $sql = "SELECT * FROM ".PREFIJO."product";
        $result = mysql_query($sql) or die('ERROR 1 al exportar los productos: '.mysql_error()); 
        $rows = mysql_num_rows($result);
          
        if ($rows > 0) {

            if (isset($_GET['id'])) {

                $num_reg = $_GET['id'] * $limit;

                if($_GET['id'] > 1) {
                    $empiezo = ($_GET['id'] - 1) * $limit;
                    $offset = ' OFFSET '.$empiezo;
                }

                $sql = "SELECT * FROM ".PREFIJO."product ORDER BY ref ASC LIMIT $limit";
                if (isset($offset)) $sql .= $offset;

                $result = mysql_query($sql) or die('ERROR 2 al exportar los productos: '.mysql_error());

                $i = 2;

                while($row = mysql_fetch_object($result)) {

                    /////
                    $sql_1 =    "SELECT * FROM (SELECT * FROM ".PREFIJO."product_price pp WHERE pp.fk_product = $row->rowid 
                                ORDER BY pp.rowid DESC) sub GROUP BY sub.price_level";

                    $result_1 = mysql_query($sql_1) or die("ERROR al buscar los precios: $row->ref, ".mysql_error()); 

                    while($row_1 = mysql_fetch_object($result_1)) {
                    
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$i, $row->rowid);
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$i, $row->ref);
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$i, $row->label);
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$i, $row->description);
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$i, $row_1->price_level);
                        if($row_1->price_base_type == 'HT') $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$i, $row_1->price);
                        if($row_1->price_base_type == 'TTC') $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$i, $row_1->price_ttc);                  
                        $i++;  

                    }
                    /////

                }

                // die("$sql");
            }                       

        }

        $date = getdate();
        $nom_archivo = 'pre_cli_'.$date['mday'].'-'.$date['mon'].'_'.$date['hours'].'-'.$date['minutes'];

        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$nom_archivo.xls");
        header('Cache-Control: max-age=0');
         
        $objWriter=PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
        $objWriter->save('php://output');

        exit;

    }
?>