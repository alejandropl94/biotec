
<h2>
  Venta
</h2>

<table>
  <thead>
    <tr>
      <th>
      </th>
      <th>
        Referencia
      </th>
      <th>
        Fecha
      </th>
      <th>
        Hora de venta
      </th>
      <th>
        Monto base
      </th>
      <th>
        IVA
      </th>
      <th>
        Total
      </th>
    </tr>
  </thead>
  <tbody>

<?php

$sql_query = 'SELECT facture.facnumber, facture.date_valid, DATE_FORMAT(facture.tms, "%h:%i %p") AS time, facture.tva, facture.total, facture.total_ttc FROM llx_facture AS facture WHERE facture.date_valid = CURDATE()';
$result = $db->query($sql_query);
$i = 1;
$total = 0;
if ($result)
{
  while ($facture = $db->fetch_object($result) )
  {
    //echo "<pre>";
    //print_r($facture);
    //echo "</pre>";
    echo "<tr>";
    echo "<td style='padding-left:20px;'>".$i."</td>";
    echo "<td style='padding-left:20px;'>".$facture->facnumber."</td>";
    echo "<td style='padding-left:20px;'>".dol_print_date($facture->date_valid, 'daytext')."</td>";
    echo "<td style='padding-left:20px;'>".$facture->time."</td>";
    echo "<td style='padding-left:20px;'>".price($facture->total, 1, '', 1, - 1, - 1, $conf->currency)."</td>";
    echo "<td style='padding-left:20px;'>".price($facture->tva, 1, '', 1, - 1, - 1, $conf->currency)."</td>";
    echo "<td style='padding-left:20px;'><b>".price($facture->total_ttc, 1, '', 1, - 1, - 1, $conf->currency)."</b></td>";
    echo "</tr>";

    $total += $facture->total_ttc;
    $i++;
  }

  echo "<tr>";
  echo "<td style='padding-left:20px;'></td>";
  echo "<td style='padding-left:20px;'></td>";
  echo "<td style='padding-left:20px;'></td>";
  echo "<td style='padding-left:20px;'></td>";
  echo "<td style='padding-left:20px;'></td>";
  echo "<td style='padding-left:20px;'></td>";
  echo "<td style='padding-left:20px;padding-top:10px;border-top-style:solid;'><b>".price($total, 1, '', 1, - 1, - 1, $conf->currency)."</b></td>";
  echo "</tr>";

  $db->free($result);
}
else
{
  dol_print_error($db);
}

?>
  </tbody>
</table>

