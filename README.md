# Boletería G4 web

Iniciar Apache y MySQL en XAMPP y abrir http://localhost/BoleteriaG4_web/.
Las cuentas utilizan sus contraseñas existentes. La base web es independiente de la base Java.

La configuración activa está en `config/config.example.php`; puede complementarse con `config/local.php` o las variables DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD y APP_ENV. No eliminar config.example.php: bootstrap.php lo carga directamente.

Para hosting PHP/MySQL: configurar HTTPS, APP_ENV=production, usuario MySQL propio con contraseña y una base web independiente ya preparada. Se necesitan PDO MySQL, mbstring, iconv y zlib. No se requieren frameworks, Composer, Java ni GD. Mantener FPDF y sus fuentes/licencia.

Conservar los .htaccess y permitir sus reglas en Apache. `storage` necesita escritura para PHP y debe permanecer bloqueado por HTTP: guarda auditoría, errores y control de intentos. Nunca publicar sus registros ni una configuración con secretos.

Los SQL de instalación, herramientas, pruebas, documentación técnica y respaldos se conservan fuera de htdocs, en el entorno de desarrollo. No son necesarios para operar. La aplicación no modifica esquemas automáticamente.

No se ha publicado en hosting. Antes de hacerlo, preparar respaldos y probar lector USB, audio e impresión en el equipo de operación.
