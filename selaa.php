<!-- Design Factory Project -kurssin työ Kirjanpitäjä, Virve Rajasärkkä HAMK 2023
     Tositteiden selaus ja haku -->

<!-- Valmistelevat toimet -->

<?php
    include("sanitation.php");
    $title = "Selaa tositteita";
    $css2 = "css/selaa.css";
    $sivunOtsikko ="Selaa tositteita";
    $ohjeteksti = '
        <div style="display: flex"><div style="flex: 5"><h3>Ohjeet</h3></div><div style="flex: 1"><p>.</p></div></div>
            <div style="text-align:left">
                <h4 style="text-align:left">Tositteiden hakeminen</h4>
                <ul class="instructionsUl"><li><p>Voit hakea tositteita haluamallasi ehdoilla.</p></li>
                    <li><p>Voit valita näytettäväksi vain meno- tai tulotositteet.</p></li>
                    <li><p>Jos ehtoja ei ole valittu, näytetään kaikki tositteet.</p></li>
                    <li><p>Voit järjestää näytettävät tositteet sarakeotsikkoa painamalla.</p></li></ul>
                <h4 style="text-align:left">Tositteen muokkaaminen ja poisto</h4>
                <ul class="instructionsUl"><li><p>Avaa tosite muokattavaksi painamalla mitä tahansa kohtaa haluamallasi rivillä.</p></li>
                    <li><p>Muokkaa haluamasi kohdat ja paina Tallenna.</p></li>
                    <li><p>Tositteen voi myös poistaa muokkausnäkymässä.</p></li>
                    <li><p>Tositteen tyyppiä ei voi vaihtaa. Voit poistaa virheellisen tositteen ja lisätä uuden.</p></li></ul>
                </div>';
    include("headerAndAside.php");
    if (empty($_SESSION['user_name'])) { echo '<script>window.location.href = "index.php";</script>'; }
    else if (empty($_SESSION['organization_id'])) { echo '<script>alert("Valitse ensin organisaatio, jonka tositteita haluat selata.");window.location.href = "organisaatio.php";</script>'; }
    else { $rivit = array(); $organizationId = cleanBusinessId($_SESSION['organization_id']); }
?>
<script src="selaa.js"></script>

<!-- Html-sisältö alkaa -->

<div class="pageContent">

<!-- Hakuehdot -->

    <h3 class="hakuotsikko">Valitse hakuehdot</h3>
    <form method="post" class="selaaLomake">

    <!-- Aika- ja tositetyyppivalinnat -->
        <div class="selaaOsio"><label for="selaaAlku">Alkaen</label><input type="date" name="selaaAlku">
            <label for="selaaLoppu">Päättyen</label><input type="date" name="selaaLoppu">
            <input type="radio" name="selaaTyyppi" value="meno">
            <label for="selaaTyyppi2">Vain menotositteet</label>
            <input type="radio" name="selaaTyyppi" value="tulo">
            <label for="selaaTyyppi">Vain tulotositteet</label>
        </div>

    <!-- Sanahaut -->
        <div class="selaaOsio">
            <label for="selaaYhteys">Asiakas tai toimittaja</label><input type="text" name="selaaYhteys">
            <label for="selaaSelite">Sanahaku selitteestä</label><input type="text" name="selaaSelite">
            <input type="submit" name="hae" class="selaaNappi" value="Hae" style="float:right;">
        </div>

    </form>
    
<!-- Valittujen hakuehtojen asetus ja tositteiden haku -->

    <?php
        if (isset($organizationId)) {
            $ehdot[0] = $organizationId;
            $sql2a = ""; $sql2b = "";
                    
            if (isset($_POST['hae'])) {
                if (!(empty($_POST['selaaAlku']))) {
                    $alku = numbers($_POST['selaaAlku']);
                    array_push($ehdot, $alku);
                    $sql2a .= " AND Entry_Date >= str_to_date(?, '%Y-%m-%d')";
                    $sql2b .= " AND Entry_Date >= str_to_date(?, '%Y-%m-%d')";
                }
                if (!(empty($_POST['selaaLoppu']))) {
                    $loppu = numbers($_POST['selaaLoppu']);
                    array_push($ehdot, $loppu);
                    $sql2a .= " AND Entry_Date <= str_to_date(?, '%Y-%m-%d')";
                    $sql2b .= " AND Entry_Date <= str_to_date(?, '%Y-%m-%d')";
                }
                if (!(empty($_POST['selaaSelite']))) {
                    $sel = '%' . lettersOnly($_POST['selaaSelite']) . '%';
                    array_push($ehdot, $sel);
                    $sql2a .= " AND Line_Statement LIKE ?";
                    $sql2b .= " AND Line_Statement LIKE ?";
                }
                if (!(empty($_POST['selaaYhteys']))) {
                    $yht = '%' . lettersOnly($_POST['selaaYhteys']) . '%';
                    array_push($ehdot, $yht);
                    $sql2a .= " AND Expenditure_Supplier LIKE ?";
                    $sql2b .= " AND Customer_ID LIKE ?";
                }
                if (!(empty($_POST['selaaTyyppi']))) {
                    $tyyppi = lettersOnly($_POST['selaaTyyppi']);
                    array_push($ehdot, $tyyppi);
                    $sql2a .= " AND Form_Type = ?";
                    $sql2b .= " AND Form_Type = ?";
                }
            }

            # haku - menotositteet
            $sql = "SELECT ExpenditureVoucher.*, AccountList.AccountName
                    FROM ExpenditureVoucher INNER JOIN AccountList ON Expenditure_Account = AccountNumber_ID
                    WHERE Organization_ID = ?" . $sql2a . " AND 1 = 1 ORDER BY Voucher_No DESC;";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($ehdot);
            $rivit = $stmt->fetchAll();
            $pdo->connection = null;

            # haku - tulotositteet
            $sql = "SELECT IncomeVoucher.*, AccountList.AccountName
                    FROM IncomeVoucher INNER JOIN AccountList ON Income_Account = AccountNumber_ID
                    WHERE Organization_ID = ?" . $sql2b . " AND 1 = 1 ORDER BY Voucher_No DESC;";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($ehdot);
            $rivit2 = $stmt->fetchAll();
            $rivit = array_merge($rivit, $rivit2);
            $pdo->connection = null;
        }
    ?>

<!-- Löytyneiden tositteiden tulostus taulukkoon -->

    <h3 class="hakuotsikko">Tositteet</h3>
    <div class="tulosboxi">
        <?php
            $ei = "Hakuehdoilla ei löydy tositteita.";

            if (count($rivit) == 0) { echo '<p class="selaaTaulukko">' . $ei . '</p>'; }
            else { echo '
                <table class="selaaTaulukko" id="tulokset">
                    <tr>
                        <th onClick="lajittele(0)" class="pointer">Nro</th>
                        <th onClick="lajittele(1)" class="pointer">Pvm</th>
                        <th onClick="lajittele(2)" class="pointer">Otsikko</th>
                        <th onClick="lajittele(3)" class="pointer">Tyyppi</th>
                        <th onClick="lajittele(4)" class="pointer">Tili</th>
                        <th onClick="lajittele(5)" class="pointer">Toimittaja/Asiakas</th>
                        <th onClick="lajittele(6)" class="pointer">Määrä</th>
                        <th onClick="lajittele(7)" class="pointer">Eräpäivä</th>
                        <th onClick="lajittele(8)" class="pointer">Selite</th>
                    </tr>';

                    for ($i = 0; $i < count($rivit); $i++) {
                        if (isset($rivit[$i]['Expenditure_ID'])) {
                            $kuka = "Expenditure_Supplier";
                            $mika = "Expenditure_Account";
                            $ots = "Expenditure_Header";
                            $tyyppi = 1;
                        }
                        else {
                            $kuka = "Customer_ID";
                            $mika = "Income_Account";
                            $ots = "Invoice_Header";
                            $tyyppi = 2;
                        }
                        echo    "<tr onClick='muokkaa(" . $rivit[$i]['Voucher_No'] . ", " . $tyyppi . ")' class='pointer'>
                                    <td>" . $rivit[$i]['Voucher_No'] . "</td>
                                    <td>" . $rivit[$i]['Entry_Date'] . "</td>
                                    <td>" . $rivit[$i][$ots] . "</td>
                                    <td>" . $rivit[$i]['Form_Type'] . "</td>
                                    <td>" . $rivit[$i][$mika] . " - " . $rivit[$i]['AccountName'] . "</td>
                                    <td>" . $rivit[$i][$kuka] . "</td>
                                    <td>" . $rivit[$i]['Amount'] . "</td>
                                    <td>" . $rivit[$i]['Due_Date'] . "</td>
                                    <td>" . $rivit[$i]['Line_Statement'] . "</td>
                                </tr>"; 
                    }
                echo "</table>";
            }
        ?>

    </div>
</div> <!-- Sivukohtainen html/php-sisältö päättyy tähän -->

<?php include("messagesInstructionsEndTags.php"); ?>

       
      


