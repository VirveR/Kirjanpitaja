<!-- Design Factory Project -kurssin työ Kirjanpitäjä, Virve Rajasärkkä HAMK 2023
     Tositteen muokkaus ja poisto -->

<!-- Valmistelevat toimet -->
<?php
    include_once("../settings.php"); include("sanitation.php"); session_start();
    $nro = numbers($_GET['nro']); $tyyppi = numbers($_GET['tyyppi']); $org = cleanBusinessId($_SESSION['organization_id']);

    # tietokannan kentät
    $tknro = 'Voucher_No'; $tktyyppi = 'Form_Type'; $tkpvm = 'Entry_Date'; $tkmakta = 'Payment_Method';
    $tklnro = 'Invoice_No'; $tklpvm = 'Invoice_Date'; $tkepvm = 'Due_Date'; $tkviite = 'Reference_No';
    $tksumma = 'Amount'; $tkalv = 'Taxrate'; $tkalvs = 'VAT'; $tkalviton = 'Exclusive_Of_Tax';
    $tkjalk = 'Periodization_Start'; $tkjlop = 'Periodization_End'; $tksel = 'Line_Statement';
    if ($tyyppi == 1) { $tkotsikko = 'Expenditure_Header'; $tkyht = 'Expenditure_Supplier'; $tktili = 'Expenditure_Account'; }
    else { $tkotsikko = 'Invoice_Header'; $tkyht = 'Customer_ID'; $tktili = 'Income_Account'; }
    
    # etsitään menotosite
    if ($tyyppi == 1) {
        $sql = "SELECT ExpenditureVoucher.*, AccountList.AccountName 
                FROM ExpenditureVoucher 
                INNER JOIN AccountList ON Expenditure_Account = AccountNumber_ID
                WHERE Organization_ID = ? AND Voucher_No = ?;";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$org, $nro]);
        $rivi = $stmt->fetch();
        $pdo->connection = null;
    }

    # tai etsitään tulotosite
    else if ($tyyppi == 2) {
        $sql = "SELECT IncomeVoucher.*, AccountList.AccountName 
                FROM IncomeVoucher 
                INNER JOIN AccountList ON Income_Account = AccountNumber_ID
                WHERE Organization_ID = ? AND Voucher_No = ?;";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$org, $nro]);
        $rivi = $stmt->fetch();
        $pdo->connection = null;
    }

    # pitäisi löytyä, mutta jos ei löydy, virhe 
    else { echo '<script>alert("Jotain meni pieleen. :("; window.close();</script>'; }
?>

<!-- Html-sisältö alkaa -->

<!DOCTYPE html>
<html lang="fi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/main.css">
        <link rel="stylesheet" href="css/selaa.css">
        <script src="selaa.js"></script>
    </head>
    <body>

    <!-- Esitäytetty muokkauslomake -->
        <div id="muokkaaTosite">
            <h3>Tosite nro <?php echo $nro; ?></h3>
            <form method="post" id="muokattavaTosite">

            <!-- Tositteen otsikkorivi: tyyppi, pvm, tili ja otsikko -->
                <div class="selaaOsio">
                    <label class="tasa">Otsikko<b style="color:red;"> *</b></label><input type="text" name="otsikko" value="<?php echo $rivi[$tkotsikko]; ?>" class="pointer pitka" required>
                </div>
                <div class="selaaOsio">
                    <label class="tasa">Kirjattu<b style="color:red;"> *</b></label><input type="date" name="pvm" value="<?php echo $rivi[$tkpvm]; ?>" class="pointer tasa" required>
                    <label class="tasa">Tilille<b style="color:red;"> *</b></label><select name="tili" class="pointer tasa" required>
                        <?php if ($tyyppi == 1) { echo '  
                                <option value="4000"'; if ($rivi[$tktili] == 4000) { echo " selected"; } echo '>4000 Ostot</option>
                                <option value="4010"'; if ($rivi[$tktili] == 4010) { echo " selected"; } echo '>4010 Tavaratuonti Ahvenanmaalta</option>
                                <option value="4020"'; if ($rivi[$tktili] == 4020) { echo " selected"; } echo '>4020 Yhteisötuonti</option>
                                <option value="4030"'; if ($rivi[$tktili] == 4030) { echo " selected"; } echo '>4030 Tuonti yhteisön ulkopuolelta</option>
                                <option value="4040"'; if ($rivi[$tktili] == 4040) { echo " selected"; } echo '>4040 Käytetyn tavaran ja antiikin osto</option>
                                <option value="4050"'; if ($rivi[$tktili] == 4050) { echo " selected"; } echo '>4050 Ostojen alennukset</option>
                                <option value="4070"'; if ($rivi[$tktili] == 4070) { echo " selected"; } echo '>4070 Palautetut tavarat</option>'; } 
                            else { echo '
                                <option value="3000"'; if ($rivi[$tktili] == 3000) { echo " selected"; } echo '>3000 Myynnit</option>
                                <option value="3010"'; if ($rivi[$tktili] == 3010) { echo " selected"; } echo '>3010 Tavaravienti Ahvenanmaalle</option>
                                <option value="3020"'; if ($rivi[$tktili] == 3020) { echo " selected"; } echo '>3020 Yhteisövienti</option>
                                <option value="3030"'; if ($rivi[$tktili] == 3030) { echo " selected"; } echo '>3030 Vienti yhteisön ulkopuolelle</option>
                                <option value="3040"'; if ($rivi[$tktili] == 3040) { echo " selected"; } echo '>3040 Käytetyn tavaran ja antiikin myynti</option>
                                <option value="3050"'; if ($rivi[$tktili] == 3050) { echo " selected"; } echo '>3050 Myyntien alennukset</option>
                                <option value="3070"'; if ($rivi[$tktili] == 3070) { echo " selected"; } echo '>3070 Palautetut tavarat</option>'; } ?></select>                    
                </div>

            <!-- Toimittaja/asiakas -->
                <div class="selaaOsio">
                    <label class="tasa"> <?php if ($tyyppi == 1) { echo "Toimittaja"; } else { echo "Asiakas"; } ?> </label><input type="text" name="yhteys" value="<?php echo $rivi[$tkyht]; ?>" class="pointer tasa">
                    <label class="tasa">Maksutapa<b style="color:red"> *</b></label><select name="maksutapa" class="pointer tasa" required>
                        <option value="Pankkitili" <?php if ($rivi[$tkmakta] == "Pankkitili") { echo "selected"; } ?>>Pankkitili</option>
                        <option value="Käteinen" <?php if ($rivi[$tkmakta] == "Käteinen") { echo "selected"; } ?>>Käteinen</option>
                        <option value="Luottokortti" <?php if ($rivi[$tkmakta] == "Luottokortti") { echo "selected"; } ?>>Luottokortti</option>
                        <option value="Hyvityslasku" <?php if ($rivi[$tkmakta] == "Hyvityslasku") { echo "selected"; } ?>>Hyvityslasku</option>
                        <option value="Siirtovelka" <?php if ($rivi[$tkmakta] == "Siirtovelka") { echo "selected"; } ?>>Siirtovelka</option></select>
                </div>

            <!-- Laskun päivämäärät -->
                <div class="selaaOsio">
                    <label class="tasa">Laskun pvm</label><input type="date" name="laskupvm" value="<?php echo $rivi[$tklpvm]; ?>" class="pointer tasa">
                    <label class="tasa">Eräpäivä</label><input type="date" name="erapvm" value="<?php echo $rivi[$tkepvm]; ?>" class="pointer tasa">
                </div>

            <!-- Laskun numerotiedot -->
                <div class="selaaOsio">
                    <label class="tasa">Laskun numero</label><input type="number" name="laskunro" value="<?php echo $rivi['Invoice_No']; ?>" class="pointer tasa">
                    <label class="tasa">Viitenumero</label><input type="text" name="viite" value="<?php echo $rivi[$tkviite]; ?>" class="pointer tasa">
                </div>

            <!-- Laskun rahatiedot -->
                <div class="selaaOsio">
                    <label class="tasa">Määrä<b style="color:red"> *</b></label><input id="alvillinen" onchange="vaihdaAlviton()" type="number" step="0.01" name="summa" value="<?php echo $rivi[$tksumma]; ?>" class="pointer tasa" required>
                    <label class="tasa">Veroton</label><input id="alviton" type="number" name="veroton" value="<?php echo $rivi[$tkalviton]; ?>" class="tasa" readonly>
                </div>

            <!-- Laskun ALV-tiedot --> 
                <div class="selaaOsio">
                    <label class="tasa">ALV<b style="color:red"> *</b></label><select id="alv" onchange="vaihdaAlviton()" name="alv" class="pointer tasa" required>
                        <option value="24" <?php if ($rivi[$tkalv] == 24) { echo " selected"; } ?>>24 %</option>
                        <option value="14" <?php if ($rivi[$tkalv] == 14) { echo " selected"; } ?>>14 %</option>
                        <option value="10" <?php if ($rivi[$tkalv] == 10) { echo " selected"; } ?>>10 %</option>
                        <option value="0" <?php if ($rivi[$tkalv] == 0) { echo " selected"; } ?>>0 %</option></select>
                    <label class="tasa">Tilille<b style="color:red"> *</b></label><select name="alvsel" class="pointer tasa" required>
                    <?php 
                        if ($tyyppi == 1) { echo '
                            <option value="Verollinen osto (netto)"'; if ($rivi[$tkalvs] == "Verollinen osto (netto)") { echo " selected"; } echo '>Verollinen osto (netto)</option>
                            <option value="Verollinen osto (brutto)"'; if ($rivi[$tkalvs] == "Verollinen osto (brutto)") { echo " selected"; } echo '>Verollinen osto (brutto)</option>
                            <option value="Voittomarginaalijärjestelmä, osto"'; if ($rivi[$tkalvs] == "Voittomarginaalijärjestelmä, osto") { echo " selected"; } echo '>Voittomarginaalijärjestelmä, osto</option>
                            <option value="Tavaroiden yhteisöhankinnat"'; if ($rivi[$tkalvs] == "Tavaroiden yhteisöhankinnat") { echo " selected"; } echo '>Tavaroiden yhteisöhankinnat</option>
                            <option value="Palveluiden yhteisöhankinnat"'; if ($rivi[$tkalvs] == "Palveluiden yhteisöhankinnat") { echo " selected"; } echo '>Palveluiden yhteisöhankinnat</option>
                            <option value="Tavaroiden maahantuonti, veron kirjaus"'; if ($rivi[$tkalvs] == "Tavaroiden maahantuonti, veron kirjaus") { echo " selected"; } echo '>Tavaroiden maahantuonti, veron kirjaus</option>
                            <option value="Palveluostot EU:n ulkopuolelta"'; if ($rivi[$tkalvs] == "Palveluostot EU:n ulkopuolelta") { echo " selected"; } echo '>Palveluostot EU:n ulkopuolelta</option>
                            <option value="Rakennuspalveluiden ostot"'; if ($rivi[$tkalvs] == "Rakennuspalveluiden ostot") { echo " selected"; } echo '>Rakennuspalveluiden ostot</option>
                        </select>'; }
                        else { echo '
                            <option value="Verollinen myynti (netto)"'; if ($rivi[$tkalvs] == "Verollinen myynti (netto)") { echo " selected"; } echo '>Verollinen myynti (netto)</option>
                            <option value="Verollinen myynti (brutto)"'; if ($rivi[$tkalvs] == "Verollinen myynti (brutto)") { echo " selected"; } echo '>Verollinen myynti (brutto)</option>
                            <option value="Nollaverokannan alainen myynti"'; if ($rivi[$tkalvs] == "Nollaverokannan alainen myynti") { echo " selected"; } echo '>Nollaverokannan alainen myynti</option>
                            <option value="Voittomarginaalijärjestelmä, myynti"'; if ($rivi[$tkalvs] == "Voittomarginaalijärjestelmä, myynti") { echo " selected"; } echo '>Voittomarginaalijärjestelmä, myynti</option>
                            <option value="Tavaroiden yhteisömyynti"'; if ($rivi[$tkalvs] == "Tavaroiden yhteisömyynti") { echo " selected"; } echo '>Tavaroiden yhteisömyynti</option>
                            <option value="Palveluiden yhteisömyynti"'; if ($rivi[$tkalvs] == "Palveluiden yhteisömyynti") { echo " selected"; } echo '>Palveluiden yhteisömyynti</option>
                            <option value="Rakennuspalveluiden myynti"'; if ($rivi[$tkalvs] == "Rakennuspalveluiden myynti") { echo " selected"; } echo '>Rakennuspalveluiden myynti</option>
                        </select>';
                        }
                    ?>
                </div>

            <!-- Jaksotus -->
                <div class="selaaOsio">
                    <label class="tasa">Jaksotuksen alkupvm</label><input type="date" name="jaksotusalk" value="<?php echo $rivi[$tkjalk]; ?>" class="pointer tasa">
                    <label class="tasa">Jaksotuksen loppupvm</label><input type="date" name="jaksotuslop" value="<?php echo $rivi[$tkjlop]; ?>" class="pointer tasa">
                </div>
                
            <!-- Selite -->
                <div class="selaaOsio">
                    <label class="tasa">Selite</label><input type="text" name="selite" value="<?php echo $rivi[$tksel]; ?>" class="pointer pitka">    
                </div>

            <!-- Lomakkeen lähetys -->
                <input type="submit" name="tallenna" value="Tallenna" class="selaaNappi">
                <input type="submit" name="peruuta" value="Peruuta" class="selaaNappi">
                <input type="submit" name="poista" value="Poista tämä tosite" class="poistaNappi" onclick="return confirm('Haluatko varmasti poistaa tämän tositteen?')">
            </form>
        </div>

<!-- Lomakkeen käsittely -->    

        <?php
            # jos perutaan, ei tallenneta
            if (isset($_POST['peruuta'])) { unset($_POST); echo '<script>window.close();</script>'; }

            # poistetaan haluttu tosite
            else if (isset($_POST['poista'])) {
                if ($tyyppi == 1) {
                    $sql = "DELETE FROM ExpenditureVoucher 
                            WHERE Voucher_No = ? AND Organization_ID = ?;";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$nro, $org]);
                    $pdo->connection = null;
                }
                else {
                    $sql = "DELETE FROM IncomeVoucher 
                            WHERE Voucher_No = ? AND Organization_ID = ?;";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$nro, $org]);
                    $pdo->connection = null;
                }
                unset($_POST);
                echo '<script>window.close(); window.opener.location.reload();</script>';
            }

            # haetaan muokatut tiedot postista
            else if (isset($_POST['tallenna'])) {
                $muokattu[0] = numbers($_POST['pvm']);
                if (isset($_POST['otsikko'])) { $muokattu[1] = $_POST['otsikko']; } else { $muokattu[1] = NULL; }
                $muokattu[2] = lettersNumbers($_POST['maksutapa']);
                if (isset($_POST['yhteys'])) { $muokattu[3] = lettersNumbers($_POST['yhteys']); } else { $muokattu[3] = NULL; }
                if (isset($_POST['laskunro']) && ($_POST['laskunro'] != '')) { $muokattu[4] = numbers($_POST['laskunro']); } else { $muokattu[4] = NULL; }
                if (isset($_POST['laskupvm']) && ($_POST['laskupvm'] != '')) { $muokattu[5] = numbers($_POST['laskupvm']); } else { $muokattu[5] = NULL; }
                if (isset($_POST['erapvm']) && ($_POST['erapvm'] != '')) { $muokattu[6] = numbers($_POST['erapvm']); } else { $muokattu[6] = NULL; }
                if (isset($_POST['viite']) && ($_POST['viite'] != '')) { $muokattu[7] = numbers($_POST['viite']); } else { $muokattu[7] = NULL; }
                $muokattu[8] = numbers($_POST['tili']);
                $muokattu[9] = numbersFloat($_POST['summa']);
                $muokattu[10] = numbersFloat($_POST['veroton']);
                $muokattu[11] = numbers($_POST['alv']);
                $muokattu[12] = lettersNumbers($_POST['alvsel']);
                if (isset($_POST['jaksotusalk']) && ($_POST['jaksotusalk'] != '')) { $muokattu[13] = numbers($_POST['jaksotusalk']); } else { $muokattu[13] = NULL; }
                if (isset($_POST['jaksotuslop']) && ($_POST['jaksotuslop'] != '')) { $muokattu[14] = numbers($_POST['jaksotuslop']); } else { $muokattu[14] = NULL; }
                if (isset($_POST['selite'])) { $muokattu[15] = lettersNumbers($_POST['selite']); } else { $muokattu[15] = NULL; }
                $muokattu[16] = numbers($nro);
                $muokattu[17] = cleanBusinessId($org);

                # tallennetaan muokattu rivi tietokantaan
                if ($tyyppi == 1) {
                    $sql = "UPDATE ExpenditureVoucher 
                            SET Entry_Date = ?, Expenditure_Header = ?, Payment_Method = ?, Expenditure_Supplier = ?, Invoice_No = ?, Invoice_Date = ?, Due_Date = ?, Reference_No = ?, 
                                Expenditure_Account = ?, Amount = ?, Exclusive_Of_Tax = ?, Taxrate = ?, VAT = ?, Periodization_Start = ?, Periodization_End = ?, Line_Statement = ?
                            WHERE Voucher_No = ? AND Organization_ID = ?;";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($muokattu);
                    $pdo->connection = null;
                }
                else {
                    $sql = "UPDATE IncomeVoucher 
                            SET Entry_Date = ?, Invoice_Header = ?, Payment_Method = ?, Customer_ID = ?, Invoice_No = ?, Invoice_Date = ?, Due_Date = ?, Reference_No = ?, 
                                Income_Account = ?, Amount = ?, Exclusive_Of_Tax = ?, Taxrate = ?, VAT = ?, Periodization_Start = ?, Periodization_End = ?, Line_Statement = ?
                            WHERE Voucher_No = ? AND Organization_ID = ?;";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($muokattu);
                    $pdo->connection = null;
                }

                unset($_POST);
                echo '<script>alert("Tosite muokattu."); window.close(); window.opener.location.reload();</script>';
            }
        ?>
        
    </body>
</html>