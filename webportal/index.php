<?php
/**
 * Autor: Adrian Zurbrügg
 * Homepage. Here you have a little overview and little instruction how to use the tool.
 * Also here is the login, which you need to do before you can access any server data.
 */

include('connection.php');
session_start();

// If login button clicked, search credentials in database
if (isset($_POST['anmeldenButton'])) {

    $usernameLogin = htmlspecialchars($_POST['login-username']);
    $passwortLogin = $_POST['login-password'];
    $statement = $pdo->prepare("SELECT * FROM user WHERE username = '$usernameLogin'");
    $statement->execute();
    $user = $statement->fetch();
    $passwordHash = $user['password'];

    // Check if password is correct //
    if ($user['username'] == $usernameLogin && password_verify($passwortLogin, $passwordHash)) {
        $_SESSION['username'] = $user['username'];
        header('Location: serverList.php');
    } else {
        // Feedback: credentials are incorrect //
        $wrongCredentials = "<script>alert('Benutzerdaten stimmen nicht')</script>";
        echo $wrongCredentials;
    }
}

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
<br>
<!-- START: Card ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<div class="row content_index">
    <div class="col s12 m11 l10">
        <div class="card blue-grey darken-1">
            <div class="card-content white-text">
                <span class="card-title">Systemüberwachung</span>
                <p>Die hier ersichtlichen Informationen, ermöglichen eine schnelle Übersicht der entsprechenden Server.</p>
                <p>Melden Sie sich an um die Daten der registrierten Server einzusehen. </p>
            </div>
            <div class="card-tabs">
                <ul class="tabs tabs-fixed-width">
                    <li class="tab"><a class="active" href="#index_login">Anmelden</a></li>
                    <li class="tab"><a href="#index_instruction">Anleitung</a></li>
                    <li class="tab"><a href="#index_news">Neuigkeiten</a></li>
                </ul>
            </div>
            <div class="card-content grey lighten-4">
                <!-- START: Login tab ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
                <div id="index_login">
                    <form method="post" enctype="multipart/form-data">
                        <div class="card-content grey lighten-4">
                            <div id="specifications_hostInfo">
                                <div class="input-field">
                                    <input type="text" id="login-username" name="login-username" required>
                                    <label for="login-username">Benutzername</label>
                                </div>
                                <div class="input-field">
                                    <input type="password" id="login-password" name="login-password" required>
                                    <label for="login-password">Passwort</label>
                                    <button type="submit" name="anmeldenButton" class="btn blue-grey right">Anmelden</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- END: Login tab --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->

                <!-- START: Instruction tab ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
                <div id="index_instruction">
                    <ul>
                        <li><b>1. Serverliste</b></li>
                        <p>Hier sind alle Systeme erfasst. Die Server werden in Windows- und Linuxsysteme differenziert. Um detailliertere Informationen zu sehen, klicken Sie den entsprechenden Server an. </p><br>
                        <li><b>2. Details:</b>
                            <br>
                            <ul style="list-style-position: outside">
                                <li class="list-padding"><b>2.1. Host Informationen</b>
                                    <p>Beinhaltet den Hostnamen, das System (Windows oder Linux) und Freitext Felder. Diese Freitext
                                        Felder können bearbeitet werden. Gehen Sie dazu unter "Host Informationen" auf
                                        das Register "Bearbeiten".</p><br>
                                </li>
                                <li class="list-padding"><b>2.2. Basis Informationen</b>
                                    <p>Beinhaltet grundlegende Informationen wie IP-Informationen, Volumes, Prozessor/RAM und System Informationen (Betriebsystem, Hersteller, Uptime)</p><br>
                                </li>
                                <li class="list-padding"><b>2.3. Performance Informationen</b>
                                    <p>Beinhaltet die belegung der Volumes und die Auslastung von CPU und RAM. Der
                                        Zeitraum der Daten kann bei allen Charts zwischen einem Tag, einer Stunde und
                                        einem Monat gewählt werden. Bei den Disks ist zusätzlich noch eine
                                        Dropdown-Liste, welche es ermöglicht zwischen den einzelnen Disks zu wechseln</p><br>
                                </li>
                                <li class="list-padding"><b>2.4. Verfügbarkeits Informationen</b>
                                    <p>Beinhaltet die erreichbarkeit des Servers. Dies wird über PING abgefragt. Ein
                                        Server kann folgende 3 Zustände haben: 0 =
                                        Nicht erreichbar / 1 = Erreichbar / 2 = Initilaisierung. Initialisierung
                                        bedeutet das ein Server neu hinzugefügt wurde. Es dient als ein Platzhalter Zustand,
                                        bis das Availability-Script das erste mal den neu hinzugefügten Server mittels
                                        PING anspricht.</p><br>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <!-- END: Instruction tab --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->

                <!-- START: News tab -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
                <div id="index_news">
                    <p class="bold">Neue Funktionen / Änderungen</p>
                    <p>Funktion eins. Ermöglicht das einsehen von....</p>
                    <p>Funktion zwei. Daten können nun....</p>
                    <p>Funktion drei. Das verwenden von .... ist nun ....</p>
                    <br>
                    <p> <b>PowerShell Main-Script:</b> Version 1.1.0</p>
                    <p> <b>PowerShell Availability-Script:</b> Version 1.0.0</p>
                    <p> <b>Webportal:</b> Version 1.0.0</p>
                </div>
                <!-- END: New tab ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
            </div>
        </div>
    </div>
</div>
<!-- END: Card ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------>

<script type="application/x-javascript">
    $(document).ready(function () {
        $('.sidenav').sidenav();
        $('.dropdown-trigger').dropdown();
        $('.collapsible').collapsible();
        $('.tabs').tabs();
    });

</script>
</body>
</html>