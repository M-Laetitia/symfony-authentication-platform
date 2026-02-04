<?php
file_put_contents(__DIR__.'/var/log/dev.log', "TEST LOG ".date('c')."\n", FILE_APPEND);
echo "Log écrit\n";