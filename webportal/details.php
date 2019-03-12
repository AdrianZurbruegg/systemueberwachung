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

/** START: Host information ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
// Select host information and save it to $resultHostInfo variable */
$statementHostInfo = $pdo->prepare("SELECT hostInfo.*, availabilityClass.type FROM hostInfo JOIN availabilityClass on hostInfo.availabilityClass_id = availabilityClass.ID WHERE hostInfo.ID = $id");
$statementHostInfo->execute();
$resultHostInfo = $statementHostInfo->fetchAll();
/** END: Host information ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/

// Select availabilityClass information.
$statementAvailabilityClasses = $pdo->prepare("SELECT * FROM availabilityClass");
$statementAvailabilityClasses->execute();
$resultAvailabilityClasses = $statementAvailabilityClasses->fetchAll();

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

<script type="text/javascript">
    /* Initialize several functions of materializecss */
    $(document).ready(function () {
        $('.sidenav').sidenav();
        $('.dropdown-trigger').dropdown();
        $('.collapsible').collapsible();
        $('.tabs').tabs();
    });
</script>
</body>
</html>


