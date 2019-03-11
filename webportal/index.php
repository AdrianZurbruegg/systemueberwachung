<?php
/**
 * Autor: Adrian Zurbrügg
 * Homepage. Here you have a little overview and little instruction how to use the tool.
 * Also here is the login, which you need to do before you can access any server data.
 */
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
<ul id="slide-out" class="sidenav sidenav-fixed">
    <li>
        <div class="user-view">
            <div class="background">
                <img src="images/IT-Logo.PNG">
            </div>
        </div>
    </li>
    <p><span class="black-text name nav-text">Angemeldet als:</span></p>
    <li><a>Abmelden</a></li>
    <div class="divider"></div>

    <li><a href="index.php">Home</a></li>
    <div class="divider"></div>
    <div class="credits">
        <p>Made by Adrian Zurbrügg <b>|</b> IPA-Projekt 2019</p>
    </div>
</ul>
<br>
<div class="row">
    <div class="col s12 m11 l10">
        <div class="card blue-grey darken-1">
            <div class="card-content white-text">
                <span class="card-title">Information</span>
                <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et
                    dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet
                    clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet,
                    consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat,
                    sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no
                    sea takimata sanctus est Lorem ipsum dolor sit amet.</p>
            </div>
            <div class="card-tabs">
                <ul class="tabs tabs-fixed-width">
                    <li class="tab"><a class="active" href="#index_login">Anmelden</a></li>
                    <li class="tab"><a href="#index_instruction">Anleitung</a></li>
                    <li class="tab"><a href="#index_news">Neuigkeiten</a></li>
                </ul>
            </div>
            <div class="card-content grey lighten-4">
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
                <div id="index_instruction">
                    <ul>
                        <li><b>1. Serverliste</b></li>
                        <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p><br>
                        <li><b>2. Details:</b>
                            <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p><br>
                            <ul style="list-style-position: outside">
                                <li class="list-padding"><b>2.1. Host Informationen</b>
                                    <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p><br>
                                </li>
                                <li class="list-padding"><b>2.2. Basis Informationen</b>
                                    <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p><br>
                                </li>
                                <li class="list-padding"><b>2.3. Performance Informationen</b>
                                    <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p><br>
                                </li>
                                <li class="list-padding"><b>2.4. Verfügbarkeits Informationen</b>
                                    <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p><br>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div id="index_news">
                    Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et
                    dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet
                    clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet,
                    consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat,
                    sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.
                </div>
            </div>
        </div>
    </div>
</div>

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