<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\User; // Asegúrate de tener esta importación
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // Importante para la clave por defecto
use Illuminate\Support\Str; // Para generar correos únicos
use Illuminate\Support\Facades\DB; // Para transacciones

class BarberController extends Controller
{
    /**
     * 1. LISTAR BARBEROS
     */
    public function index()
    {
        try {
            // Traemos los barberos con su usuario.
            $barbers = Barber::with(['user' => function($query) {
                $query->select('id', 'name', 'email'); 
            }])->get();

            return response()->json($barbers);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener barberos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 2. CONTRATAR (CREAR) UN BARBERO DIRECTAMENTE POR NOMBRE
     * ¡Esta es la lógica que querías!
     */
    public function store(Request $request)
    {
        // Validamos la info de la barbería
        $request->validate([
            'name'          => 'required|string|max:255', // El nombre que escribes directamente
            'rh'            => 'required|string|max:3',
            'eps'           => 'required|string|max:30',
            'specialty'     => 'nullable|string',
            'contract_type' => 'required|in:fijo,temporal,prestacion',
            'entry_time'    => 'required',
            'exit_time'     => 'required',
        ]);

        // Usamos una transacción por si acaso algo falla en la mitad
        return DB::transaction(function () use ($request) {
            try {
                // Generamos un correo genérico basado en el nombre para evitar colisiones
                $cleanName = Str::slug($request->name);
                $email = $cleanName . '@dkaizen.com';

                // Verificamos si ese correo ya existe (raro, pero posible)
                if (User::where('email', $email)->exists()) {
                    $email = $cleanName . '.' . rand(10,99) . '@dkaizen.com';
                }

                // 1. CREAMOS EL USUARIO AUTOMÁTICAMENTE
                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $email,
                    'password' => Hash::make('dkaizen123'), // Clave genérica
                    'role'     => 'barber', // Le asignamos el rol
                ]);

                // 2. CREAMOS EL REGISTRO DE BARBERO
                $barberData = $request->only([
                    'rh', 'eps', 'specialty', 'contract_type', 'entry_time', 'exit_time'
                ]);
                
                // Asignamos el ID del usuario que acabamos de crear
                $barberData['user_id'] = $user->id;

                $barber = Barber::create($barberData);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Barbero contratado con éxito. Su usuario se generó automáticamente.',
                    'data'    => $barber->load('user') // Recargamos para devolver los datos completos
                ], 201);

            } catch (\Exception $e) {
                return response()->json(['error' => 'Hubo un error al crear el usuario del barbero.' . $e->getMessage()], 500);
            }
        });
    }

    /**
     * 3. VER PERFIL (Este no cambia)
     */
    public function show($id)
    {
        $barber = Barber::with('user')->find($id);

        if (!$barber) {
            return response()->json(['message' => 'Barbero no encontrado'], 404);
        }

        return response()->json($barber);
    }

    /**
     * 4. ACTUALIZAR DATOS (Este no cambia, el admin puede actualizar info de barbería)
     */
    public function update(Request $request, $id)
{
    $barber = Barber::with('user')->find($id);

    if (!$barber) {
        return response()->json(['message' => 'Barbero no encontrado'], 404);
    }

    // Usamos una transacción para que se actualicen ambos o ninguno
    return \DB::transaction(function () use ($request, $barber) {
        // 1. Si mandas un nombre, actualizamos el nombre en la tabla USERS
        if ($request->has('name')) {
            $barber->user->update([
                'name' => $request->name
            ]);
        }

        // 2. Actualizamos el resto de info en la tabla BARBERS
        $barber->update($request->all());
        
        return response()->json([
            'success' => true,
            'message' => '¡Perfil de ' . $barber->user->name . ' actualizado!',
            'data'    => $barber->load('user')
        ]);
    });
}

    /**
     * 5. ELIMINAR (Este no cambia)
     */
    public function destroy($id)
    {
        $barber = Barber::find($id);

        if (!$barber) {
            return response()->json(['message' => 'Barbero no encontrado'], 404);
        }

        $barber->delete();
        
        return response()->json(['message' => 'Barbero eliminado correctamente']);
    }

    /**
     * YA NO NECESITAS EL MÉTODO availableUsers porque ya no seleccionamos usuarios existentes
     */
}