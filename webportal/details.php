<?php
/**
 * Created by PhpStorm.
 * User: lw-lzua
 * Date: 11.03.2019
 * Time: 08:09
 */

$pw = password_hash("London99!", PASSWORD_BCRYPT);
echo $pw;