// Design Factory Project -kurssin työ Kirjanpitäjä, Virve Rajasärkkä HAMK 2023

// selaa-sivun skriptit

//lajittelee taulukon valitun otsikon perusteella
function lajittele(n){
    var taulukko, rivit, vaihdetaan, i, x, y, vaihtoon, suunta, vaihdot = 0;
    taulukko = document.getElementById("tulokset");
    vaihdetaan = true;
    suunta = "laskeva";
    while (vaihdetaan) {
        vaihdetaan = false;
        rivit = taulukko.rows;
        for (i = 1; i < (rivit.length - 1); i++) {
            vaihtoon = false;
            x = rivit[i].getElementsByTagName("TD")[n];
            y = rivit[i + 1].getElementsByTagName("TD")[n];
            if (suunta == "laskeva") {
                if (n == 0 || n == 6) {
                    if (Number(x.innerHTML) > Number(y.innerHTML)) {
                        vaihtoon = true;
                        break;
                    }
                }
                else {
                    if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                        vaihtoon = true;
                        break;
                    }
                }                    
            }
            else if (suunta == "nouseva") {
                if (n == 0 || n == 6) {
                    if (Number(x.innerHTML) < Number(y.innerHTML)) {
                        vaihtoon = true;
                        break;
                    }
                }
                else {
                    if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                        vaihtoon = true;
                        break;
                    }
                }
            }
        }
        if (vaihtoon) {
            rivit[i].parentNode.insertBefore(rivit[i + 1], rivit[i]);
            vaihdetaan = true;
            vaihdot++;
        }
        else {
            if (vaihdot == 0 && suunta == "laskeva") {
                suunta = "nouseva";
                vaihdetaan = true;
            }
        }
    }
}

// lähettää valitun tositteen muokattavaksi
function muokkaa(nro, tyyppi) {
    params = "scrollbars=yes,resizable=yes,status=no,location=no,toolbar=no,menubar=no";
    window.open("muokkaaTosite.php?nro=" + nro + "&tyyppi=" + tyyppi, "win2", params);
}

// muokkaaTosite-sivun skriptit

// päivittää alvittoman summan muokkauslomakkeelle
function vaihdaAlviton() {
    var alvillinen = document.getElementById("alvillinen").value;
    var alv = document.getElementById("alv").value;
    var alviton = alvillinen / (100 + alv) / 100;
    alviton = alviton.toFixed(2);
    document.getElementById("alviton").setAttribute("value", alviton);
}