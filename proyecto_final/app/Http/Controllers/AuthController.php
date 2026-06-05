<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\CodigoRecuperacionMail;
use App\Models\Usuario;
use App\Models\PersonalAdministrativo;
use App\Models\Estudiante;
use App\Models\RecuperacionCodigo;
use App\Models\Pasante;

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

        // 1. Buscar en tabla usuarios (Tabla principal unificada)
        $usuarioDb = Usuario::where('correo_institucional', $correo)->first();

        if ($usuarioDb && Hash::check($contrasena, $usuarioDb->password)) {
            $usuario = [
                'id' => $usuarioDb->id,
                'nombres' => $usuarioDb->nombres,
                'apellidos' => $usuarioDb->apellidos,
                'correo' => $usuarioDb->correo_institucional
            ];
            $rol = $usuarioDb->rol; // Leer el rol directo de la BD

            // Regla: Si el rol es pasante, su correo debe empezar con "us"
            if ($rol === 'pasante' && !Str::startsWith(strtolower($correo), 'us')) {
                return response()->json(['mensaje' => 'El correo de pasante debe iniciar con "us".'], 400);
            }
        }

        // 2. Buscar en tabla personal_administrativo (Si no está en usuarios, caso heredado/alterno)
        if (!$usuario) {
            $personalDb = PersonalAdministrativo::where('correo_institucional', $correo)->first();

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

        // Si el correo empieza con "us" se trata de un Estudiante/Pasante
        if (Str::startsWith($correo, 'us')) {
            // 1. Validar que el estudiante exista en la base de datos institucional (usando el correo secundario)
            $estudiante = Estudiante::where('correo_secundario', $correo)->first();
            
            if (!$estudiante) {
                return response()->json(['mensaje' => 'Este correo no pertenece a un estudiante activo matriculado.'], 400);
            }

            // 2. Verificar si ya se le creó una cuenta en la tabla 'usuarios'
            $existe = Usuario::where('correo_institucional', $correo)->exists();
            if ($existe) {
                return response()->json(['mensaje' => 'Esta cuenta ya está registrada. Por favor inicia sesión.'], 400);
            }

             // 3. Insertar usuario usando los nombres oficiales de la base de datos de estudiantes
             $usuario = Usuario::create([
                 'nombres' => $estudiante->nombres,
                 'apellidos' => $estudiante->apellidos,
                 'correo_institucional' => $correo,
                 'password' => Hash::make($request->contrasena),
                 'estado' => 'activo',
                 'rol' => 'pasante',
                 'fecha_registro' => now()
             ]);
 
             // 4. Crear registro asociado en la tabla pasantes automáticamente
             Pasante::create([
                 'usuario_id' => $usuario->id,
                 'area' => $estudiante->carrera ?? 'Ingeniería en Sistemas', // Podemos guardar la carrera como el área inicial
                 'tipo_pasantia' => 'Por definir',
                 'estado' => 'en_proceso',
                 'fase_actual' => 'Pendiente',
             ]);
 
             return response()->json(['mensaje' => 'Cuenta de pasante verificada y creada con éxito. Ahora puedes iniciar sesión.']);
        } else {
            // Se trata de Personal Administrativo (Supervisor o Vicedecano)
            // 1. Validar que el personal exista en la base de datos institucional (personal_administrativo)
            $personal = PersonalAdministrativo::where('correo_institucional', $correo)->first();
            if (!$personal) {
                return response()->json(['mensaje' => 'Este correo no pertenece al personal administrativo registrado.'], 400);
            }

            // 2. Verificar si ya tiene cuenta en la tabla 'usuarios'
            $existeUser = Usuario::where('correo_institucional', $correo)->exists();
            if ($existeUser) {
                return response()->json(['mensaje' => 'Esta cuenta administrativa ya está registrada. Por favor inicia sesión.'], 400);
            }

            // 3. Determinar rol
            $rol = 'supervisor';
            if (str_contains($correo, 'decano') || str_contains($correo, 'vicedecano') || strtolower($personal->cargo) === 'vice_decano') {
                $rol = 'vice_decano';
            }

            // 4. Crear en tabla usuarios (tabla de login unificada)
            $usuario = Usuario::create([
                'nombres' => $personal->nombres,
                'apellidos' => $personal->apellidos,
                'correo_institucional' => $correo,
                'password' => Hash::make($request->contrasena),
                'estado' => 'activo',
                'rol' => $rol,
                'fecha_registro' => now()
            ]);

            // 5. Actualizar la contraseña en la tabla personal_administrativo para mantener sincronía
            $personal->update([
                'password' => Hash::make($request->contrasena)
            ]);

            return response()->json(['mensaje' => 'Cuenta de personal administrativo creada con éxito. Ahora puedes iniciar sesión.']);
        }
    }

    public function enviarCodigo(Request $request)
    {
        $request->validate(['correo' => 'required|email']);
        $correo = strtolower($request->correo);

        // Verificar que el correo exista en usuarios o personal_administrativo
        $existeUser  = Usuario::where('correo_institucional', $correo)->exists();
        $existeAdmin = PersonalAdministrativo::where('correo_institucional', $correo)->exists();

        if (!$existeUser && !$existeAdmin) {
            return response()->json(['mensaje' => 'No se encontró ninguna cuenta con ese correo.'], 404);
        }

        // Generar código de 6 dígitos en texto plano
        $codigoPlano = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // ✅ Hashear el código antes de guardarlo en la DB
        $codigoHasheado = Hash::make($codigoPlano);

        RecuperacionCodigo::updateOrCreate(
            ['correo'    => $correo],
            ['codigo'    => $codigoHasheado,
             'creado_en' => now()]
        );

        // ✅ Intentar enviar el código al correo real del usuario (usando un desvío si está configurado en .env)
        $emailEnviado = false;
        $destinatario = env('MAIL_TO_ADDRESS') ?: $correo;
        try {
            Mail::to($destinatario)->send(new CodigoRecuperacionMail($codigoPlano, $correo));
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
                ? 'Código enviado a tu correo institucional.'
                : 'SMTP no configurado. Usa el código de prueba mostrado abajo.'
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
        $registroCodigo = RecuperacionCodigo::where('correo', $correo)->first();

        // Verificar que existe y que el código en texto plano coincide con el hash guardado
        if (!$registroCodigo || !Hash::check($request->codigo, $registroCodigo->codigo)) {
            return response()->json(['mensaje' => 'El código de verificación es incorrecto o ha expirado.'], 400);
        }

        // ✅ Verificar que el código no tenga más de 15 minutos
        $creadoEn = \Carbon\Carbon::parse($registroCodigo->creado_en);
        if ($creadoEn->diffInMinutes(now()) > 15) {
            RecuperacionCodigo::where('correo', $correo)->delete();
            return response()->json(['mensaje' => 'El código ha expirado. Solicita uno nuevo.'], 400);
        }

        $nuevaPassword = Hash::make($request->nuevaContrasena);

        // Actualizar en tabla usuarios (pasantes y personal administrativo)
        $actualizadoUsuarios = Usuario::where('correo_institucional', $correo)
            ->update(['password' => $nuevaPassword]);

        // Actualizar también en la tabla personal_administrativo para mantener consistencia
        $actualizadoAdmin = PersonalAdministrativo::where('correo_institucional', $correo)
            ->update(['password' => $nuevaPassword]);

        if (!$actualizadoUsuarios && !$actualizadoAdmin) {
            return response()->json(['mensaje' => 'No se encontró la cuenta para actualizar la contraseña.'], 404);
        }

        // Eliminar el código usado
        RecuperacionCodigo::where('correo', $correo)->delete();

        return response()->json(['mensaje' => 'Contraseña restablecida con éxito. Ya puedes iniciar sesión.']);
    }

    public function verificarCodigo(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'codigo' => 'required|string|size:6'
        ]);

        $correo = strtolower($request->correo);

        // Buscar el registro del código
        $registroCodigo = RecuperacionCodigo::where('correo', $correo)->first();

        // Verificar que existe y que el código en texto plano coincide con el hash guardado
        if (!$registroCodigo || !Hash::check($request->codigo, $registroCodigo->codigo)) {
            return response()->json(['mensaje' => 'El código de verificación es incorrecto.'], 400);
        }

        // Verificar expiración (15 minutos)
        $creadoEn = \Carbon\Carbon::parse($registroCodigo->creado_en);
        if ($creadoEn->diffInMinutes(now()) > 15) {
            RecuperacionCodigo::where('correo', $correo)->delete();
            return response()->json(['mensaje' => 'El código ha expirado. Solicita uno nuevo.'], 400);
        }

        return response()->json(['mensaje' => 'Código verificado con éxito.']);
    }
}
