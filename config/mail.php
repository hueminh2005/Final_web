<?php
/**
 * Mail Configuration
 */

return [
    'driver' => 'smtp',
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'your_email@gmail.com',
    'password' => 'your_app_password',
    'encryption' => 'tls',
    'from' => [
        'address' => 'noreply@noteapp.com',
        'name' => 'Note Management App'
    ],
    'throwExceptions' => true,
];
?>
