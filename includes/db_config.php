<?php
/**
 * Veritabanı Yapılandırma Dosyası
 * Bu dosya veritabanı bağlantı bilgilerini güvenli bir şekilde saklar
 */

// Doğrudan erişimi engelle
if (!defined('ASEC_SECURITY') && !defined('ASEC_LOADED')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Bu dosyaya doğrudan erişim yasaktır.');
}

// Veritabanı yapılandırma bilgileri
return [
    'host' => 'localhost',
    'dbname' => 'db_asec',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];
