<?php
/* Copyright (C) 2007-2008	Jeremie Ollivier	<jeremie.o@laposte.net>
 * Copyright (C) 2011		Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2011		Juanjo Menent		<jmenent@2byte.es>
 * Copyright (C) 2015		Regis Houssin		<regis.houssin@capnetworks.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

$langs->load("main");
$langs->load("bills");
$langs->load("cashdesk");

// Object $form must de defined

?>

<script type="text/javascript" src="javascript/facturation1.js"></script>
<script type="text/javascript" src="javascript/dhtml.js"></script>
<script type="text/javascript" src="javascript/keypad.js"></script>

<!-- ========================= Cadre "Article" ============================= -->
<fieldset class="cadre_facturation"><legend class="titre1"><?php echo $langs->trans("Article"); ?></legend>
	<form id="frmFacturation" class="formulaire1" method="post" action="facturation_verif.php" autocomplete="off">
		<input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>" />

		<input type="hidden" name="hdnSource" value="NULL" />

		<table style="width: 100%;">
			<tr><th class="label1" style="text-align:left;"><?php echo $langs->trans("FilterRefOrLabelOrBC"); ?></th></tr>
			<tr>
			<!-- Affichage de la reference et de la designation -->
			<td><input class="texte_ref" type="text" id ="txtRef" name="txtRef" style="width: 100%;" value="<?php echo $obj_facturation->ref() ?>"
				onkeyup="javascript: verifResultat('resultats_dhtml', this.value, <?php echo (isset($conf->global->BARCODE_USE_SEARCH_TO_SELECT) ? (int) $conf->global->BARCODE_USE_SEARCH_TO_SELECT : 1) ?>);"
				onfocus="javascript: this.select(); verifResultat('resultats_dhtml', this.value, <?php echo (isset($conf->global->BARCODE_USE_SEARCH_TO_SELECT) ? (int) $conf->global->BARCODE_USE_SEARCH_TO_SELECT : 1) ?>);" />
			</td>
			</tr>
			  <tr><td><div id="resultats_dhtml"></div></td></tr>
		</table>
	</form>

	<form id="frmQte" class="formulaire1" method="post" action="facturation_verif.php?action=ajout_article" onsubmit ="javascript: return verifSaisie();">
		<input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>" />
		<table>
			<tr>
				<td><input class="texte1" type="hidden" id="txtQte" name="txtQte" value="1" onkeyup="javascript: modif();" onfocus="javascript: this.select();" />
<?php print genkeypad("txtQte", "frmQte");?>
				</td>
				<!-- Affichage du stock pour l'article courant -->
				<td>
				<input class="texte1_off" type="hidden" name="txtStock" value="<?php echo $obj_facturation->stock() ?>" disabled />
				</td>
				<!-- Show unit price -->
				<?php // TODO Remove the disabled and use this value when adding product into cart ?>
				<td><input class="texte1_off" type="hidden" name="txtPrixUnit" value="<?php echo price2num($obj_facturation->prix(), 'MU'); ?>" onchange="javascript: modif();" disabled /></td>
				<td></td>
    			<!-- Choix de la remise -->
    			<td><input class="texte1" type="hidden" id="txtRemise" name="txtRemise" value="0" onkeyup="javascript: modif();" onfocus="javascript: this.select();"/>
					<?php print genkeypad("txtRemise", "frmQte");?>
    			</td>
    			<!-- Affichage du total HT -->
    			<td><input class="texte1_off" type="hidden" name="txtTotal" value="" disabled /></td>
    			<td></td>
                <!-- Choix du taux de TVA -->
          <td class="select_tva">
          <?php //var_dump($tab_tva); ?>
          <select hidden="true" name="selTva" onchange="javascript: modif();" >
              <?php
                  $tva_tx = $obj_facturation->tva();  // Try to get a previously entered VAT rowid. First time, this will return empty.

                  $tab_tva_size=count($tab_tva);      // $tab_tva contains list of possible vat array('rowid'=> , 'taux'=> ) 
                  for ($i=0;$i < $tab_tva_size;$i++) 
                  {
                      if ($tva_tx == $tab_tva[$i]['rowid'])
                          $selected = 'selected';
                      else
                          $selected = '';

                      echo '<option '.$selected.' value="'.$tab_tva[$i]['rowid'].'">'.$tab_tva[$i]['taux'].'</option>'."\n               ";
                  }
              ?>
          </select>
          </td>
			</tr>
		</table>
	</form>
</fieldset>

<script>
	if(document.getElementById('txtRef').value != "")
		document.getElementById('frmQte').submit()
</script>


<fieldset class="cadre_facturation">
	<legend class="titre1"><?php echo $langs->trans("Productos"); ?></legend>
	<form id="frmProducts" class="formulaire1" method="post" action="facturation_verif.php?action=change_articles" autocomplete="off">
		<input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>" />
		<input type="hidden" name="hdnSource" value="NULL" />
	<?php 
		$tab=array();
		$tab = $_SESSION['poscart'];

		$tab_size=count($tab);
		print "<input type='hidden'id='last_id' value='".($tab_size - 1)."'/>";
		if ($tab_size <= 0)
			print '<div class="center">'.$langs->trans("NoArticle").'</div><br>';
		else
		{
			print "<table><tr><th>Cantidad</th><th></th><th>Producto</th><th></th><th>Precio con IVA</th><th></th><th>Total</th><th></th></tr>";
			for ($i=0;$i < $tab_size;$i++)
			{
				echo "<tr>";
				echo '<td><input class="texte1" type="text" name="rowQty'.$tab[$i]['id'].'" id="rowQty'.$tab[$i]['id'].'" value="'.$tab[$i]['qte'] .'" /></td>';
				echo "<td></td>";
				echo "<td>".$tab[$i]['ref'].' - '.$tab[$i]['label']."</td>";
				echo "<td></td>";
				echo "<td>".price(price2num($tab[$i]['price_ttc'], 'MT'),0,$langs,0,0,-1,$conf->currency)."</td>";
				echo "<td></td>";
				echo "<td>".price(price2num($tab[$i]['total_ttc'], 'MT'),0,$langs,0,0,-1,$conf->currency)."</td>";
				echo '<td><p><a href="facturation_verif.php?action=suppr_article&suppr_id='.$tab[$i]['id'].'" title="'.$langs->trans("DeleteArticle").'">Quitar</p></a></td>';
				echo "</tr>";
			}
			print "</table>";
		}
	?>
	<button type="submit" hidden="true"></button>
	</form>
</fieldset>

<table style="width:100%;">
	<tr>
		<td>
			( "<b>,</b>" )
		</td>
		<td>
			Editar referencia
		</td>
	</tr>
	<tr>
		<td>
			( "<b>.</b>" )
		</td>
		<td>
			Editar ultima cantidad
		</td>
	</tr>
	<tr>
		<td>
			( "<b>-</b>" )
		</td>
		<td>
			Editar cantidad recibida
		</td>
	</tr>
</table>

<script type="text/javascript">
/*	Calendar.setup ({
		inputField	: "txtDatePaiement",
		ifFormat	: "%Y-%m-%d",
		button		: "btnCalendrier"
	});
*/
	if (document.getElementById('frmFacturation').txtRef.value) {

		modif();
		document.getElementById('frmQte').txtQte.focus();
		document.getElementById('frmQte').txtQte.select();

	} else {

		document.getElementById('frmFacturation').txtRef.focus();

	}

	$(document).keypress(function(e) {
		if (e.key == ".")
		{
			e.preventDefault();
			$("#rowQty"+$("#last_id").val()).focus();
			$("#rowQty"+$("#last_id").val()).select()
		}
		else if (e.key == "-")
		{
			e.preventDefault();
			$("#txtEncaisse").focus();
			$("#txtEncaisse").select()
		}
		else if (e.key == ",")
		{
			e.preventDefault();
			$("#txtRef").focus();
			$("#txtRef").select()
		}
	});

</script>
