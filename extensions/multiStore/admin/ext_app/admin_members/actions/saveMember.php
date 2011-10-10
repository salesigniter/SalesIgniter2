<?php
$adminAccount->admins_stores = implode(',', $_POST['admins_stores']);
$adminAccount->save();
