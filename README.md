# Transbank SDK Wrapper - @dev

Wrapper no-oficial de [Transbank SDK](https://github.com/TransbankDevelopers/transbank-sdk-php) para mejorar la experiencia de uso.

> Esta versión es incompatible con PHP 5. Para usar este código con PHP 5, usa la versión 1.4.3 del SDK oficial.

> Esta paquete es un trabajo en progreso ¡No lo uses en producción hasta que no esté listo! 

> Esta versión ocupa el namespace `Transbank/Wrapper`. En la versión final va a cambiar.

## Requisitos:

- PHP 7.1.3 o mayor
- Composer

## Dependencias

Este paquete descarga automáticamente el SDK oficial de Transbank como dependencia, además de la [implementación de SOAP de Luis Urrutia](https://github.com/LuisUrrutia/TransbankSoap).  

A su vez, el SDK oficial necesita las siguientes extensiones de PHP habilitadas:

* ext-curl
* ext-json
* ext-mbstring
* ext-soap
* ext-dom

Instalarlas dependerá de tu sistema: en algunos casos sólo necesitarás habilitarlas en tu `php.ini`; en otros, descargarlas usando tu gestor de packetes (como `apt-get` o `apk`) o compilarlas manualmente. 

# Instalación

Hay tres formas para instalar el paquete: usando Composer, sin composer, y todo de forma (muy) manual.

### Instalar con Composer (fácil)

Para usar el SDK en tu proyecto puedes usar Composer, instalándolo desde la consola:

```bash
composer require darkghosthunter/transbank-wrapper "1.0-beta"
```

También puedes añadir el SDK como dependencia a tu proyecto y luego ejecutar `composer update`.

```json
    "require": {
        "darkghostgunter/transbank-wrapper": "@dev"
    }
```

### Instalación sin Composer (complicado)

Además de tener instalado la línea de comandos de PHP, debes descargar el código desde este repositorio, descomprimirlo en el directorio que desees, y realizar lo siguiente:

1 - [Descargar `composer.phar`](https://getcomposer.org/download/) en el mismo directorio donde descomprimiste el SDK.

2 - Ejecutar en el directorio del SDK:

```bash
php composer.phar install --nodev
```

3 - Requerir el SDK directamente desde tu aplicación 

```php
require_once('/directorio/del/sdk/load.php');
```

### Instalación remota (jodido)

Si no tienes acceso a la consola de tu servidor web, siempre puedes usar tu propio sistema: 

* [Descarga PHP](http://php.net/downloads.php)

* [Descarga `composer.phar`](https://getcomposer.org/download) donde descargaste este paquete.

* Abre una ventana de consola (powershell en Windows, Terminal en MacOS, *sh en Linux) y tipea:

```bash
directorio/de/php/php.exe composer.phar install --no-dev
```

> (Si en MacOS y Unix, omite `.exe`)

* Comprime el directorio del paquete.

* Sube el directorio del paquete a tu servidor y descomprímlo allí.

> Si subes cada archivo uno por uno, puedes demorarte horas.

* Continúa con el [tercer paso de la instalación manual](#instalación-manual-complicado).

## Documentación 

La documentación de este Wrapper está [en la Wiki](https://github.com/DarkGhostHunter/transbank-wrapper/wiki).

La información sobre las variables de cada transacción está en [Transbank Developers](https://www.transbankdevelopers.cl).

## Información para contribuir y desarrollar este Wrapper

Tirar la talla en buen chileno en los PR. Si usas otro idioma, serás víctima de bullying.

# Licencia

Este paquete está licenciado bajo la [Licencia MIT](LICENCIA) [(En inglés)](LICENSE).

`Redcompra`, `Webpay`, `Onepay`, `Patpass` y `tbk` son marcas registradas de [Transbank S.A.](https://www.transbank.cl/)

Este paquete no está aprobado, apoyado ni avalado por Transbank S.A. ni ninguna persona natural o jurídica vinculada directa o indirectamente a Transbank S.A.