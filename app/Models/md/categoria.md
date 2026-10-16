# Explicación de mi modelo `Categoria.php` (En mis palabras)

En este archivo controlo las **familias o grupos de servicios** que ofrecemos en **LashApp**. Es el modelo que organiza el catálogo del salón para que los servicios no estén revueltos y las clientas puedan encontrar fácilmente lo que buscan (como pestañas, cejas, depilación o combos especiales).

Aquí te explico cómo funciona de forma rápida y sencilla:

---

## 1. ¿Qué representa en el estudio de belleza?

Es como las **secciones o pestañas de nuestra carta de servicios**. En lugar de mostrar una lista gigante desordenada, las agrupamos en categorías lógicas:
* **Pestañas:** Extensiones Clásicas, Volumen Ruso, Efecto Foxy, Retoque.
* **Cejas:** Diseño y Depilación, Henna, Laminado de Cejas.
* **Tratamientos:** Lifting de Pestañas, Botox de Pestañas.

Este modelo `Categoria.php` es el que guarda esos grupos en la base de datos.

---

## 2. La tabla y los campos que guardo (`$table` y `$fillable`)

```php
protected $table = 'categorias';

protected $fillable = [
    'nombre',
    'descripcion',
    'estado',
];
```

* **`$table = 'categorias'`:** Se conecta directamente con la tabla `categorias` de la base de datos.
* **`nombre`:** El nombre del grupo (por ejemplo: `'Pestañas'` o `'Cejas'`).
* **`descripcion`:** Un texto corto explicando qué tipo de servicios abarca esta categoría.
* **`estado`:** Dice si la categoría está `'activo'` (se muestra en la web para agendar) o `'inactivo'` (oculta temporalmente).

---

## 3. Su relación con los servicios (`servicios`)

```php
public function servicios()
{
    return $this->hasMany(Servicio::class, 'categoria_id');
}
```

* **Qué significa:** *"Una categoría tiene muchos servicios"* (`hasMany`).
* **Para qué me sirve:** Si tengo la categoría "Pestañas", con `$categoria->servicios` puedo traer automáticamente todos los servicios que pertenecen a esa categoría con sus respectivos precios, duraciones y fotos, listos para pintarlos en la pantalla del catálogo.

---

## En resumen: ¿Para qué sirve `Categoria.php`?
* Organiza el menú de servicios del salón en familias limpias y ordenadas.
* Conecta cada grupo con sus servicios individuales (`Servicio`).
* Hace que la experiencia de navegación para la clienta sea mucho más intuitiva al momento de reservar.
