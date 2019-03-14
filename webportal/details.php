<?php
/**
 * Autor: Adrian Zurbrügg
 * Detail view of each server. Here are the 4 main informations (host, basic, performance and availability)
 * Each main part includes several information about the specific topic.
 */

include('connection.php');
session_start();

// If no username found in session, redirect to the home page.
if (!isset($_SESSION["username"])) {
    header("location: index.php");
}

// If there's no id set as a url parameter, the user gets redirected to the server list.
if (!isset($_GET['id'])) {
    header('Location: serverList.php');
}

// Get parameter id from url
$id = $_GET['id'];

// This block of code checks if the button, to save the edits for host information, is clicked and updates the table with the new user input.
if (isset($_POST['btn_editHostInfo'])) {
    $input_administrator = htmlspecialchars($_POST['input_administrator']);
    $input_additionalDNS = htmlspecialchars($_POST['input_additionalDNS']);
    $dd_availabilityClass = htmlspecialchars($_POST['dd_availabilityClass']);
    $input_description = htmlspecialchars($_POST['input_description']);
    $input_note = htmlspecialchars($_POST['input_Note']);
    $updateAvailabilityClass = $pdo->prepare("UPDATE hostInfo SET systemAdministrator = '$input_administrator', description = '$input_description', availabilityClass_id = '$dd_availabilityClass', additionalDNS = '$input_additionalDNS', notes = '$input_note' WHERE ID = '$id'");
    $updateAvailabilityClass->execute();
}

// Function which splits arrays by whitespace. Parameter: array to split. Return value: array
function splitByWhitespace($arrayToSplit){
    $arrayToSplit = explode(' ', $arrayToSplit);
    return $arrayToSplit;
}

/** START: Host information ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
// Select host information and save it to $resultHostInfo variable */
$statementHostInfo = $pdo->prepare("SELECT hostInfo.*, availabilityClass.type FROM hostInfo JOIN availabilityClass on hostInfo.availabilityClass_id = availabilityClass.ID WHERE hostInfo.ID = $id");
$statementHostInfo->execute();
$resultHostInfo = $statementHostInfo->fetchAll();
/** END: Host information ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/

/** START: Basic information ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
// Select basic information and save it to $resultBasicInfo variable
$statementBasicInfo = $pdo->prepare("SELECT * FROM basicInfo WHERE hostInfo_id = $id");
$statementBasicInfo->execute();
$resultBasicInfo = $statementBasicInfo->fetchAll();
// Iterate over the result and split dnsServer, uptime and volumes by whitespace. This is done to display the values later in a clear format.
foreach ($resultBasicInfo as $row) {
    $dnsServer = $row['dnsServer'];
    $dnsServerSplitted = splitByWhitespace($dnsServer);

    $uptime = $row['uptime'];
    $uptimeSplitted = splitByWhitespace($uptime);

    $volumes = $row['volumes'];
    $volumesSplitted = splitByWhitespace($volumes);
}
/** END: Basic information -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/

/** START: Availability --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
// Select availabilityClass information.
$statementAvailabilityClasses = $pdo->prepare("SELECT * FROM availabilityClass");
$statementAvailabilityClasses->execute();
$resultAvailabilityClasses = $statementAvailabilityClasses->fetchAll();
/** END: Availability  ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/

/** START: Perfomance info -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
/** Chart performance Y-Axis values -----------------------------------------------------*/
// Select performance information from the last 31 days. (Those are x-axis values for the chart)
$statementPerformanceInfoMonthY = $pdo->prepare("SELECT cDate, diskUsage, cpuUsage, ramUsage FROM performanceInfo A INNER JOIN (SELECT MAX(cDate) as maxDate FROM performanceInfo WHERE hostInfo_id = $id AND cDate >= date_sub(NOW(), INTERVAL 31 day) GROUP BY CAST(cDate as DATE), DAY(cDate)) AS B ON A.cDate = B.maxDate WHERE hostInfo_id = $id ORDER BY cDate ASC");
$statementPerformanceInfoMonthY->execute();
$resultPerformanceInfoMonthY = $statementPerformanceInfoMonthY->fetchAll();

// Select performance information from the last day. (Those are x-axis values for the chart)
$statementPerformanceInfoDayY = $pdo->prepare("SELECT cDate, diskUsage, cpuUsage, ramUsage FROM performanceInfo A INNER JOIN (SELECT MAX(cDate) as maxDate FROM performanceInfo WHERE hostInfo_id = $id AND cDate >= date_sub(NOW(), INTERVAL 1 DAY) GROUP BY CAST(cDate as DATE), HOUR(cDate)) AS B ON A.cDate = B.maxDate ORDER BY cDate ASC");
$statementPerformanceInfoDayY->execute();
$resultPerformanceInfoDayY = $statementPerformanceInfoDayY->fetchAll();

// Select performance information from the last hour. (Those are x-axis values for the chart)
$statementPerformanceInfoHourY = $pdo->prepare("SELECT cDate, diskUsage, cpuUsage, ramUsage FROM performanceInfo WHERE hostInfo_id = $id AND cDate >= date_sub(NOW(), INTERVAL 1 hour) ORDER BY cDate ASC");
$statementPerformanceInfoHourY->execute();
$resultPerformanceInfoHourY = $statementPerformanceInfoHourY->fetchAll();

// Iterate over the 3 results and declare arrays for each attribute. (Those are x-axis values for the chart)
foreach ($resultPerformanceInfoMonthY as $row) {
    $diskUsages = $row['diskUsage'];
    $diskUsagesArrayMonthY[] = $diskUsages;

    $cpuUsages = $row['cpuUsage'];
    $cpuUsagesArrayMonthY[] = $cpuUsages;

    $ramUsages = $row['ramUsage'];
    $ramUsagesArrayMonthY[] = $ramUsages;
}

foreach ($resultPerformanceInfoDayY as $row) {
    $diskUsages = $row['diskUsage'];
    $diskUsagesArrayDayY[] = $diskUsages;

    $cpuUsages = $row['cpuUsage'];
    $cpuUsagesArrayDayY[] = $cpuUsages;

    $ramUsages = $row['ramUsage'];
    $ramUsagesArrayDayY[] = $ramUsages;
}

foreach ($resultPerformanceInfoHourY as $row) {
    $diskUsages = $row['diskUsage'];
    $diskUsagesArrayHourY[] = $diskUsages;

    $cpuUsages = $row['cpuUsage'];
    $cpuUsagesArrayHourY[] = $cpuUsages;

    $ramUsages = $row['ramUsage'];
    $ramUsagesArrayHourY[] = $ramUsages;
}

// Takes one of the three diskUsagesArrays as a parameter, rearranges the elements inside the array and saves it to the jsonDataY variable.
// This is required because the y-axis dataset for the chartjs requires a specific format to work properly.
// Example of how the jsonDataY looks like: "{"L":[{"y":"100"},{"y":"50"},{"y":"40"}],"C":[{"y":"61"},{"y":"39"},{"y":"59"}],"":[{"y":"31"},{"y":"97"},{"y":"67"}]}"
// Parameter: Array of diskUsages. Return value: json formatted array
function arrangeJsonData($diskUsageTime){
    $diskUsages = $diskUsageTime;
    $jsonDataY = array();
    foreach($diskUsages as $key => $element){
        $parts = explode(" ",$element);
        foreach($parts as $part){
            $underParts = explode(',', $part);
            if(isset($underParts[0]) && isset($underParts[1]) ){
                $jsonDataY[$underParts[0]][]=array('y'=>$underParts[1]);
            }
        }
    }
    return $jsonDataY;
}

// Declare and fill the variables for the 3 periods of time.
$diskUsagesArrayMonthY = arrangeJsonData($diskUsagesArrayMonthY);
$diskUsagesArrayDayY = arrangeJsonData($diskUsagesArrayDayY);
$diskUsagesArrayHourY = arrangeJsonData($diskUsagesArrayHourY);
/**--------------------------------------------------------------------*/
/** Chart performance X-Axis values -----------------------------------------------------*/
// Select performance information from the last 31 days. (Those are y-axis values for the chart)
$statementPerformanceInfoMonthX = $pdo->prepare("SELECT MAX(cDate) as cDate FROM performanceInfo WHERE hostInfo_id = $id AND cDate >= date_sub(NOW(), INTERVAL 31 day) GROUP BY CAST(cDate as DATE), DAY(cDate) ORDER BY cDate ASC");
$statementPerformanceInfoMonthX->execute();
$resultPerformanceInfoMonthX = $statementPerformanceInfoMonthX->fetchAll();

// Select performance information from the last day. (Those are y-axis values for the chart)
$statementPerformanceInfoDayX = $pdo->prepare("SELECT MAX(cDate) as cDate FROM performanceInfo WHERE hostInfo_id = $id AND cDate >= date_sub(NOW(), INTERVAL 1 DAY) GROUP BY CAST(cDate as DATE), HOUR(cDate) ORDER BY cDate ASC");
$statementPerformanceInfoDayX->execute();
$resultPerformanceInfoDayX = $statementPerformanceInfoDayX->fetchAll();

// Select performance information from the last hour. (Those are y-axis values for the chart)
$statementPerformanceInfoHourX = $pdo->prepare("SELECT cDate FROM performanceInfo WHERE hostInfo_id = $id AND cDate >= date_sub(NOW(), INTERVAL 1 hour) ORDER BY cDate ASC");
$statementPerformanceInfoHourX->execute();
$resultPerformanceInfoHourX = $statementPerformanceInfoHourX->fetchAll();

// Iterate over the 3 results, split the array by whitespace and declare an array for each time period. (Those are y-axis values for the chart)
// Example of how the jsonDataX could look like: "{["2019-01-19"], ["2019-01-20"], ["2019-01-21"]}" etc.
foreach ($resultPerformanceInfoMonthX as $row) {
    $timestamp = $row['cDate'];
    $performanceJsonDataMonthX[] = splitByWhitespace($timestamp)[0];
}
foreach ($resultPerformanceInfoDayX as $row) {
    $timestamp = $row['cDate'];
    $performanceJsonDataDayX[] = splitByWhitespace($timestamp);
}
foreach ($resultPerformanceInfoHourX as $row) {
    $timestamp = $row['cDate'];
    $performanceJsonDataHourX[] = splitByWhitespace($timestamp)[1];
}

/**--------------------------------------------------------------------*/
/** END: Perfomance info -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/

?>

<html lang="de">
<head>
    <title>Server Liste</title>
    <!-- Automatic logout after 1440 seconds -->
    <meta http-equiv="refresh" content="1440;url=logout.php" />
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
        <div class="background">
            <img src="images/logo.jpg">
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
    <li><a href="index.php">Startseite</a></li>
    <li><a href="serverList.php">Server Liste</a></li>
    <div class="divider"></div>
    <div class="credits">
        <p>Made by Adrian Zurbrügg <b>|</b> IPA-Projekt 2019</p>
    </div>
</ul>
<!-- END: Navigation ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------>

<div class="row">
    <div class="col s12 m11">
        <?php foreach ($resultHostInfo as $row) {
            ?>
            <h4> <?php echo $row['hostname']; ?></h4>
            <?php
        }
        ?>
    </div>
</div>

<!-- START: Mainpart Host information ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<div class="row">
    <div class="col s12 m11">
        <div class="card blue-grey darken-1">
            <div class="card-content white-text">
                <span class="card-title">Host Informationen</span>
            </div>
            <div class="card-tabs">
                <ul class="tabs tabs-fixed-width">
                    <li class="tab "><a class="active" href="#specifications_hostInfo">Spezifikationen</a></li>
                    <li class="tab"><a href="#edit_hostInfo">Bearbeiten</a></li>
                </ul>
            </div>
            <!-- Iterate over resultHostInfo and display the result in a table -->
            <?php foreach ($resultHostInfo as $row) {
                ?>
                <div class="card-content grey lighten-4">
                    <!-- Specifications ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
                    <div id="specifications_hostInfo">
                        <table id="tbl_host_specifications">
                            <tr>
                                <td width="40%" class="bold">Hostname:</td>
                                <td><?php echo $row['hostname']; ?></td>
                            </tr>
                            <tr>
                                <td class="bold">System:</td>
                                <td><?php echo $row['hostSystem']; ?></td>
                            </tr>
                            <tr>
                                <td class="bold">Administrator:</td>
                                <td><?php echo $row['systemAdministrator']; ?></td>
                            </tr>
                            <tr>
                                <td class="bold">Verfügbarkeitsklasse:</td>
                                <td><?php echo $row['type']; ?></td>
                            </tr>
                            <tr>
                                <td class="bold">Zusätzliche DNS-Server:</td>
                                <td><?php echo $row['additionalDNS']; ?></td>
                            </tr>
                            <tr>
                                <td class="bold">Beschreibung:</td>
                                <td><?php echo $row['description']; ?></td>
                            </tr>
                            <tr>
                                <td class="bold">Bemerkungen:</td>
                                <td><?php echo $row['notes']; ?></td>
                            </tr>
                        </table>
                    </div>
                    <!----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->


                    <!-- Edit --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
                    <form action="details.php?id=<?php echo $row['ID']; ?>" method="post">
                        <div id="edit_hostInfo">
                            <div class="row">
                                <div class="input-field col s12 m6 l6">
                                    <p class="text-left bold">Administrator:</p>
                                    <input type="text" name="input_administrator" id="input_administrator"
                                           value="<?php echo $row['systemAdministrator']; ?>">
                                </div>
                                <div class="input-field col s12 m6 l6">
                                    <p class="text-left bold">Zusätzliche DNS-Server:</p>
                                    <input type="text" name="input_additionalDNS" id="input_additionalDNS"
                                           value="<?php echo $row['additionalDNS']; ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="input-field col s12 m6 l6">
                                    <p class="text-left bold">Beschreibung:</p>
                                    <textarea class="textarea" id="input_description"
                                              name="input_description"><?php echo $row['description']; ?></textarea>
                                </div>
                                <div class="input-field col s12 m6 l6">
                                    <p class="text-left bold">Bemerkungen:</p>
                                    <textarea class="textarea" id="input_Note"
                                              name="input_Note"><?php echo $row['notes']; ?></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col s12 m6 l6">
                                    <p class="text-left bold">Verfügbarkeitsklasse:</p>
                                    <select class="form-control browser-default dropdown" id="dd_availabilityClass"
                                            name="dd_availabilityClass" required>
                                        <?php
                                        foreach ($resultAvailabilityClasses as $row2) {
                                            if ($row2['ID'] == $row['availabilityClass_id']) {
                                                ?>
                                                <option selected
                                                        value="<?php echo $row2['ID'] ?>"><?php echo $row['type'] ?></option>
                                                <?php
                                            } else {
                                                echo "<option value=" . $row2['ID'] . ">" . $row2['type'] . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <button class="btn blue-grey right" type="submit" name="btn_editHostInfo"
                                        id="btn_editHostInfo">Übernehmen
                                </button>
                            </div>
                        </div>
                    </form>
                    <!----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>
<!-- END: Mainpart Host information ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->

<!-- START: Mainpart Basic information ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<div class="row">
    <div class="col s12 m11">
        <div class="card blue-grey darken-1">
            <div class="card-content white-text">
                <span class="card-title">Basis Informationen</span>
            </div>
            <div class="card-tabs">
                <ul class="tabs tabs-fixed-width">
                    <li class="tab"><a class="active" href="#ip_basicInfo">IP-Informationen</a></li>
                    <li class="tab"><a href="#volumes_basicInfo">Volumes</a></li>
                    <li class="tab"><a href="#processor_ram_basicInfo">Prozessor/RAM</a></li>
                    <li class="tab"><a href="#systemInfo_basicInfo">System Informationen</a></li>
                </ul>
            </div>
            <!-- Iterate over resultBasicInfo and display the result in a table -->
            <?php foreach ($resultBasicInfo as $row) {
                ?>
                <div class="card-content grey lighten-4">
                    <!-- BasicInfo ip-information ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
                    <div id="ip_basicInfo">
                        <table id="tbl_basic_ip">
                            <tr>
                                <td width="40%" class="bold">IP-Adresse:</td>
                                <td><?php echo $row['ipAddress']; ?></td>
                            </tr>
                            <tr>
                                <td class="bold">Subnetzmaske:</td>
                                <td><?php echo $row['subnetmask']; ?></td>
                            </tr>
                            <tr>
                                <td class="bold">Gateway:</td>
                                <td><?php echo $row['gateway']; ?></td>
                            </tr>
                            <tr>
                                <td class="bold">DNS-Server:</td>
                                <td>
                                    <?php for ($i = 0; $i < sizeof($dnsServerSplitted); $i++) {
                                        echo $dnsServerSplitted[$i] . "<br>";
                                    } ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->

                    <!-- BasicInfo volumes -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
                    <div id="volumes_basicInfo">
                        <?php foreach ($resultHostInfo as $row2) {
                            if ($row2['hostSystem'] == 'Windows') {
                                ?>
                                <table id="tbl_basic_volumesWindows">
                                    <tr>
                                        <th>Buchstabe:</th>
                                        <th>Name:</th>
                                        <th>Filesystem:</th>
                                        <th>Health Status:</th>
                                        <th>Operational Status:</th>
                                        <th>Gesamter Speicherplatz (in GB):</th>
                                        <th>Belegter Speicherplatz (in GB):</th>
                                    </tr>
                                    <?php for ($i = 0; $i < sizeof($volumesSplitted); $i++) {
                                        echo '<tr>';
                                        $volumesData = explode(',', $volumesSplitted[$i]);
                                        foreach ($volumesData as $volumeData) {
                                            echo '<td>' . $volumeData . '</td>';
                                        }
                                        echo '</tr>';
                                    } ?>
                                </table>
                                <?php
                            } elseif ($row2['hostSystem'] == 'Linux') {
                                ?>
                                <table id="tbl_basic_volumesLinux">
                                    <tr>
                                        <th>Filesystem:</th>
                                        <th>Type:</th>
                                        <th>Gesamter Speicherplatz (in GB):</th>
                                        <th>Belegter Speicherplatz (in GB):</th>
                                        <th>Mounted:</th>
                                    </tr>
                                    <?php for ($i = 0; $i < sizeof($volumesSplitted); $i++) {
                                        echo '<tr>';
                                        $volumesData = explode(',', $volumesSplitted[$i]);
                                        foreach ($volumesData as $volumeData) {
                                            echo '<td>' . $volumeData . '</td>';
                                        }
                                        echo '</tr>';
                                    } ?>
                                </table>
                                <?php
                            }
                        } ?>
                    </div>
                    <!----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->

                    <!-- BasicInfo processor ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------>
                    <div id="processor_ram_basicInfo">
                        <table id="tbl_basic_processor_ram">
                            <tr>
                                <td width="40%" class="bold">Prozessor:</td>
                                <td><?php echo $row['processor']; ?></td>
                            </tr>
                            <tr>
                                <td class="bold">Kerne:</td>
                                <td><?php echo $row['cores']; ?></td>
                            </tr>
                            <tr>
                                <td class="bold">Logische Kerne:</td>
                                <td><?php echo $row['logicalCores']; ?></td>
                            </tr>
                            <tr>
                                <td class="bold">Anzahl RAM:</td>
                                <td><?php echo $row['ram']; ?> GB</td>
                            </tr>
                        </table>
                    </div>
                    <!----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->

                    <!-- BasicInfo system information --------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
                    <div id="systemInfo_basicInfo">
                        <table id="tbl_basic_system">
                            <tr>
                                <td width="40%" class="bold">Betriebssystem:</td>
                                <td><?php echo $row['operatingSystem']; ?></td>
                            </tr>
                            <tr>
                                <td class="bold">Hersteller:</td>
                                <td><?php echo $row['manufacturerInfo']; ?></td>
                            </tr>
                            <tr>
                                <td class="bold">Uptime:</td>
                                <td>
                                    <?php for ($i = 0; $i < sizeof($uptimeSplitted); $i++) {
                                        echo $uptimeSplitted[$i] . "<br>";
                                    } ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>
<!-- END: Mainpart Basic information ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------>


<!-- START: Mainpart Performance information ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<div class="row">
    <div class="col s12 m11">
        <div class="card blue-grey darken-1">
            <div class="card-content white-text">
                <span class="card-title">Performance Informationen</span>
            </div>
            <div class="card-tabs">
                <ul class="tabs tabs-fixed-width">
                    <li class="tab"><a class="active" href="#volumes_performanceInfo">Volumes</a></li>
                    <li class="tab"><a href="#cpu_performanceInfo">CPU</a></li>
                    <li class="tab"><a href="#ram_performanceInfo">RAM</a></li>
                </ul>
            </div>
            <div class="card-content grey lighten-4">
                <!-- PerformanceInfo volumes ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------>
                <div id="volumes_performanceInfo">
                    <div class="col s12 m6 l6">
                        <p class="text-left bold">Zeitspanne:</p>
                        <select class="form-control browser-default dropdown" id="dd_performanceInfo_volumes_time">
                            <option value="">Auswählen</option>
                            <option value="hour">Stunde</option>
                            <option value="day">Tag</option>
                            <option value="month">Monat</option>
                        </select>
                        <br>
                    </div>
                    <div class="col s12 m6 l6">
                        <p class="text-left bold">Volume:</p>
                        <select class="form-control browser-default dropdown" id="dd_performanceInfo_volumes">
                            <option value="choose" selected="selected">Auswählen</option>
                            <!-- Split the diskUsages array to get every volume letter for the dropdown -->
                            <?php
                            $diskUsages = splitByWhitespace($diskUsages);
                            foreach ($diskUsages as $diskUsage) {
                                $diskUsageSplitted = explode(",", $diskUsage);
                                $disk = $diskUsageSplitted[0];
                                ?>
                                <option value="<?php echo $disk; ?>"><?php echo $disk; ?></option>
                                <?php
                            }
                            ?>
                        </select>
                        <br>
                    </div>
                    <canvas id="volumesChart"></canvas>
                </div>
                <!--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->

                <!-- PerformanceInfo CPU ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
                <div id="cpu_performanceInfo">
                    <div class="col s12 m6 l6">
                        <p class="text-left bold">Zeitspanne:</p>
                        <select class="form-control browser-default dropdown" id="dd_performanceInfo_time_cpu">
                            <option value="choose">Auswählen</option>
                            <option value="hour">Stunde</option>
                            <option value="day">Tag</option>
                            <option value="month">Monat</option>
                        </select>
                        <br>
                    </div>
                    <canvas id="cpuChart"></canvas>
                </div>
                <!--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->

                <!-- PerformanceInfo RAM ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
                <div id="ram_performanceInfo">
                    <div class="col s12 m6 l6">
                        <p class="text-left bold">Zeitspanne:</p>
                        <select class="form-control browser-default dropdown" id="dd_performanceInfo_time_ram">
                            <option value="choose">Auswählen</option>
                            <option value="hour">Stunde</option>
                            <option value="day">Tag</option>
                            <option value="month">Monat</option>
                        </select>
                        <br>
                    </div>
                    <canvas id="ramChart"></canvas>
                </div>
                <!--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
            </div>
        </div>
    </div>
</div>
<!-- END: Mainpart Performance information ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------>

<!-- Include chartjs -->
<script src="js/http_cdnjs.cloudflare.com_ajax_libs_Chart.js_2.4.0_Chart.js"></script>
<script type="text/javascript">
    /* Initialize several functions of materializecss */
    $(document).ready(function () {
        $('.sidenav').sidenav();
        $('.dropdown-trigger').dropdown();
        $('.collapsible').collapsible();
        $('.tabs').tabs();
    });

    /* Declare variables which are used to fill the chart with data.
     * jsonDataX = x-axis data.
     * jsonDataY = y-axis data. */
    var jsonDataX;
    var jsonDataY;

    /* Generates a new chart. Parameter: canvas element in which the chart will be loaded. Return value: chart object */
    function generateChart(ctx) {
        var chart = new Chart(ctx, {
            // The type of chart to create
            type: 'line',

            // The data for the dataset
            data: {
                labels: jsonDataX,
                datasets: [{
                    label: "Wert in %",
                    borderColor: 'rgb(255, 99, 132)',
                    data: jsonDataY,
                }]
            },

            // Configuration options
            options: {
                scales: {
                    yAxes: [{
                        display: true,
                        ticks: {
                            min: 0,
                            max: 100
                        }
                    }]
                },
                legend: {
                    display: false
                }
            }
        });
        return chart;
    }

    /* Takes the value of jsonDataX and pushes it to the corresponding chart.
     * Parameters:
     * jsonDataX = X-axis values. (array)
     * chart = which chart gets the data (object) */
    function pushDataToChartX(jsonDataX, chart){
        chart.data.labels = [];
        for (var i in jsonDataX) {
            chart.data.labels.push(jsonDataX[i]);
        }
    }

    /* Takes the value of jsonDataY and pushes it to the corresponding chart.
     * Parameters:
     * jsonDataY = Y-axis values. (array)
     * chart = which chart gets the data (object) */
    function pushDataToChartY(jsonDataY, chart){
        chart.data.datasets[0].data = [];
        for (var i in jsonDataY) {
            chart.data.datasets[0].data.push(jsonDataY[i]);
        }
    }

    /* Sets the value of jsonDataX and jsonDataY according to the given time span. This could be a hour, a day or a month.
     * Parameters:
     * period = time period (string)
     * chart = which chart gets the data (object)
     * jsonDataX = contains values of corresponding time period. X-axis. (array)
     * jsonDataY = contains values of the corresponding time period. Y-axis. (array) */
    function setJsonDataXY(period, chart, jsonDataX, jsonDataY){
        /* Sets the time period (x-axis data) and jsonDataY (y-axis data) */
        if (period === 'hour') {
            pushDataToChartX(jsonDataX, chart);
            pushDataToChartY(jsonDataY, chart);
        } else if (period === 'day') {
            pushDataToChartX(jsonDataX, chart);
            pushDataToChartY(jsonDataY, chart);
        } else if (period === 'month') {
            pushDataToChartX(jsonDataX, chart);
            pushDataToChartY(jsonDataY, chart);
        }
    }

    /* START: Chart Volumes ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    /* Get the canvas element and create a new chart for volumes */
    var ctxDisks = document.getElementById('volumesChart').getContext('2d');
    var chartDisks = generateChart(ctxDisks);

    /* Checks if the dropdown with the volumes is changed and pushes the corresponding data to the chart. */
    $("#dd_performanceInfo_volumes").change(function () {
        chartDisks.data.datasets[0].data = [];
        var selectedVol = $('#dd_performanceInfo_volumes :selected').val();

        /* Pass the selected value of the volumes to jsonDataY */
        var volumeDataY = jsonDataY[selectedVol];

        /* The y-axis data for the volumes is pushed in a specific format. Check the arrangeJsonData function to see which format. */
        for (var i in volumeDataY) {
            chartDisks.data.datasets[0].data.push({y: volumeDataY[i].y});
        }
        chartDisks.update();
    });

    /* Checks if the dropdown with the time period is changed and pushes the corresponding data to the chart. */
    $("#dd_performanceInfo_volumes_time").change(function () {

        /* This resets the volume dropdown to "Auswählen" if the time period dropdown is changed */
        $('#dd_performanceInfo_volumes').val('choose');

        var selectedTime = $('#dd_performanceInfo_volumes_time :selected').val();

        /* Sets the time period (x-axis data) and jsonDataY (y-axis data) */
        if (selectedTime === 'hour') {
            jsonDataX = <?php echo json_encode($performanceJsonDataHourX); ?>;
            jsonDataY = <?php echo json_encode($diskUsagesArrayHourY); ?>;
            pushDataToChartX(jsonDataX, chartDisks);
        }else if (selectedTime === 'day') {
            jsonDataX = <?php echo json_encode($performanceJsonDataDayX); ?>;
            jsonDataY = <?php echo json_encode($diskUsagesArrayDayY); ?>;
            pushDataToChartX(jsonDataX, chartDisks);
        }else if (selectedTime === 'month') {
            jsonDataX = <?php echo json_encode($performanceJsonDataMonthX); ?>;
            jsonDataY = <?php echo json_encode($diskUsagesArrayMonthY); ?>;
            pushDataToChartX(jsonDataX, chartDisks);
        }
        chartDisks.reset();
    });
    /* END: Chart Volumes ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/


    /* START: Chart CPU --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    /* Get the canvas element and create a new chart for CPU */
    var ctxCpu = document.getElementById('cpuChart').getContext('2d');
    var chartCpu = generateChart(ctxCpu);

    /* If the time period dropdown is changed, update data */
    $("#dd_performanceInfo_time_cpu").change(function () {

        var selectedTime = $('#dd_performanceInfo_time_cpu :selected').val();

        /* Calls the function setJsonDataXY according to the dropdown selection */
        if (selectedTime === 'hour'){
            setJsonDataXY(selectedTime, chartCpu,  <?php echo json_encode($performanceJsonDataHourX); ?>, <?php echo json_encode($cpuUsagesArrayHourY); ?>);
        } else if (selectedTime === 'day'){
            setJsonDataXY(selectedTime, chartCpu,  <?php echo json_encode($performanceJsonDataDayX); ?>, <?php echo json_encode($cpuUsagesArrayDayY); ?>);
        } else if (selectedTime === 'month'){
            setJsonDataXY(selectedTime, chartCpu,  <?php echo json_encode($performanceJsonDataMonthX); ?>, <?php echo json_encode($cpuUsagesArrayMonthY); ?>);
        }
        chartCpu.update();
    });
    /* END: Chart CPU ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/


    /* START: Chart RAM -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    var ctxRam = document.getElementById('ramChart').getContext('2d');
    var chartRam = generateChart(ctxRam);

    /* If the time period dropdown is changed, update data */
    $("#dd_performanceInfo_time_ram").change(function () {

        var selectedTime = $('#dd_performanceInfo_time_ram :selected').val();

        /* Calls the function setJsonDataXY according to the dropdown selection */
        if (selectedTime === 'hour'){
            setJsonDataXY(selectedTime, chartRam,  <?php echo json_encode($performanceJsonDataHourX); ?>, <?php echo json_encode($ramUsagesArrayHourY); ?>);
        } else if (selectedTime === 'day'){
            setJsonDataXY(selectedTime, chartRam,  <?php echo json_encode($performanceJsonDataDayX); ?>, <?php echo json_encode($ramUsagesArrayDayY); ?>);
        } else if (selectedTime === 'month'){
            setJsonDataXY(selectedTime, chartRam,  <?php echo json_encode($performanceJsonDataMonthX); ?>, <?php echo json_encode($ramUsagesArrayMonthY); ?>);
        }
        chartRam.update();
    });
    /* END: Chart RAM ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/


</script>
</body>
</html>


