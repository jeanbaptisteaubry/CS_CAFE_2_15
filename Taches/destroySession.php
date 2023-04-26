<?php

//Code à utiliser sur le navigateur, quand une session bloque !
session_start();
unset($_SESSION);
session_unset();
session_unset();

echo "session détruite";