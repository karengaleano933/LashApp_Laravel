import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/login.css',
                'resources/css/register.css',
                'resources/css/dashboardadmin.css',
                'resources/css/sidebaradmin.css',
                'resources/css/footer.css',
                'resources/css/citas.css',
                'resources/css/usuarios.css',
                'resources/css/servicios.css',
                'resources/css/pagos.css',
                'resources/css/resenas.css',
                'resources/css/reportes.css',
                'resources/css/configuracion.css',
                'resources/css/dashboardcliente.css',
                'resources/css/catalogoclient.css',
                'resources/css/agendarcitas.css',
                'resources/css/miscitas.css',
                'resources/css/historilacliente.css',
                'resources/css/resenascliente.css',
                'resources/css/perfilcliente.css',
                'resources/css/dashboardtrabajador.css',
                'resources/css/citas_trabajador.css',
                'resources/css/servicio_trabajador.css',
            ],
            refresh: true,
        }),
    ],
});
