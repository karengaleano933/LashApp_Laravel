<!-- Incluir Header superior de Admin -->
@include('layouts.header')
@include('layouts.sidebaradmin')

<!-- Contenido Principal -->
<div class="page-content" id="usuariosContainer" data-base-url="{{ url('/admin/usuarios') }}">

<!-- ══════════════════ ALERTAS DE ACCIÓN ══════════════════ -->
@if (session('success'))
    <div class="alert alert-success" style="padding: 14px 18px; border-radius: 12px; background: #eaf6ef; color: #2e6946; border: 1px solid #cbe9d6; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 500;">
        <i class="fa-solid fa-circle-check" style="font-size: 18px; color: #16a34a;"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger" style="padding: 14px 18px; border-radius: 12px; background: #fdf2f2; color: #991b1b; border: 1px solid #fecaca; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 500;">
        <i class="fa-solid fa-circle-exclamation" style="font-size: 18px; color: #dc2626;"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if (isset($errors) && $errors->any())
    <div class="alert alert-danger" style="padding: 14px 18px; border-radius: 12px; background: #fdf2f2; color: #991b1b; border: 1px solid #fecaca; margin-bottom: 20px;">
        <ul style="margin: 0; padding-left: 18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- ══════════════════ ENCABEZADO ══════════════════ -->
<div class="usuarios-header">
    <div class="usuarios-titulo">
        <div class="usuarios-icono">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <h1>Usuarios</h1>
            <p>Gestiona los usuarios del sistema y sus permisos</p>
        </div>
    </div>
</div>

<!-- ==========================================
     TARJETAS DE ESTADÍSTICAS
========================================== -->
<div class="usuarios-cards">

    <div class="usuario-card">
        <div class="usuario-card-icon purple">
            <i class="fa-solid fa-users"></i>
        </div>
        <div class="usuario-card-body">
            <span>Usuarios Totales</span>
            <strong>{{ $usuariosTotales ?? 0 }}</strong>
            <small class="purple-text">
                <i class="fa-solid fa-arrow-trend-up"></i> Registrados
            </small>
        </div>
    </div>

    <div class="usuario-card">
        <div class="usuario-card-icon green">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <div class="usuario-card-body">
            <span>Usuarios Activos</span>
            <strong>{{ $usuariosActivos ?? 0 }}</strong>
            <small class="green-text">
                <i class="fa-solid fa-circle-check"></i> Activos
            </small>
        </div>
    </div>

    <div class="usuario-card">
        <div class="usuario-card-icon red">
            <i class="fa-solid fa-user-xmark"></i>
        </div>
        <div class="usuario-card-body">
            <span>Usuarios Inactivos</span>
            <strong>{{ $usuariosInactivos ?? 0 }}</strong>
            <small class="red-text">
                <i class="fa-solid fa-circle-xmark"></i> Inactivos
            </small>
        </div>
    </div>

    <div class="usuario-card">
        <div class="usuario-card-icon blue">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div class="usuario-card-body">
            <span>Administradores</span>
            <strong>{{ $administradores ?? 0 }}</strong>
            <small class="blue-text">Sin cambios</small>
        </div>
    </div>

    <div class="usuario-card">
        <div class="usuario-card-icon purple">
            <i class="fa-solid fa-lock"></i>
        </div>
        <div class="usuario-card-body">
            <span>Último acceso</span>
            <strong class="ultimo-acceso">Hoy</strong>
            <small class="green-text">● En tiempo real</small>
        </div>
    </div>

</div>

<!-- ==========================================
     CONTENIDO PRINCIPAL
========================================== -->
<div class="usuarios-main-grid">

    <!-- ======================================
         TABLA DE USUARIOS
    ======================================= -->
    <div class="usuarios-tabla-card">

        <div class="usuarios-tabla-header">
            <h3>
                <i class="fa-solid fa-address-book"></i>
                Lista de Usuarios
            </h3>

            <div class="usuarios-header-actions">
                <select id="filtroRol">
                    <option value="">Todos los roles</option>
                    @foreach ($roles ?? [] as $r)
                        <option value="{{ $r->nombre }}">{{ ucfirst($r->nombre) }}</option>
                    @endforeach
                </select>

                <button type="button" class="btn-nuevo-usuario" id="abrirNuevoUsuario">
                    <i class="fa-solid fa-plus"></i> Nuevo Usuario
                </button>
            </div>
        </div>

        <!-- BUSCADOR -->
        <div class="usuarios-buscador">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="buscarUsuario" placeholder="Buscar por nombre, correo o rol...">
        </div>

        <!-- TABLA -->
        <div class="table-responsive">
            <table id="tablaUsuarios" class="usuarios-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Documento</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Fecha Registro</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($usuarios ?? [] as $usuario)
                        @php
                            $nombreCompleto = trim(($usuario->name ?? '') . ' ' . ($usuario->apellido ?? ''));
                            if (empty($nombreCompleto)) $nombreCompleto = 'Usuario';
                            $rolNombre = $usuario->role?->nombre ?? 'Sin rol';
                            $rolClase = strtolower(str_replace(' ', '', $rolNombre));
                            $estado = $usuario->estado ?? 'activo';
                            $estadoClase = strtolower($estado);
                            $inicial = strtoupper(substr($usuario->name ?? 'U', 0, 1));
                        @endphp
                        <tr data-rol="{{ $rolNombre }}">
                            <td data-label="Usuario">
                                <div class="usuario-info">
                                    <div class="usuario-avatar">
                                        {{ $inicial }}
                                    </div>
                                    <span>{{ $nombreCompleto }}</span>
                                </div>
                            </td>

                            <td data-label="Documento">{{ $usuario->documento ?? 'N/A' }}</td>

                            <td data-label="Teléfono">{{ $usuario->telefono ?? 'N/A' }}</td>

                            <td data-label="Correo">{{ $usuario->email ?? '' }}</td>

                            <td data-label="Rol">
                                <span class="rol-badge rol-{{ $rolClase }}">
                                    {{ ucfirst($rolNombre) }}
                                </span>
                            </td>

                            <td data-label="Estado">
                                <span class="estado-badge estado-{{ $estadoClase }}">
                                    {{ ucfirst($estado) }}
                                </span>
                            </td>

                            <td data-label="Fecha Registro">
                                {{ $usuario->created_at ? $usuario->created_at->format('d/m/Y, h:i a') : 'Sin fecha' }}
                            </td>

                            <td data-label="Acciones" class="text-center">
                                <button type="button" class="btn-accion btn-editar-usuario" 
                                    data-id="{{ $usuario->id }}" 
                                    data-nombre="{{ $usuario->name }}"
                                    data-apellido="{{ $usuario->apellido }}"
                                    data-documento="{{ $usuario->documento }}"
                                    data-telefono="{{ $usuario->telefono }}"
                                    data-correo="{{ $usuario->email }}"
                                    data-rol="{{ $usuario->role_id }}"
                                    data-estado="{{ $usuario->estado ?? 'activo' }}"
                                    title="Editar usuario">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                <button type="button" class="btn-accion btn-eliminar-usuario" 
                                    data-id="{{ $usuario->id }}" 
                                    data-nombre="{{ $nombreCompleto }}"
                                    title="Eliminar usuario">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 24px; color: var(--texto-sec);">
                                No hay usuarios registrados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <!-- ======================================
         PANEL DERECHO
    ======================================= -->
    <div class="usuarios-side">

        <!-- ROLES -->
        <div class="roles-permisos-card">
            <div class="roles-header">
                <h3>Roles y Permisos</h3>
            </div>

            <div class="rol-permiso">
                <div class="rol-icon admin">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <strong>Administrador</strong>
                    <span>Acceso completo al sistema</span>
                </div>
                <b>{{ $administradores ?? 0 }} {{ ($administradores ?? 0) == 1 ? 'usuario' : 'usuarios' }}</b>
            </div>

            <div class="rol-permiso">
                <div class="rol-icon recepcionista">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <strong>Trabajador</strong>
                    <span>Gestiona citas, clientes y servicios</span>
                </div>
                <b>{{ $trabajadores ?? 0 }} {{ ($trabajadores ?? 0) == 1 ? 'usuario' : 'usuarios' }}</b>
            </div>

            <div class="rol-permiso">
                <div class="rol-icon lashista">
                    <i class="fa-solid fa-heart"></i>
                </div>
                <div>
                    <strong>Cliente</strong>
                    <span>Agenda citas y solicita servicios</span>
                </div>
                <b>{{ $clientes ?? 0 }} {{ ($clientes ?? 0) == 1 ? 'usuario' : 'usuarios' }}</b>
            </div>
        </div>

    </div>

</div>

<!-- ==========================================
     MODAL CREAR NUEVO USUARIO
========================================== -->
<div class="modal-usuario-overlay" id="modalCrearUsuario" aria-hidden="true" role="dialog">
    <div class="modal-usuario-container">
        <div class="modal-usuario-header">
            <h3>
                <i class="fa-solid fa-user-plus"></i>
                Crear Nuevo Usuario
            </h3>
            <button type="button" class="btn-cerrar-modal" id="cerrarModalUsuario" aria-label="Cerrar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-usuario-body">
            <form action="{{ route('usuarios.store') }}" method="POST" id="formCrearUsuario">
                @csrf

                <div class="campo-usuario">
                    <label>Nombre <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="name" placeholder="Ej. Ana" required>
                </div>

                <div class="campo-usuario">
                    <label>Apellido <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="apellido" placeholder="Ej. Martínez" required>
                </div>

                <div class="campo-usuario">
                    <label>Documento <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="documento" placeholder="Número de documento" required>
                </div>

                <div class="campo-usuario">
                    <label>Teléfono <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="telefono" placeholder="Número de teléfono" required>
                </div>

                <div class="campo-usuario">
                    <label>Correo electrónico <span style="color:#ef4444;">*</span></label>
                    <input type="email" name="email" placeholder="ejemplo@ohlalash.com" required>
                </div>

                <div class="campo-usuario">
                    <label>Rol <span style="color:#ef4444;">*</span></label>
                    <select name="role_id" required>
                        <option value="" disabled selected>Selecciona un rol</option>
                        @foreach ($roles ?? [] as $r)
                            <option value="{{ $r->id }}">{{ ucfirst($r->nombre) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="campo-usuario">
                    <label>Contraseña <span style="color:#ef4444;">*</span></label>
                    <div class="password-input">
                        <input type="password" name="password" id="passwordUsuario" placeholder="Mínimo 6 caracteres" minlength="6" required>
                        <i class="fa-solid fa-eye" id="mostrarPassword"></i>
                    </div>
                </div>

                <div class="usuario-form-buttons">
                    <button type="button" class="btn-cancelar-usuario" id="cancelarModalUsuario">Cancelar</button>
                    <button type="submit" class="btn-crear-usuario">
                        <i class="fa-solid fa-user-plus"></i> Crear Usuario
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL EDITAR USUARIO
========================================== -->
<div class="modal-usuario-overlay" id="modalEditarUsuario" aria-hidden="true" role="dialog">
    <div class="modal-usuario-container">
        <div class="modal-usuario-header">
            <h3>
                <i class="fa-solid fa-user-pen"></i>
                Editar Usuario
            </h3>
            <button type="button" class="btn-cerrar-modal" id="cerrarModalEditar" aria-label="Cerrar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-usuario-body">
            <form id="formEditarUsuario" method="POST" action="">
                @csrf
                @method('PUT')

                <div class="campo-usuario">
                    <label>Nombre <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="name" id="edit_nombre" required>
                </div>

                <div class="campo-usuario">
                    <label>Apellido <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="apellido" id="edit_apellido" required>
                </div>

                <div class="campo-usuario">
                    <label>Documento <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="documento" id="edit_documento" required>
                </div>

                <div class="campo-usuario">
                    <label>Teléfono <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="telefono" id="edit_telefono" required>
                </div>

                <div class="campo-usuario">
                    <label>Correo electrónico <span style="color:#ef4444;">*</span></label>
                    <input type="email" name="email" id="edit_correo" required>
                </div>

                <div class="campo-usuario">
                    <label>Rol <span style="color:#ef4444;">*</span></label>
                    <select name="role_id" id="edit_id_rol" required>
                        @foreach ($roles ?? [] as $r)
                            <option value="{{ $r->id }}">{{ ucfirst($r->nombre) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="campo-usuario">
                    <label>Estado <span style="color:#ef4444;">*</span></label>
                    <select name="estado" id="edit_estado" required>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>

                <div class="campo-usuario">
                    <label>Contraseña (Opcional - dejar en blanco para mantener)</label>
                    <div class="password-input">
                        <input type="password" name="password" id="edit_password" placeholder="Nueva contraseña (opcional)">
                        <i class="fa-solid fa-eye" id="mostrarPasswordEdit"></i>
                    </div>
                </div>

                <div class="usuario-form-buttons">
                    <button type="button" class="btn-cancelar-usuario" id="cancelarModalEditar">Cancelar</button>
                    <button type="submit" class="btn-crear-usuario">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formulario invisible para Eliminar Usuario -->
<form id="formEliminarUsuario" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

</div> <!-- Cierre de page-content -->

@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper -->
</div> <!-- Cierre de .app-layout -->

<!-- JAVASCRIPT MODULAR (VITE) -->
@vite(['resources/js/modules/admin/gusuarios.js'])
</body>
</html>

