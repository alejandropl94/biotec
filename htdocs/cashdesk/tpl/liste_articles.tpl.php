<?php
/* Copyright (C) 2007-2008	Jeremie Ollivier	<jeremie.o@laposte.net>
 * Copyright (C) 2011		Juanjo Menent		<jmenent@2byte.es>
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

require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

$langs->load("main");
$langs->load("bills");
$langs->load("cashdesk");

?>

<div class="liste_articles_haut">
<div class="liste_articles_bas">

<p class="titre"><?php echo $langs->trans("Venta"); ?></p>

<?php
/** add Ditto for MultiPrix*/
$thirdpartyid = $_SESSION['CASHDESK_ID_THIRDPARTY'];
$societe = new Societe($db);
$societe->fetch($thirdpartyid);
/** end add Ditto */

?>

<form id="frmDifference"  class="formulaire1" method="post" onsubmit="javascript: return verifReglement()" action="validation_verif.php?action=valide_achat" autocomplete="off">
  <input type="hidden" name="hdnChoix" value="" />
  <input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>" />
  <table style="width:100%;">
    <tr>
      <!-- Affichage du montant du -->
      <td>
        <input class="texte2_off" type="hidden" name="txtDu" value="<?php echo price2num($obj_facturation->prixTotalTtc(), 'MT'); ?>" disabled />
      </td>
    </tr>
    <tr>
      <th class="label1">
        <?php echo $langs->trans("Received"); ?>
      </th>
    </tr>
    <tr>
      <!-- Choix du montant encaisse -->
      <td>
        <input class="texte2" type="text" id="txtEncaisse" name="txtEncaisse" value="" onkeyup="javascript: verifDifference();" onfocus="javascript: this.select();" />
        <?php print genkeypad("txtEncaisse", "frmDifference");?>
      </td>
    </tr>
    <tr>
      <th class="label1">
        <?php echo $langs->trans("Change"); ?>
      </th>
    </tr>
    <tr>
      <!-- Affichage du montant rendu -->
      <td>
        <input class="texte2_off" type="text" name="txtRendu" value="0" disabled />
      </td>
    </tr>
  </table>

  <?php
  if (empty($_SESSION['CASHDESK_ID_BANKACCOUNT_CASH']) || $_SESSION['CASHDESK_ID_BANKACCOUNT_CASH'] < 0)
  {
    $langs->load("errors");
    print '<input class="bouton_mode_reglement_disabled" type="button" name="btnModeReglement" value="'.$langs->trans("Terminar venta").'" title="'.dol_escape_htmltag($langs->trans("ErrorModuleSetupNotComplete")).'" />';
  }
  else print '<input class="button bouton_mode_reglement" type="submit" name="btnModeReglement" value="'.$langs->trans("Terminar venta").'" onclick="javascript: verifClic(\'ESP\');" />';
  ?>
</form>
<br/>

<?php
echo ('<p class="cadre_prix_total">'.$langs->trans("Total").' : '.price(price2num($total_ttc, 'MT'),0,$langs,0,0,-1,$conf->currency).'<br></p>'."\n");

?></div>
</div>
