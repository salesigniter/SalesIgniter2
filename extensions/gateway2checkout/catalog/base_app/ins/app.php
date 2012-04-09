<?php
ob_start();
print_r($_GET);
print_r($_POST);
$content = ob_get_contents();
ob_end_clean();
mail('stephen@itwebexperts.com', 'ins message', $content);
itwExit();