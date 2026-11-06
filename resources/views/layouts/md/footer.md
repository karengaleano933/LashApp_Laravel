# Explicación de mi Vista de Pie de Página (`layouts/footer.blade.php`)

Esta vista es el pie de página común que cierra la interfaz visual en LashApp. Contiene el logotipo del estudio con su nombre oficial, la información de contacto físico y telefónico (Neiva–Huila, Colombia, horarios de atención), los enlaces a redes sociales (Instagram, WhatsApp, TikTok, Facebook) y los créditos de derechos de autor con el año actual generado dinámicamente.

---

### 1. Estructura del pie de página y datos de contacto

| Código | ¿Qué hace? |
| :--- | :--- |
| `<footer class="footer">` | Contenedor principal del pie de página al final de cada pantalla. |
| `<img src="{{ asset('img/logo.png') }}" alt="LashApp" class="footer-logo">` | Muestra el logotipo de LashApp en el pie de página. |
| `<p>Oh La Lash by Lorena Ayala Studio</p>` | Razón social y nombre del salón de belleza. |
| `<li><i class="fa-solid fa-phone"></i> 3147662316</li>` | Número telefónico de atención al cliente. |
| `<li><i class="fa-solid fa-location-dot"></i> Neiva–Huila, Colombia</li>` | Ubicación geográfica de la sede principal. |
| `<li><i class="fa-solid fa-clock"></i> Lun – Sáb: 8:00 AM – 6:00 PM</li>` | Horario de atención al público de lunes a sábado. |

---

### 2. Redes sociales y derechos de autor

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="social-links"> ... </div>` | Botones circulares con enlaces a los canales de Facebook, Instagram, WhatsApp y TikTok del estudio. |
| `<p>&copy; {{ date('Y') }} LashApp &mdash; Todos los derechos reservados.</p>` | Genera automáticamente el año en curso (ej. `2026`) para la leyenda legal de derechos reservados. |
