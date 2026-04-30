<?php
$str = file_get_contents("resources/views/properties/create.php");
// It's encoded broken on reading by powershell, let's copy by file system
copy("resources/views/properties/create.php", "resources/views/properties/edit.php");
echo "edit.php base copied.\n";