# Explicación de mis Rutas de Consola y Comandos de Terminal (`routes/console.php`)

En este archivo guardo los **comandos de texto** que puedo ejecutar desde la terminal (la consola negra de comandos de la computadora) en lugar de hacerlo desde el navegador web. 

Mientras que los archivos `web.php` y `auth.php` responden cuando alguien hace clic en una página web, este archivo responde cuando el programador o la administradora técnica escribe una orden directa en la computadora usando la herramienta `artisan`.

---

## 1. Conexión de Herramientas de Inspiración y Consola

| Código | ¿Qué hace? |
| :--- | :--- |
| `use Illuminate\Foundation\Inspiring;` | Traigo una colección de frases célebres y motivacionales de pensadores, científicos y artistas que vienen incluidas en Laravel. |
| `use Illuminate\Support\Facades\Artisan;` | Traigo a mi asistente de comandos `Artisan`, que es quien escucha y ejecuta las órdenes escritas en la terminal. |

---

## 2. Creación del Comando Motivacional (`inspire`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `Artisan::command('inspire', function () { ... })` | Creo una orden llamada `inspire`. Cuando alguien escribe en la terminal `php artisan inspire`, se activa esta función. |
| `$this->comment(Inspiring::quote());` | Escoge una frase sabia o inspiradora al azar de la colección y la imprime con letras de color en la pantalla de la terminal para motivar a quien esté programando. |
| `->purpose('Display an inspiring quote');` | Le agrega una pequeña etiqueta de descripción que explica para qué sirve este comando cuando se consulta la lista general de ayudas de la terminal. |
