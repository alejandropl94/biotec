<?php

include_once "connection.php";
include_once "class/template.class.php";
include_once "class/pagemaker.class.php";

set_time_limit(300);

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';					// to work if your module directory is into dolibarr root htdocs directory
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';			// to work if your module directory is into a subdir of root htdocs directory
if (! $res && file_exists("../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../dolibarr/htdocs/main.inc.php';     // Used on dev env only
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../../dolibarr/htdocs/main.inc.php';   // Used on dev env only
if (! $res) die("Include of main fails");
// Change this following line to use the correct relative path from htdocs
dol_include_once('/module/class/skeleton_class.class.php');

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
ini_set('max_execution_time', 300);
// Get parameters
$id			= GETPOST('id','int');
$action		= GETPOST('action','alpha');
$myparam	= GETPOST('myparam','alpha');

// Protection if external user
if ($user->societe_id > 0)
{
	//accessforbidden();
}

/*******************************************************************
* 
* ACTIONS
* 
********************************************************************/

function cargarArchivo(&$name){

    if($_FILES) {

        $name = $_FILES['filename']['name'];
        
        switch ($_FILES['filename']['type']) {
            
            case 'application/vnd.ms-excel': 
                $ext = 'csv';
                break;
            
            default:
                $ext = 'xlsx';
                break;

        }

        if($ext == 'csv') {

            move_uploaded_file($_FILES['filename']['tmp_name'], $name);
            echo "<p>Carga de archivo exitosa<p>";
            return true;

        } else {

            echo "<p>El archivo no cuenta con la extensión .csv</p>";
            return false;

        }

    }

}

/*******************************************************************
* 
* VIEW
* 
********************************************************************/

llxHeader('','Importar','');

echo '<link rel="stylesheet" type="text/css" href="css/style.css">';

$form=new Form($db);

// Poner aquí el contenido de la página

// Menu
if($action == 'menu') {

	$link1 = make_link('Precios al público', 'pagetop.php?action=prices_cli');
    $link2 = make_link('Precios de proveedores', 'pagetop.php?action=prices_prov');
    //$link3 = make_link('Niveles de precio clientes', 'pagetop.php?action=exp_pre_cli');
    // para subir
    //$link4 = make_link('Pagos', 'pagetop.php?action=exp_ticket');

	echo <<<_END
		<h3>Importaciones: </h3>
		$link1
        $link2
_END;

}
/////

// Niveles de Precio a Clientes
if($action == 'prices_cli') {

	$f_uploaded = false;
    $name;

	echo <<<_END
	    <form method='post' action='' enctype='multipart/form-data'>
            <h3>Importación de precio al público</h3>
	        <div class="titre">Seleccionar plantilla *.csv
                <img src="../theme/md/img/info.png" border="0" alt="" title="Para convertir un archivo de excel a CSV, ve a archivo, guardar como y en tipo poner CSV(MS-DOS)" class="hideonsmartphone">
                <a id="show_info" href="#">Ejemplo platilla</a>
                <div id="info_sheet" hidden="true">
                    <img src="../theme/common/plantilla_import.PNG" title="La lista de productos debe iniciar a partir de la fila 2 y los datos deben ir en el orden indicado en las columnas (A - F)">
                </div>
            </div><br>
	        <input type='file' name='filename' size='10'>
	        <input type='submit' class="button" value='Importar' class="">
	    </form>
_END;
    print ' <script>

                $("#show_info").click(function(){
                    $("#info_sheet").toggle();
                });
            </script>';

    $f_uploaded = cargarArchivo($name);

    if($f_uploaded) {
        print '<div style="max-height:400px; overflow-y:auto">
                <table width="100%">';
        $handle = fopen($name, 'r');
        $count_updated = 0;
        $count_inserted = 0;
        $i = 0;

        while($reg = fgetcsv($handle, 1000, ',')) {
            $template = new Template();
            $i++;
            if($i != 1) {

                //if(!empty($reg[0])) $template->id = $reg[0];
                if(!empty($reg[0])) $template->ref = $reg[0];
                if(!empty($reg[1])) $template->label = $reg[1]." | ".$reg[2]." | ".$reg[3];
                //if(!empty($reg[3])) $template->desc = $reg[3]; 
                //if(!empty($reg[4])) $template->price_level = $reg[4];
                if(!empty($reg[5])) $template->tax = $reg[5];  else $template->tax = 0;
                if(!empty($reg[4])) {

                    $template->price = $reg[4];
                    $template->price_base = 'HT';
                    $template->price_ttc = $template->price + ($template->price * $template->tax / 100);

                }
                /*if(!empty($reg[6])) {

                    $template->price_ttc = $reg[6]; 
                    $template->price_base = 'TTC'; 
                    $template->price = $template->price_ttc / (1 + $template->tax / 100);

                }*/
                // if(!empty($reg[8])) $template->price_min = $reg[8]; else $template->price_min = 0.0; 
                // if(!empty($reg[9])) $template->price_min_ttc = $reg[9]; else $template->price_min_ttc = 0.0; 
                // if(!empty($reg[11])) $template->insell = $reg[11];
                // if(!empty($reg[12])) $template->inbuy = $reg[12];
                // if(!empty($reg[13])) $template->date = $reg[13];
                $sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'product WHERE ref = "'.$template->ref.'" OR label = "'.$template->label.'"';
                $resql = $db->query($sql);
                if($resql){
                    $num = $db->num_rows($resql);
                    if($num){
                        $obj = $db->fetch_object($resql);
                        $sql_update = "UPDATE ".MAIN_DB_PREFIX."product SET price = $template->price, price_base_type = '$template->price_base', label = '$template->label', ref = '$template->ref', price_ttc = $template->price_ttc, tva_tx = $template->tax WHERE rowid = '$obj->rowid'";
                        $template->id = $obj->rowid;
                        $db->free($resql);
                        $resql_update = $db->query($sql_update);
                        if($resql_update){
                            $count_updated +=1;
                            echo '<tr class="impair">';
                            echo '<td title="Código">'.$template->ref;
                            echo '<td title="Etiqueta">'.$template->label;
                            echo '<td align="right" title="Precio sin IVA">$ '.number_format($template->price,2);
                            echo '<td align="right" title="Precio con IVA">$ '.number_format($template->price_ttc,2);
                            echo '<td align="right" title="IVA">'.$template->tax;

                        }else{
                            echo mysql_error();
                        }
                        $db->free($resql_update);
                    }else{
                        $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."product (ref, datec, label, description, price_base_type, price, price_ttc, price_min, price_min_ttc, tva_tx, tosell, tobuy) 
                                VALUES ('$template->ref', now(), '$template->label', '$template->desc','$template->price_base', $template->price, $template->price_ttc, 
                                0, 0, $template->tax, 1, 1)";
                        $resql_insert = $db->query($sql_insert);
                        if($resql_insert){
                            $template->id = $db->last_insert_id(MAIN_DB_PREFIX."product");
                            $count_inserted +=1;
                            echo '<tr class="pair" style="background-color: #DADAFF!important" title="Este producto fue insertado con la referencia:'.$template->ref.'">';
                            echo '<td>'.$template->ref;
                            echo '<td>'.$template->label;
                            echo '<td align="right">$ '.$template->price;
                            echo '<td align="right">$ '.$template->price_ttc;
                            echo '<td align="right" title="IVA">'.$template->tax;    
                        }else{
                            echo mysql_error();
                        }
                        $db->free($resql_insert);
                    }

                    $sql_prod_price = " INSERT INTO ".MAIN_DB_PREFIX."product_price (fk_product, date_price, price_level, price, price_ttc, price_min, price_min_ttc, price_base_type, tva_tx, fk_user_author) VALUES ($template->id, now(), 1, $template->price, $template->price_ttc, 0, 0, '$template->price_base', $template->tax, $user->id)";
                    $resql_prod_price = $db->query($sql_prod_price);
                    if ($resql_prod_price) {
                        //inserto en llx_product_price
                        $db->free($resql_prod_price);
                    }else{
                        #echo $db->lastqueryerror;
                    }
                }else{
                    echo $db->lastqueryerror;
                }

            }
            unset($template);

        }
        $db->commit();
        print '</table></div>';
        echo '<p>Importación exitosa ('.$count_updated.') Productos actualizados y ('.$count_inserted.') productos registrados</p>';
        //chmod(getcwd(), 0777);
        fclose($handle);
        try {
            unlink($_FILES['filename']['name']);
        } catch (Exception $e) {
            print_r($e);
        }
        

    }

    echo "<br><br>".make_link('Regresar', 'pagetop.php?action=menu');


}
/////

// Niveles de Precio a Proveedores
if($action == 'prices_prov') {
die("EN DESARROLLO");
    $f_uploaded = false;

    echo <<<_END
        <form method='post' action='' enctype='multipart/form-data'>
            <h3>Importación niveles de precio Proveedores</h3>
            <p>Seleccionar plantilla.csv:</p>
            <input type='file' name='filename' size='10'>
            <input type='submit' value='cargar'>
        </form>
_END;

    if($_FILES) {

        $name = $_FILES['filename']['name'];
        
        switch ($_FILES['filename']['type']) {
            
            case 'application/vnd.ms-excel': 
                $ext = 'csv';
                break;
            
            default:
                $ext = 'xlsx';
                break;

        }

        if($ext == 'csv') {

            move_uploaded_file($_FILES['filename']['tmp_name'], $name);
            $f_uploaded = true;

        } else {

            echo "<p>El archivo no cuenta con la extensión .csv</p>";
            $f_uploaded = false;

        }

    }

    if($f_uploaded) {

        $handle = fopen($name, 'r');
        $template = new Template();

        $i = 0;

        while($reg = fgetcsv($handle, 1000, ',')) {

            $i++;
            if($i != 1) {

                if(!empty($reg[0])) $template->ref = $reg[0];
                if(!empty($reg[1])) $template->prov = $reg[1];
                if(!empty($reg[2])) $template->ref_prov = $reg[2];
                if(!empty($reg[3])) $template->tax = $reg[3];
                if(!empty($reg[4])) $template->price = $reg[4]; 

                // Debug
                // echo "<p>$template->ref, $template->prov, $template->ref_prov, $template->tax, $template->price</p>";
                // die();

                $sql_e = connectDB(); 

                if(!isset($sql_e)) { 

                    // Find product from ref
                    $query = "SELECT rowid FROM ".MAIN_DB_PREFIX."product WHERE ref = '$template->ref'";
                    $result = mysql_query($query) or die("<p>ERROR al buscar el producto: $template->ref, ".mysql_error()."</p>");

                    if($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

                        $prod_rowid = $row['rowid'];
                        //echo "Existe el producto: $prod_rowid ";

                    } else die("<p>No existe el producto: $template->ref</p>");

                    // Find provider from nom
                    $query = "SELECT rowid FROM ".MAIN_DB_PREFIX."societe WHERE nom = '$template->prov'";
                    $result = mysql_query($query) or die("<p>ERROR al buscar el proveedor: $template->prov, ".mysql_error()."</p>");

                    if($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

                        $prov_rowid = $row['rowid'];
                        //echo "Existe el proveedor: $prov_rowid ";

                    } else die("<p>No existe el proveedor: $template->prov</p>");

                    // --- Checkpoint: product and provider validated ---
                    //die();

                    // Get the real price
                    $real_price = $template->price / ( 1 + $template->tax / 100); 
                    //die("Precio real = $real_price");
                    /////

                    $query =    "INSERT INTO ".MAIN_DB_PREFIX."product_fournisseur_price (datec, fk_product, fk_soc, ref_fourn, fk_availability, quantity, tva_tx, price, unitprice, remise_percent)  
                                VALUES (now(), $prod_rowid, $prov_rowid, '$template->ref_prov', 0, 1, $template->tax, $real_price, $real_price, 0)";

                    //$result = mysql_query($query) or die("<p>ERROR al asignar precio de proveedor: ".mysql_error()."</p>");
                    $result = mysql_query($query) or ($sql_e = mysql_error());

                    if(isset($sql_e)) {

                        if($sql_e[0] == 'D' && $sql_e[1] == 'u' && $sql_e[2] == 'p') {

                                unset($sql_e);

                                $query =    "UPDATE ".MAIN_DB_PREFIX."product_fournisseur_price SET datec=now(), fk_product=$prod_rowid, fk_soc=$prov_rowid, 
                                            fk_availability=0, quantity=1, tva_tx=$template->tax, price=$real_price, unitprice=$real_price, remise_percent=0 
                                            WHERE ref_fourn='$template->ref_prov'";
                                // echo "$query";
                                $result = mysql_query($query) or die("<p>ERROR al actualizar precio a proveedor: $template->ref_prov, ".mysql_error()."</p>");
                                // echo "<p>Actualización de precio a Proveedor correcta</p>";

                        } else die("<p>ERROR al asignar precio de proveedor: ".mysql_error()."</p>");
                    
                    }

                } else break;

            }

        }

        if(isset($sql_e)) echo "<p>$sql_e</p>";
        else echo '<p>Importación exitosa</p>';

    }

    $link = make_link('Regresar', 'pagetop.php?action=menu');

    echo <<<_END
        <br>
        $link
_END;

}
/////

if($action == 'exp_pre_cli') {

    $limit = 300;

    $sql = "SELECT * FROM "."doli_"."product";
    $resql = $db->query($sql);

    if($resql) {

        $num = $db->num_rows($resql);
        $i = 0;
        $cont = 1;
        echo '<p>Exportación por partes:</p>';

        while($i < $num) {

            $i += $limit;
            echo $link = make_link("Parte $cont", "exportacion.php?id=$cont");
            $cont++;
            echo '<br><br>';

        }

    } else die("ERROR");

}

if($action == 'exp_ticket') {

    $c1 = '';
    $c2 = '';
    $c3 = '';

    if(isset($_POST['fec'])) $c1 = 'checked';
    if(isset($_POST['cli'])) $c2 = 'checked';
    if(isset($_POST['usu'])) $c3 = 'checked';

    echo <<<_END
    
    <form method='POST' action='pagetop.php?action=exp_ticket'>
           <h3>Exportación de Pagos</h3>
           <div class='blockvmenusearch' style='width: 350px; height:110px;'>
               <p><input type='checkbox' name='fec' value='1' $c1> Fecha: de <input type='text' name='inicio' maxlength="10" size="8"> a <input type='text' name='final' maxlength="10" size="8"></p>
               <p><input type='checkbox' name='cli' value='1' $c2> Cliente: <input type='text' name='cliente' size="30"> </p>
               <p><input type='checkbox' name='usu' value='1' $c3> Usuario: <input type='text' name='usuario' size="30"> </p>
               <input type='hidden' name='nada' value='1'>
               <p><input type='submit' value='Crear Partes' style="float:right;margin-right:10px;"></p>
           </div>
    </form>

_END;

    $limit = 300;

    $sql = "SELECT p.datec, f.facnumber, s.code_client, s.nom, p.amount, pe.concepto, p.note, u.lastname, u.firstname  FROM ".MAIN_DB_PREFIX."paiement p 
            LEFT JOIN ".MAIN_DB_PREFIX."paiement_facture pf ON pf.fk_paiement = p.rowid
            LEFT JOIN ".MAIN_DB_PREFIX."facture f ON f.rowid = pf.fk_facture
            LEFT JOIN ".MAIN_DB_PREFIX."societe s ON s.rowid = f.fk_soc
            LEFT JOIN ".MAIN_DB_PREFIX."paiement_extras pe ON pe.fk_paiement = p.rowid
            LEFT JOIN ".MAIN_DB_PREFIX."user u ON u.rowid = p.fk_user_creat WHERE 1 = 1";

    if(isset($_POST['fec'])){
        $sql .= " AND p.datec BETWEEN '$_POST[inicio] 00:00:00' AND '$_POST[final] 23:59:59'";
    }

    if(isset($_POST['cli'])){
        $sql .= " AND s.nom LIKE '$_POST[cliente]'";
    }

    if(isset($_POST['usu'])){
        $sql .= " AND (u.lastname LIKE '$_POST[usuario]' OR u.firstname LIKE '$_POST[usuario]')";
    }

    echo $sql;

    $resql = $db->query($sql);

    if($resql) {

        $num = $db->num_rows($resql);
        $i = 0;
        $cont = 1;
        echo '<p>Exportación por partes:</p>';

        if ($num > 0) {

            while($i < $num) {

                $i += $limit;
                echo $link = make_link("Parte $cont", "exportacion2.php?id=$cont&fecha=$_POST[fec]&ini=$_POST[inicio]&fin=$_POST[final]&cli=$_POST[cli]&cliente=$_POST[cliente]&usu=$_POST[usu]&usuario=$_POST[usuario]");
                $cont++;
                echo '<br><br>';

            }

        } else echo "<p>No hay resultados</p>";

    } else die("ERROR");

}

// Final de la página
llxFooter();
$db->close();
