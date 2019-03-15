<?php
/**
 * Autor: Adrian Zurbrügg
 * List of all servers. The server are divided into 2 tables. Windows & Linux.
 * To each server a small overview of the information is available (ping, hostname, administrator, os, availability class and description)
 */

include('connection.php');
session_start();

// If no username found in session, redirect to the home page.
if (!isset($_SESSION["username"])) {
    header("location: index.php");
}

// Select required information for windows systems. MAX(id) is used to limit the results to the latest entry.
$statementWindows = $pdo->prepare("SELECT hostInfo.*, availabilityClass.type, availabilityInfo.ID as a_id, availabilityInfo.ping, availabilityInfo.cDate, basicInfo.operatingSystem FROM hostInfo JOIN availabilityClass on hostInfo.availabilityClass_id = availabilityClass.ID JOIN availabilityInfo on hostInfo.ID = availabilityInfo.hostInfo_id JOIN basicInfo on hostInfo.ID = basicInfo.hostInfo_ID WHERE hostSystem = 'Windows' AND availabilityInfo.ID IN (SELECT MAX(id) FROM availabilityInfo GROUP BY hostInfo_id)");
$statementWindows->execute();
$resultWindows = $statementWindows->fetchAll();

// Select required information for linux systems. MAX(id) is used to limit the results to the latest entry.
$statementLinux = $pdo->prepare("SELECT hostInfo.*, availabilityClass.type, availabilityInfo.ID as a_id, availabilityInfo.ping, availabilityInfo.cDate, basicInfo.operatingSystem FROM hostInfo JOIN availabilityClass on hostInfo.availabilityClass_id = availabilityClass.ID JOIN availabilityInfo on hostInfo.ID = availabilityInfo.hostInfo_id JOIN basicInfo on hostInfo.ID = basicInfo.hostInfo_ID WHERE hostSystem = 'Linux' AND availabilityInfo.ID IN (SELECT MAX(id) FROM availabilityInfo GROUP BY hostInfo_id)");
$statementLinux->execute();
$resultLinux = $statementLinux->fetchAll();


?>

<html lang="de">
<head>
    <title>test</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta charset="utf-8">
    <link type="text/css" rel="stylesheet" href="css/materialize.min.css"/>
    <link type="text/css" rel="stylesheet" href="css/materialize.css"/>
    <link type="text/css" rel="stylesheet" href="fonts/material-icons.css"/>
    <link href="css/style.css" rel="stylesheet" type="text/css">

    <script src='js/jquery-ui-1.12.1.custom/external/jquery/jquery.js' type='text/javascript'></script>
    <script src='js/jquery-ui-1.12.1.custom/jquery-ui.min.js' type='text/javascript'></script>
    <script src='js/materialize.js' type='text/javascript'></script>
    <script src='js/materialize.min.js' type='text/javascript'></script>
</head>
<body>
<!-- START: Navigation ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<ul id="slide-out" class="sidenav sidenav-fixed">
    <li>
        <div class="user-view">
            <div class="background">
                <img src="images/IT-Logo.PNG">
            </div>
        </div>
    </li>
    <!-- Show username and a logout option if user is logged in -->
    <?php if (isset($_SESSION["username"])) {
        $sessionuser = $_SESSION['username']; ?>
        <p><span class="black-text name nav-text">Angemeldet als: <?php echo $sessionuser; ?></span></p>
        <li><a href="logout.php">Abmelden</a></li>
        <div class="divider"></div>
        <?php
    } ?>
    <li><a href="index.php">Home</a></li>
    <li><a href="serverList.php">Server Liste</a></li>
    <div class="divider"></div>
    <div class="credits">
        <p>Made by Adrian Zurbrügg <b>|</b> IPA-Projekt 2019</p>
    </div>
</ul>
<!-- END: Navigation ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------>

<!-- START: Serverlist ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<div class="content_serverList">
    <!-- Windows Server -->
    <div class="row">
        <h4 class="col m9">Windows Systeme:</h4>
        <div class="input-field col">
            <i class="material-icons prefix">search</i>
            <input id="search_windows" type="text" onkeyup="search('tbl_serverListWindows', 'search_windows')">
            <label for="search_windows">Suche</label>
        </div>
        <div class="col">
            <div class="table-wrapper z-depth-2">
                <div class="table-scroll">
                    <table id="tbl_serverListWindows" class="highlight tbl_serverList">
                        <tr>
                            <th width="10%">Erreichbarkeit:</th>
                            <th width="10%">Hostname:</th>
                            <th width="15%">Systembetreuer:</th>
                            <th width="15%">Betriebssystem:</th>
                            <th width="15%">Verfügbarkeitsklasse:</th>
                            <th width="35%">Beschreibung:</th>
                        </tr>
                        <?php foreach ($resultWindows as $row) { ?>
                            <tr>
                                <td><span class="dot"><?php echo $row['ping']; ?></span></td>
                                <td>
                                    <!-- Pass the hostInfo id as an url parameter -->
                                    <a href="details.php?id=<?php echo $row['ID']; ?>"><?php echo $row['hostname']; ?></a>
                                </td>
                                <td><?php echo $row['systemAdministrator']; ?></td>
                                <td><?php echo $row['operatingSystem']; ?></td>
                                <td><?php echo $row['type']; ?></td>
                                <td><?php echo $row['description']; ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Linux Server -->
    <div class="row">
        <h4 class="col m9">Linux Systeme:</h4>
        <div class="input-field col">
            <i class="material-icons prefix">search</i>
            <input id="search_linux" type="text" onkeyup="search('tbl_serverListLinux', 'search_linux')">
            <label for="search_linux">Suche</label>
        </div>
        <div class="col">
            <div class="table-wrapper z-depth-2">
                <div class="table-scroll">
                    <table id="tbl_serverListLinux" class="highlight tbl_serverList">
                        <tr>
                            <th width="10%">Erreichbarkeit:</th>
                            <th width="10%">Hostname:</th>
                            <th width="15%">Systembetreuer:</th>
                            <th width="15%">Betriebssystem:</th>
                            <th width="15%">Verfügbarkeitsklasse:</th>
                            <th width="35%">Beschreibung:</th>
                        </tr>
                        <?php foreach ($resultLinux as $row) { ?>
                            <tr>
                                <td><span class="dot"><?php echo $row['ping']; ?></span></td>
                                <td>
                                    <!-- Pass the hostInfo id as an url parameter -->
                                    <a href="details.php?id=<?php echo $row['ID']; ?>"><?php echo $row['hostname']; ?></a>
                                </td>
                                <td><?php echo $row['systemAdministrator']; ?></td>
                                <td><?php echo $row['operatingSystem']; ?></td>
                                <td><?php echo $row['type']; ?></td>
                                <td><?php echo $row['description']; ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Serverlist ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------>

<script type="application/x-javascript">
    $(document).ready(function () {
        // Initialize navbar
        $('.sidenav').sidenav();

        // This makes he whole table row clickable
        $('#tbl_serverListWindows tr, #tbl_serverListLinux tr').click(function() {
            var href = $(this).find("a").attr("href");
            if(href) {
                window.location = href;
            }
        });
    });

    // Sets the color for the ping status dot
    var ping = document.getElementsByClassName('dot');
    var i;
    for (i = 0; i < ping.length; i++){
        if (ping[i].innerHTML === '2') {
            ping[i].innerHTML = '';
            ping[i].style.backgroundColor = 'orange';
        } else if (ping[i].innerHTML === '1') {
            ping[i].innerHTML = '';
            ping[i].style.backgroundColor = 'green';
        } else if (ping[i].innerHTML === '0') {
            ping[i].innerHTML = '';
            ping[i].style.backgroundColor = 'red';
        }
    }

    // Function which searches in the specified table.
    // Parameters:
    // tableId = id of the table (string)
    // inputId = id of the input field where the cellContent is written in. (string)
    function search(tableId, inputId) {
        var input, filter, table, tr, td, td1, td2, td3, td4, td5, i, cleanedFilter;
        input = document.getElementById(inputId);
        filter = input.value.toUpperCase();
        table = document.getElementById(tableId);
        tr = table.getElementsByTagName("tr");

        cleanedFilter = filter

        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[0];
            td1 = tr[i].getElementsByTagName("td")[1];
            td2 = tr[i].getElementsByTagName("td")[2];
            td3 = tr[i].getElementsByTagName("td")[3];
            td4 = tr[i].getElementsByTagName("td")[4];
            td5 = tr[i].getElementsByTagName("td")[5];

            if (td) {
                cellContent = td.innerHTML.toUpperCase()
                cellContent1 = td1.innerHTML.toUpperCase()
                cellContent2 = td2.innerHTML.toUpperCase()
                cellContent3 = td3.innerHTML.toUpperCase()
                cellContent4 = td4.innerHTML.toUpperCase()
                cellContent5 = td5.innerHTML.toUpperCase()

                if (cellContent.indexOf(cleanedFilter) > -1 || cellContent1.indexOf(cleanedFilter) > -1 || cellContent2.indexOf(cleanedFilter) > -1 || cellContent3.indexOf(cleanedFilter) > -1 || cellContent4.indexOf(cleanedFilter) > -1 || cellContent5.indexOf(cleanedFilter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }


</script>
</body>
</html>
