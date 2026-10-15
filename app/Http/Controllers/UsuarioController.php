<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;


class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarios = User::with('role')->get();
        $roles = Role::all();

        // Conteos estadísticos
        $usuariosTotales = $usuarios->count();
        $usuariosActivos = User::where('estado', 'activo')->count();
        $usuariosInactivos = User::where('estado', 'inactivo')->count();
        $administradores = User::where('role_id', 1)->count();
        $trabajadores = User::where('role_id', 2)->count();
        $clientes = User::where('role_id', 3)->count();

        return view('admin.gusuarios.gusuarios', compact(
            'usuarios',
            'roles',
            'usuariosTotales',
            'usuariosActivos',
            'usuariosInactivos',
            'administradores',
            'trabajadores',
            'clientes'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         $roles = Role::all();
        return view('admin.gusuarios.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $usuario = new User;

        $usuario->role_id = $request->post('role_id');
        $usuario->name = $request->post('name');
        $usuario->apellido = $request->post('apellido');
        $usuario->documento = $request->post('documento');
        $usuario->telefono = $request->post('telefono');
        $usuario->email = $request->post('email');
        $usuario->password = Hash::make($request->post('password'));
        $usuario->estado = 'activo';
        $usuario->created_by = auth()->id();
        $usuario->save();

        if ($usuario->role_id == 3) {
            \App\Models\Cliente::firstOrCreate(['user_id' => $usuario->id]);
        } elseif (in_array($usuario->role_id, [1, 2])) {
            \App\Models\Trabajador::firstOrCreate(
                ['user_id' => $usuario->id],
                [
                    'especialidad' => $usuario->role_id == 1 ? 'Administrador / Máster' : 'Lashista Profesional',
                    'descripcion' => 'Especialista en belleza de la mirada',
                    'estado' => 'activo'
                ]
            );
        }

        return redirect()
            ->route('usuarios.index')
            ->with('success', '¡Usuario registrado correctamente!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $usuario = User::findOrFail($id);
        $usuario->name = $request->input('name');
        $usuario->apellido = $request->input('apellido');
        $usuario->documento = $request->input('documento');
        $usuario->telefono = $request->input('telefono');
        $usuario->email = $request->input('email');
        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->input('password'));
        }
        $usuario->role_id = $request->input('role_id');
        $usuario->estado = strtolower($request->input('estado', 'activo'));
        $usuario->save();

        // Sincronizar cliente o trabajador
        if ($usuario->role_id == 3) {
            \App\Models\Cliente::firstOrCreate(['user_id' => $usuario->id]);
            \App\Models\Trabajador::where('user_id', $usuario->id)->delete();
        } elseif (in_array($usuario->role_id, [1, 2])) {
            \App\Models\Trabajador::firstOrCreate(
                ['user_id' => $usuario->id],
                [
                    'especialidad' => $usuario->role_id == 1 ? 'Administrador / Máster' : 'Lashista Profesional',
                    'descripcion' => 'Especialista en belleza de la mirada',
                    'estado' => 'activo'
                ]
            );
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $usuario = User::findOrFail($id);
        
        // Evitar que el admin se elimine a sí mismo
        if (auth()->id() == $usuario->id) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta de administrador.');
        }

        // Eliminar relaciones hijas si existen
        \App\Models\Cliente::where('user_id', $usuario->id)->delete();
        \App\Models\Trabajador::where('user_id', $usuario->id)->delete();
        $usuario->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado exitosamente');
    }
}
