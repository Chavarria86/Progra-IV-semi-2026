<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\CodigoRecuperacionMail;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'contrasena' => 'required'
        ]);

        $correo = $request->correo;
        $contrasena = $request->contrasena;

        $usuario = null;
        $rol = null;

        // 1. Buscar en tabla usuarios (Pasantes)
        $usuarioDb = DB::table('usuarios')->where('correo_institucional', $correo)->first();

        if ($usuarioDb && Hash::check($contrasena, $usuarioDb->password)) {
            $usuario = [
                'id' => $usuarioDb->id,
                'nombres' => $usuarioDb->nombres,
                'apellidos' => $usuarioDb->apellidos,
                'correo' => $usuarioDb->correo_institucional
            ];
            $rol = 'pasante';

            // Regla: Si es pasante, su correo debe empezar con "usss"
            if (!Str::startsWith(strtolower($correo), 'usss')) {
                return response()->json(['mensaje' => 'El correo de pasante debe iniciar con "usss".'], 400);
            }
        }

        // 2. Buscar en tabla personal_administrativo (Supervisores / Vice Decano)
        if (!$usuario) {
            $personalDb = DB::table('personal_administrativo')->where('correo_institucional', $correo)->first();

            if ($personalDb && Hash::check($contrasena, $personalDb->password)) {
                $usuario = [
                    'id' => $personalDb->id,
                    'nombres' => $personalDb->nombres,
                    'apellidos' => $personalDb->apellidos,
                    'correo' => $personalDb->correo_institucional
                ];
                
                $cargo = strtolower($personalDb->cargo);
                if (str_contains($cargo, 'decano')) {
                    $rol = 'vice_decano';
                } else {
                    $rol = 'supervisor';
                }
            }
        }

        if (!$usuario || !$rol) {
            return response()->json(['mensaje' => 'Correo o contraseña incorrectos.'], 401);
        }

        $usuario['rol'] = $rol;

        // Rutas de redirección de Vue
        $rutas = [
            'pasante' => '/dashboard/pasante',
            'supervisor' => '/dashboard/supervisor',
            'vice_decano' => '/dashboard/vicedecano'
        ];

        // Usamos una sesión nativa de Laravel para mantener estado (SPA Authentication con Sanctum o session)
        // Por simplicidad, devolveremos los datos para que Vue los maneje en localStorage.
        return response()->json([
            'mensaje' => 'Inicio de sesión exitoso.',
            'usuario' => $usuario,
            'redireccion' => $rutas[$rol] ?? '/login'
        ]);
    }

    public function registro(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'contrasena' => 'required|string|min:4'
        ]);

        $correo = strtolower($request->correo);

        if (!Str::startsWith($correo, 'usss')) {
            return response()->json(['mensaje' => 'El correo de pasante debe iniciar con "usss".'], 400);
        }

        // 1. Validar que el estudiante exista en la base de datos institucional (usando el correo secundario)
        $estudiante = DB::table('estudiante')->where('correo_secundario', $correo)->first();
        
        if (!$estudiante) {
            return response()->json(['mensaje' => 'Este correo no pertenece a un estudiante activo matriculado.'], 400);
        }

        // 2. Verificar si ya se le creó una cuenta en la tabla 'usuarios'
        $existe = DB::table('usuarios')->where('correo_institucional', $correo)->exists();
        if ($existe) {
            return response()->json(['mensaje' => 'Esta cuenta ya está registrada. Por favor inicia sesión.'], 400);
        }

        // 3. Insertar usuario usando los nombres oficiales de la base de datos de estudiantes
        $usuarioId = DB::table('usuarios')->insertGetId([
            'nombres' => $estudiante->nombre,
            'apellidos' => $estudiante->apellido,
            'correo_institucional' => $correo,
            'password' => Hash::make($request->contrasena),
            'estado' => 1,
            'fecha_registro' => now()
        ]);

        // 4. Crear registro asociado en la tabla pasantes automáticamente
        DB::table('pasantes')->insert([
            'usuario_id' => $usuarioId,
            'area' => $estudiante->carrera, // Podemos guardar la carrera como el área inicial
            'tipo_pasantia' => 'Por definir',
            'estado' => 'en_proceso',
            'fase_actual' => 'Pendiente',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['mensaje' => 'Cuenta verificada y creada con éxito. Ahora puedes iniciar sesión.']);
    }

    public function enviarCodigo(Request $request)
    {
        $request->validate(['correo' => 'required|email']);
        $correo = strtolower($request->correo);

        // Verificar que el correo exista en usuarios o personal_administrativo
        $existeUser  = DB::table('usuarios')->where('correo_institucional', $correo)->exists();
        $existeAdmin = DB::table('personal_administrativo')->where('correo_institucional', $correo)->exists();

        if (!$existeUser && !$existeAdmin) {
            return response()->json(['mensaje' => 'No se encontró ninguna cuenta con ese correo.'], 404);
        }

        // Generar código de 6 dígitos en texto plano
        $codigoPlano = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // ✅ Hashear el código antes de guardarlo en la DB (nunca se guarda en texto plano)
        $codigoHasheado = Hash::make($codigoPlano);

        DB::table('recuperacion_codigos')->updateOrInsert(
            ['correo'    => $correo],
            ['codigo'    => $codigoHasheado,
             'creado_en' => now()]
        );

        // ✅ Intentar enviar el código al correo real del usuario
        $emailEnviado = false;
        try {
            Mail::to($correo)->send(new CodigoRecuperacionMail($codigoPlano, $correo));
            $emailEnviado = true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error enviando correo: ' . $e->getMessage());
        }

        // En producción: exigir que el email funcione
        if (!$emailEnviado && app()->environment('production')) {
            return response()->json([
                'mensaje' => 'No se pudo enviar el correo. Contacta al administrador.'
            ], 500);
        }

        // En desarrollo local: mostrar el código en pantalla si el email falló
        $respuesta = [
            'mensaje' => $emailEnviado
                ? '✅ Código enviado a tu correo institucional.'
                : '⚠️ SMTP no configurado. Usa el código de prueba mostrado abajo.'
        ];

        if (!$emailEnviado && app()->environment('local')) {
            $respuesta['codigo_dev'] = $codigoPlano; // Solo visible en modo desarrollo
            \Illuminate\Support\Facades\Log::info("=== CÓDIGO DEV para {$correo}: {$codigoPlano} ===");
        }

        return response()->json($respuesta);
    }

    public function recuperar(Request $request)
    {
        $request->validate([
            'correo'         => 'required|email',
            'codigo'         => 'required|string|size:6',
            'nuevaContrasena'=> 'required|string|min:4'
        ]);

        $correo = strtolower($request->correo);

        // Buscar el registro del código
        $registroCodigo = DB::table('recuperacion_codigos')->where('correo', $correo)->first();

        // Verificar que existe y que el código en texto plano coincide con el hash guardado
        if (!$registroCodigo || !Hash::check($request->codigo, $registroCodigo->codigo)) {
            return response()->json(['mensaje' => 'El código de verificación es incorrecto o ha expirado.'], 400);
        }

        // ✅ Verificar que el código no tenga más de 15 minutos
        $creadoEn = \Carbon\Carbon::parse($registroCodigo->creado_en);
        if ($creadoEn->diffInMinutes(now()) > 15) {
            DB::table('recuperacion_codigos')->where('correo', $correo)->delete();
            return response()->json(['mensaje' => 'El código ha expirado. Solicita uno nuevo.'], 400);
        }

        $nuevaPassword = Hash::make($request->nuevaContrasena);

        // Actualizar en tabla usuarios (pasantes)
        $actualizadoUsuarios = DB::table('usuarios')
            ->where('correo_institucional', $correo)
            ->update(['password' => $nuevaPassword]);

        // Si no se actualizó en usuarios, intentar en personal_administrativo
        if (!$actualizadoUsuarios) {
            DB::table('personal_administrativo')
                ->where('correo_institucional', $correo)
                ->update(['password' => $nuevaPassword]);
        }

        // Eliminar el código usado
        DB::table('recuperacion_codigos')->where('correo', $correo)->delete();

        return response()->json(['mensaje' => 'Contraseña restablecida con éxito. Ya puedes iniciar sesión.']);
    }
}
