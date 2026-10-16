# Explicación de mi Vista de Reportes y Rendimiento (`reportes/reportes.blade.php`) (En mis palabras)

Esta vista es la herramienta de **Business Intelligence (Inteligencia de Negocios)** de **LashApp**. Convierte todos los datos de citas, pagos y calificaciones en gráficas interactivas, tablas comparativas y métricas de tendencia para que la dueña del salón pueda tomar decisiones estratégicas basadas en números reales.

---

## 1. Barra de Períodos y Exportación

En la parte superior agregué controles para definir qué lapso de tiempo se está analizando:

* **Selector de período:** Muestra por defecto el rango de fechas del último mes (`d/m/Y - d/m/Y`) usando `Carbon`.
* **Comparador:** Permite contrastar los resultados actuales contra el *período anterior* o contra el *mismo mes del año anterior*.
* **Botón "Exportar Reporte":** Ejecuta `window.print()`, que gracias a las reglas CSS `@media print` genera un documento PDF limpio y estilizado listo para imprimir o enviar a un contador.

---

## 2. Tarjetas de KPIs con Tendencias de Crecimiento

En la primera fila se destacan 4 indicadores clave de desempeño (KPIs):

1. **Ingresos Totales (`$totalIngresos`):** La facturación global acumulada con un indicador de porcentaje de crecimiento (`+12% vs período anterior`).
2. **Total de Citas (`$totalCitas`):** Volumen de servicios prestados con su indicador de tendencia.
3. **Nuevos Clientes (`$totalClientes`):** Cuántas personas nuevas conocieron el estudio y agendaron por primera vez.
4. **Calificación Promedio (`$promedioEstrellas`):** El nivel de satisfacción global sobre 5 estrellas (ej. `4.8 / 5`).

---

## 3. Gráficos Estadísticos con Chart.js

Incluyo la librería **Chart.js** para renderizar gráficos visuales y dinámicos:

### A. Ingresos por Día (Gráfico de Líneas)
* Muestra la curva de ventas de los últimos 7 días.
* Diseñado con un gradiente suave en tonos morados característicos de la marca.
* Permite identificar rápidamente qué días de la semana son los más rentables.

### B. Ingresos por Servicio (Gráfico de Dona)
* Muestra la cuota de participación de cada tratamiento en los ingresos totales (Lifting, Pelo a Pelo, Cejas, etc.).
* Al lado del gráfico hay una lista de leyendas personalizada con puntos de color y el porcentaje exacto calculado dinámicamente con `@php`:
  ```blade
  $pctServ = round(($valorServ / $sumaServ) * 100);
  ```

### C. Citas por Estado (Gráfico de Distribución)
* Representa visualmente la proporción entre citas *Completadas*, *Pendientes* y *Canceladas*.
* Ayuda a detectar si la tasa de cancelaciones está dentro de los límites saludables.

---

## 4. Tabla de Resumen Comparativo por Período

Debajo de los gráficos coloqué una tabla comparativa muy detallada que muestra:
* La métrica analizada (Ingresos, Citas, Clientes nuevos, Completadas, Canceladas, Promedio de estrellas).
* El valor del **Período Actual**.
* El valor del **Período Anterior**.
* La **Variación porcentual** con flechitas verdes hacia arriba (`trend-up`) para incrementos positivos y rojas hacia abajo (`trend-down`) para reducciones.

---

## 5. Tarjetas de Información Destacada (Highlights)

En la esquina inferior derecha creé dos bloques que sintetizan los mejores momentos comerciales del estudio:

* **Mejor Día de la Semana:** Identifica el día con el pico más alto de asistencia (ej. *Sábado*).
* **Mejor Franja Horaria:** Señala las horas de mayor concurrencia (ej. *11:00 am - 1:00 pm*), permitiendo planificar mejor los turnos del personal.

---

## En resumen: ¿Por qué es tan valiosa esta vista?
Permite saber exactamente qué servicios dejan más ganancias, cuáles días conviene lanzar promociones y cómo evoluciona el crecimiento del salón mes a mes.
