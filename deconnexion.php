<?php
session_start();
session_destroy();
header('Location: connexion_user.php');
exit;