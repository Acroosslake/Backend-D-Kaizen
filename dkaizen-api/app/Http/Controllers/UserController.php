<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Barber; // ✅ Importamos el modelo Barber para la automatización
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Listar todos los usuarios del sistema.
     */
    public function index()
    {
        return response()->json(User::all());
    }

    /**
     * Actualizar datos de un usuario y gestionar perfiles de staff.
     */
    public function update(Request $request, $id)
    {
        // 🛡️ SEGURIDAD: No permitir que el admin se quite el rango a sí mismo
        if (auth()->id() == $id && $request->role !== 'admin') {
            return response()->json([
                'message' => 'No puedes quitarte el rango de Admin a ti mismo. Alguien debe mantener el orden.'
            ], 403);
        }

        $user = User::findOrFail($id);
        
        // 1. Guardamos el rol que tiene antes de la actualización
        $oldRole = $user->role;

        // 2. Actualizamos los datos básicos
        $user->update($request->only(['name', 'email', 'role']));


        // Si el rol cambió a 'barber', creamos automáticamente su perfil en la tabla de staff
        if ($user->role === 'barber' && $oldRole !== 'barber') {
            Barber::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'specialty' => 'Master Barber', // Valores por defecto para que aparezca en Staff
                    'status' => true,
                    'entry_time' => '09:00',
                    'exit_time' => '19:00',
                    'rh' => 'O+',
                    'eps' => 'Sura',
                    'contract_type' => 'fijo'
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente',
            'user' => $user
        ]);
    }

    /**
     * Eliminar un usuario (con protección de autosuicidio).
     */
    public function destroy($id)
    {
        // 🛡️ SEGURIDAD: No permitir que el admin se borre a sí mismo
        if (auth()->id() == $id) {
            return response()->json([
                'message' => 'No puedes eliminar tu propia cuenta, fiera. El sistema necesita al menos un admin.'
            ], 403);
        }

        $user = User::findOrFail($id);
        
        // Opcional: Si el usuario era barbero, podrías borrar su perfil de barbero también
        // Barber::where('user_id', $id)->delete();

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuario eliminado correctamente'
        ]);
    }
}