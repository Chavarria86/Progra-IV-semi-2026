<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\AiChat;
use App\Models\AiMessage;
use App\Models\CurriculumVitae;
use App\Models\Informe;
use App\Models\Vacante;
use App\Models\Pasante;
use App\Models\PersonalAdministrativo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{
    // Obtener chats del usuario
    public function getChats(Request $request)
    {
        $usuario_id = $request->header('X-User-Id');
        if (!$usuario_id) {
            return response()->json(['mensaje' => 'Usuario no especificado'], 400);
        }

        $chats = AiChat::where('usuario_id', $usuario_id)
            ->orderByDesc('updated_at')
            ->get();

        return response()->json(['chats' => $chats]);
    }

    // Crear un nuevo chat
    public function crearChat(Request $request)
    {
        $usuario_id = $request->header('X-User-Id');
        if (!$usuario_id) {
            return response()->json(['mensaje' => 'Usuario no especificado'], 400);
        }

        $usuario = Usuario::find($usuario_id);
        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        $rol = $usuario->rol;
        $titulos = [
            'pasante' => 'Asesoría de Pasantía',
            'supervisor' => 'Gestión de Tutoría',
            'vice_decano' => 'Análisis y Gestión'
        ];
        $titulo = $titulos[$rol] ?? 'Consulta General';
        $titulo .= ' (' . date('d M, H:i') . ')';

        $chat = AiChat::create([
            'usuario_id' => $usuario_id,
            'rol' => $rol,
            'titulo' => $titulo
        ]);

        return response()->json(['chat' => $chat]);
    }

    // Obtener mensajes de un chat
    public function getMensajes($chatId)
    {
        $chat = AiChat::find($chatId);
        if (!$chat) {
            return response()->json(['mensaje' => 'Chat no encontrado'], 404);
        }

        $mensajes = AiMessage::where('chat_id', $chatId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['mensajes' => $mensajes]);
    }

    // Enviar mensaje y obtener respuesta de IA (Gemini o Fallback)
    public function enviarMensaje(Request $request, $chatId)
    {
        $request->validate([
            'mensaje' => 'required|string|max:5000',
            'cv_id' => 'nullable|integer',
            'informe_id' => 'nullable|integer',
            'vacante_id' => 'nullable|integer',
        ]);

        $chat = AiChat::find($chatId);
        if (!$chat) {
            return response()->json(['mensaje' => 'Chat no encontrado'], 404);
        }

        $usuario_id = $request->header('X-User-Id');
        if ($chat->usuario_id != $usuario_id) {
            return response()->json(['mensaje' => 'No autorizado'], 403);
        }

        // 1. Guardar mensaje del usuario
        $mensajeUsuario = AiMessage::create([
            'chat_id' => $chat->id,
            'remitente' => 'user',
            'contenido' => $request->mensaje,
            'cv_id' => $request->cv_id,
            'informe_id' => $request->informe_id,
            'vacante_id' => $request->vacante_id,
        ]);

        // Actualizar timestamp del chat
        $chat->touch();

        // 2. Obtener historial de conversación
        $historialMensajes = AiMessage::where('chat_id', $chat->id)
            ->orderBy('created_at', 'asc')
            ->get();

        $apiKey = env('GEMINI_API_KEY');
        $respuestaTexto = '';

        if (!empty($apiKey)) {
            // Invocar a Gemini API real
            $respuestaTexto = $this->llamarGemini($chat->rol, $historialMensajes, $apiKey);
        }

        // Si falló la llamada a la API o no hay ApiKey, usar Fallback
        if (empty($respuestaTexto)) {
            $respuestaTexto = $this->generarRespuestaFallback($chat->rol, $request->mensaje, $mensajeUsuario);
        }

        // 3. Guardar mensaje del modelo
        $mensajeModelo = AiMessage::create([
            'chat_id' => $chat->id,
            'remitente' => 'model',
            'contenido' => $respuestaTexto
        ]);

        return response()->json([
            'mensaje_usuario' => $mensajeUsuario,
            'mensaje_ia' => $mensajeModelo
        ]);
    }

    // Llamar a la API de Gemini (Google AI Studio)
    private function llamarGemini($rol, $historial, $apiKey)
    {
        $systemPrompts = [
            'pasante' => "Eres el Asistente de IA oficial de Pasantías de la Universidad Gerardo Barrios (UGB). Tu objetivo es aconsejar y guiar al estudiante (Pasante) en su proceso. Puedes dar sugerencias sobre la redacción de su CV (palabras clave, formato), explicar cómo redactar sus objetivos y actividades en los informes mensuales de horas, cómo aplicar a vacantes y responder dudas de informática y desarrollo web (Laravel, Vue, Postgres, CSS). También debes asistir al usuario en la creación, revisión, verificación de ortografía y corrección gramatical de documentos (como informes, cartas de presentación y CVs). Sé muy amable, motivador y profesional. Si el usuario te comparte un CV o informe adjunto, analízalo con detalle y bríndale retroalimentación constructiva. IMPORTANTE: Tus respuestas deben ser limpias, claras y sumamente ordenadas, sin agregar símbolos innecesarios ni exceso de marcas markdown (evita el abuso de asteriscos, hashtags o formatos extraños). Entrega solo lo que es útil y necesario para el usuario de manera directa.",
            
            'supervisor' => "Eres el Asistente de IA oficial de Pasantías de la Universidad Gerardo Barrios (UGB) enfocado en Supervisores. Tu objetivo es apoyar al supervisor académico en su labor de tutoría y evaluación. Ayúdale a redactar observaciones constructivas para informes de estudiantes, a evaluar el avance y horas validadas, a estructurar y corregir cartas de recomendación, y a proponer sugerencias de vacantes laborales adecuadas. También asiste en la creación, revisión de ortografía y corrección gramatical de cualquier documento relacionado. Responde de forma ejecutiva, formal y clara. Si se adjunta un CV o informe de un estudiante bajo su tutela, bríndele un análisis del progreso, áreas de mejora y sugerencias de correcciones. IMPORTANTE: Tus respuestas deben ser limpias, claras y sumamente ordenadas, sin agregar símbolos innecesarios ni exceso de marcas markdown (evita el abuso de asteriscos, hashtags o formatos extraños). Entrega solo lo que es útil y necesario para el usuario de manera directa.",
            
            'vice_decano' => "Eres el Asistente de IA oficial de Pasantías de la Universidad Gerardo Barrios (UGB) enfocado en la gestión administrativa del Vicedecano. Tu rol es asistirle en la toma de decisiones estratégicas, análisis de estadísticas de pasantías, asignación óptima de supervisores y administración general del flujo del portal. Asiste también en la redacción, corrección ortográfica y gramatical de comunicados y documentos del portal. Mantén un tono formal, analítico y enfocado en la excelencia académica. Si se adjunta un reporte de informe o vacante, analícelo con visión de optimización del portal y convenios empresariales. IMPORTANTE: Tus respuestas deben ser limpias, claras y sumamente ordenadas, sin agregar símbolos innecesarios ni exceso de marcas markdown (evita el abuso de asteriscos, hashtags o formatos extraños). Entrega solo lo que es útil y necesario para el usuario de manera directa."
        ];

        $promptSistema = $systemPrompts[$rol] ?? "Eres un asistente virtual de apoyo en el sistema de pasantías de la UGB.";

        // Formatear los contenidos para el body de Gemini
        $contents = [];
        foreach ($historial as $msg) {
            $textoFinal = $msg->contenido;
            
            // Si el mensaje tiene un adjunto, inyectamos el contexto de dicho adjunto en el prompt
            if ($msg->cv_id || $msg->informe_id || $msg->vacante_id) {
                $textoContexto = $this->formatearMensajeConContexto($msg);
                $textoFinal = $textoContexto . "\n\nPregunta o consulta original del usuario:\n" . $msg->contenido;
            }

            $contents[] = [
                'role' => $msg->remitente,
                'parts' => [
                    ['text' => $textoFinal]
                ]
            ];
        }

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $apiKey;
            
            $body = [
                'contents' => $contents,
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $promptSistema]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 4000
                ]
            ];

            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $body);

            if ($response->successful()) {
                $resData = $response->json();
                $texto = $resData['candidates'][0]['content']['parts'][0]['text'] ?? '';
                if (!empty($texto)) {
                    return $texto;
                }
            }

            Log::error('Error en respuesta de Gemini API: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Excepción al conectar con Gemini: ' . $e->getMessage());
        }

        return null;
    }

    // Dar formato al contexto del adjunto para que la IA lo entienda
    private function formatearMensajeConContexto(AiMessage $msg)
    {
        $contexto = "";

        if ($msg->cv_id) {
            $cv = CurriculumVitae::find($msg->cv_id);
            if ($cv) {
                $contexto .= "[CONTEXTO ADJUNTADO: CURRÍCULUM VITAE]\n";
                $contexto .= "Título del CV: " . ($cv->titulo_cv ?? 'No especificado') . "\n";
                $contexto .= "Nombre Completo: " . ($cv->nombre_completo ?? 'No especificado') . "\n";
                $contexto .= "Perfil/Sobre mí: " . ($cv->sobre_mi ?? 'No especificado') . "\n";
                $contexto .= "Educación: " . ($cv->educacion ?? 'No especificado') . "\n";
                $contexto .= "Objetivo Profesional: " . ($cv->objetivo ?? 'No especificado') . "\n";
                $contexto .= "Habilidades Técnicas: " . ($cv->habilidades ?? 'No especificado') . "\n";
                $contexto .= "Conocimientos: " . ($cv->conocimientos ?? 'No especificado') . "\n";
                $contexto .= "Idiomas: " . ($cv->idiomas ?? 'No especificado') . "\n";
                $contexto .= "Certificados: " . ($cv->certificados ?? 'No especificado') . "\n";
                $contexto .= "Proyectos/Logros: " . ($cv->proyectos_sociales ?? $cv->logros ?? 'No especificado') . "\n";
                $contexto .= "Color Plantilla/Diseño: " . ($cv->color_plantilla ?? 'Por defecto') . "\n";
                $contexto .= "--------------------------------------------------\n";
            }
        }

        if ($msg->informe_id) {
            $informe = Informe::with('pasante.usuario')->find($msg->informe_id);
            if ($informe) {
                $nombrePasante = $informe->pasante && $informe->pasante->usuario
                    ? $informe->pasante->usuario->nombres . ' ' . $informe->pasante->usuario->apellidos
                    : 'Estudiante';
                $contexto .= "[CONTEXTO ADJUNTADO: INFORME MENSUAL DE HORAS]\n";
                $contexto .= "Pasante: " . $nombrePasante . "\n";
                $contexto .= "Tipo de Informe: " . strtoupper($informe->tipo ?? 'parcial') . "\n";
                $contexto .= "Estado del Informe: " . ($informe->estado ?? 'pendiente') . "\n";
                $contexto .= "Fecha de Registro: " . ($informe->created_at ?? 'No especificada') . "\n";
                $contexto .= "Observaciones/Retroalimentación: " . ($informe->observaciones ?? 'Sin observaciones aún') . "\n";
                $contexto .= "--------------------------------------------------\n";
            }
        }

        if ($msg->vacante_id) {
            $vacante = Vacante::find($msg->vacante_id);
            if ($vacante) {
                $contexto .= "[CONTEXTO ADJUNTADO: VACANTE LABORAL DISPONIBLE]\n";
                $contexto .= "Empresa: " . ($vacante->empresa ?? 'No especificada') . "\n";
                $contexto .= "Área requerida: " . ($vacante->area ?? 'No especificada') . "\n";
                $contexto .= "Descripción y requisitos: " . ($vacante->descripcion ?? 'Sin descripción') . "\n";
                $contexto .= "Estado: " . ($vacante->estado ?? 'activa') . "\n";
                $contexto .= "--------------------------------------------------\n";
            }
        }

        return $contexto;
    }

    // Generar una respuesta simulada y contextualizada si no hay API Key activa o falla Gemini
    private function generarRespuestaFallback($rol, $mensaje, $msgObj = null)
    {
        $msgLower = mb_strtolower($mensaje);

        // 1. Si hay un CV adjunto, damos retroalimentación inteligente simulada sobre el CV
        if ($msgObj && $msgObj->cv_id) {
            $cv = CurriculumVitae::find($msgObj->cv_id);
            if ($cv) {
                $nombre = $cv->nombre_completo ?? 'Estudiante';
                $titulo = $cv->titulo_cv ?? 'Currículum';
                return "He analizado detenidamente tu currículum **\"{$titulo}\"** para el estudiante **{$nombre}**.\n\n" .
                       "**Análisis de Optimización (Simulación IA):**\n" .
                       "- **Estructura:** La organización de las secciones es adecuada y fácil de leer.\n" .
                       "- **Sobre mí:** Tu perfil profesional está orientado al rol, pero te sugiero ser más directo en tus metas de pasantía escolar.\n" .
                       "- **Habilidades y Conocimientos:** Se mencionan habilidades importantes, sin embargo, te aconsejo añadir palabras clave relevantes de desarrollo web y base de datos (tales como *Laravel*, *Vue.js*, *PostgreSQL* y *Tailwind CSS*) para aumentar tu tasa de coincidencia en el sistema.\n" .
                       "- **Puntaje estimado de impacto:** 85/100.\n\n" .
                       "¿Deseas que te recomiende cómo redactar mejor alguna sección específica de tu educación o proyectos?";
            }
        }

        // 2. Si hay un informe adjunto
        if ($msgObj && $msgObj->informe_id) {
            $informe = Informe::with('pasante.usuario')->find($msgObj->informe_id);
            if ($informe) {
                $tipo = strtoupper($informe->tipo ?? 'parcial');
                $estado = $informe->estado ?? 'pendiente';
                $observaciones = $informe->observaciones ?? 'Sin observaciones registradas por el supervisor.';
                
                $analisis = "He examinado el **Informe {$tipo}** adjunto.\n\n" .
                            "**Detalles del Informe:**\n" .
                            "- **Estado actual:** `{$estado}`\n" .
                            "- **Observaciones del supervisor:** *\"{$observaciones}\"*\n\n";

                if ($estado === 'correccion') {
                    $analisis .= "**Recomendación del Asistente:** Tu informe requiere atención. Te sugiero redactar de forma más específica las actividades realizadas, detallando las herramientas aplicadas. Por ejemplo, en lugar de 'avance de proyecto', escribe 'maquetado del frontend usando Vue y conexión a la base de datos PostgreSQL'. Una vez corregido, tu supervisor podrá aprobarlo para validar tus horas.";
                } elseif ($estado === 'aprobado') {
                    $analisis .= "**Recomendación del Asistente:** Tu informe ha sido aprobado con éxito. Excelente trabajo. Las horas correspondientes han sido validadas en tu expediente escolar de forma correcta.";
                } else {
                    $analisis .= "**Recomendación del Asistente:** El informe se encuentra pendiente de revisión. Te aconsejo esperar la evaluación de tu tutor asignado. Si tienes dudas sobre cómo sustentar tus horas, indícamelo.";
                }

                return $analisis;
            }
        }

        // 3. Si hay una vacante adjunta
        if ($msgObj && $msgObj->vacante_id) {
            $vacante = Vacante::find($msgObj->vacante_id);
            if ($vacante) {
                $empresa = $vacante->empresa ?? 'Empresa';
                $area = $vacante->area ?? 'Sistemas';
                return "He revisado la vacante de **{$empresa}** para el área de **{$area}**.\n\n" .
                       "**Análisis de la Vacante:**\n" .
                       "- **Requisitos clave:** Se orienta a labores de {$area}.\n" .
                       "- **Compatibilidad con Pasantes:** Los estudiantes de esta especialidad académica tienen una alta compatibilidad con el perfil buscado. Para los pasantes, les sugerimos asegurarse de tener su CV validado en Fase 2 antes de presionar 'Aplicar ahora'.\n" .
                       "- **Consejo de postulación:** Es ideal destacar en tu CV proyectos prácticos relacionados con el área, así como tu manejo de base de datos relacionales en PostgreSQL y frameworks backend como Laravel.\n\n" .
                       "¿Te gustaría que redactemos una carta de presentación o revisemos qué preguntas de entrevista suelen hacer para esta vacante?";
            }
        }

        // Fallback clásico por palabras clave si no hay adjuntos
        if ($rol === 'pasante') {
            if (str_contains($msgLower, 'cv') || str_contains($msgLower, 'curriculum') || str_contains($msgLower, 'vida')) {
                return "Para optimizar tu CV y pasar a la Fase 2, te sugiero incluir un buen resumen profesional ('Sobre mí') enfocado en tus metas, y separar tus habilidades en categorías (Desarrollo, Base de Datos, Blandas). Recuerda que tu supervisor revisará tu CV en el portal antes de habilitarte a aplicar a vacantes.";
            }
            if (str_contains($msgLower, 'informe') || str_contains($msgLower, 'horas') || str_contains($msgLower, 'reporte')) {
                return "Los informes mensuales deben ser muy específicos. En la sección de 'Actividades Realizadas', describe qué herramientas usaste y qué problemas resolviste (por ejemplo: 'Diseño e implementación de bases de datos relacionales en PostgreSQL'). Cada informe aprobado por tu supervisor sumará esas horas a tu expediente automáticamente.";
            }
            if (str_contains($msgLower, 'vacante') || str_contains($msgLower, 'empresa') || str_contains($msgLower, 'aplicar')) {
                return "En el menú de 'Vacantes' verás las ofertas activas publicadas por el Vicedecano. Si tu supervisor te sugiere una vacante, verás un foco amarillo indicándolo en la tarjeta. Solo debes presionar 'Aplicar ahora', adjuntar tu CV validado y esperar a que el supervisor apruebe tu postulación.";
            }
            return "Hola. Como tu Asistente IA de Pasantías de la UGB, te comento que estoy disponible para ayudarte a redactar tu currículum, estructurar tus informes mensuales de horas o darte consejos para tus postulaciones a empresas. ¿Qué duda específica tienes sobre tu pasantía?";
        }

        if ($rol === 'supervisor') {
            if (str_contains($msgLower, 'informe') || str_contains($msgLower, 'horas') || str_contains($msgLower, 'evaluar')) {
                return "Al evaluar informes en 'Control de Informes', puedes aprobarlos para sumar las horas al pasante o solicitar correcciones si el informe está incompleto. Recuerda que con el nuevo historial de informes revisados, puedes ingresar a las evaluaciones pasadas y actualizar las observaciones o cambiar el estado si es necesario.";
            }
            if (str_contains($msgLower, 'sugerir') || str_contains($msgLower, 'vacante') || str_contains($msgLower, 'sugerencia')) {
                return "Puedes ir a la nueva pestaña de 'Sugerir Vacantes' en tu menú lateral, donde verás el catálogo de ofertas activas. Presiona 'Sugerir a Pasante' en la vacante deseada, elige al estudiante y este recibirá una notificación visual para que aplique formalmente.";
            }
            if (str_contains($msgLower, 'cv') || str_contains($msgLower, 'validar')) {
                return "Recuerda que en 'Validar CVs' puedes revisar el archivo de los pasantes que acaban de iniciar. Al validar su currículum, el estudiante avanzará automáticamente de la Fase 1 a la Fase 2, lo que le permitirá aplicar a las vacantes del sistema.";
            }
            return "Estimado supervisor, estoy a su disposición para facilitarle la redacción de retroalimentaciones para sus estudiantes, guiarle en el uso de la pestaña de 'Sugerir Vacantes' o ayudarle a estructurar cartas de recomendación. ¿En qué le puedo asistir hoy?";
        }

        if ($rol === 'vice_decano') {
            if (str_contains($msgLower, 'estadistica') || str_contains($msgLower, 'indicador') || str_contains($msgLower, 'grafic') || str_contains($msgLower, 'reporte')) {
                return "El panel del Vicedecano cuenta con estadísticas en tiempo real sobre la cantidad de pasantes en proceso, vacantes activas y el rendimiento por área (Desarrollo, Diseño, etc.). Esto facilita la toma de decisiones para gestionar convenios con nuevas empresas cooperantes.";
            }
            if (str_contains($msgLower, 'supervisor') || str_contains($msgLower, 'asignar')) {
                return "La asignación de supervisores se gestiona en 'Asignar Supervisores'. Es recomendable mantener una carga equitativa de estudiantes por supervisor académico para asegurar el correcto seguimiento del plan de trabajo y el control de informes.";
            }
            return "Hola, Dr. Méndez. Como asistente de gestión institucional, puedo ayudarle a analizar el progreso de las pasantías de este ciclo, sugerir mejoras en la asignación de supervisores o apoyarle con la redacción de políticas de práctica profesional. ¿Qué tema administrativo desea abordar?";
        }

        return "Hola, soy el asistente inteligente de Pasantías UGB. Estoy aquí para resolver tus inquietudes orientadas al proyecto. ¿En qué te puedo colaborar?";
    }

    // Obtener los adjuntos disponibles para el usuario según su rol
    public function getAdjuntosDisponibles(Request $request)
    {
        $usuario_id = $request->header('X-User-Id');
        if (!$usuario_id) {
            return response()->json(['mensaje' => 'Usuario no especificado'], 400);
        }

        $usuario = Usuario::find($usuario_id);
        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        $rol = $usuario->rol;
        $cvs = [];
        $informes = [];
        $vacantes = [];

        if ($rol === 'pasante') {
            // 1. CVs del pasante
            $cvs = CurriculumVitae::where('usuario_id', $usuario_id)
                ->where('estado', 'activo')
                ->get()
                ->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'tipo' => 'cv',
                        'nombre' => "CV: " . ($c->titulo_cv ?? 'Mi CV')
                    ];
                });

            // 2. Informes del pasante
            $pasante = Pasante::where('usuario_id', $usuario_id)->first();
            if ($pasante) {
                $informes = Informe::where('pasante_id', $pasante->id)
                    ->get()
                    ->map(function ($i) {
                        $fecha = date('d/m/Y', strtotime($i->created_at));
                        return [
                            'id' => $i->id,
                            'tipo' => 'informe',
                            'nombre' => "Informe " . strtoupper($i->tipo) . " ({$fecha}) - " . ucfirst($i->estado)
                        ];
                    });
            }
        } elseif ($rol === 'supervisor') {
            // 1. Resolver ID del supervisor en la tabla personal_administrativo
            $supervisor_id = $this->resolverSupervisorId($usuario_id);

            // 2. Buscar pasantes asignados
            $pasantes = Pasante::where('supervisor_id', $supervisor_id)->with('usuario')->get();

            foreach ($pasantes as $p) {
                if (!$p->usuario) continue;
                
                // CVs de sus pasantes
                $pCvs = CurriculumVitae::where('usuario_id', $p->usuario_id)
                    ->where('estado', 'activo')
                    ->get()
                    ->map(function ($c) use ($p) {
                        return [
                            'id' => $c->id,
                            'tipo' => 'cv',
                            'nombre' => "CV de {$p->usuario->nombres}: " . ($c->titulo_cv ?? 'CV')
                        ];
                    })->toArray();
                $cvs = array_merge($cvs, $pCvs);

                // Informes de sus pasantes
                $pInformes = Informe::where('pasante_id', $p->id)
                    ->get()
                    ->map(function ($i) use ($p) {
                        $fecha = date('d/m/Y', strtotime($i->created_at));
                        return [
                            'id' => $i->id,
                            'tipo' => 'informe',
                            'nombre' => "Informe de {$p->usuario->nombres} (" . strtoupper($i->tipo) . ") - " . ucfirst($i->estado)
                        ];
                    })->toArray();
                $informes = array_merge($informes, $pInformes);
            }

            // 3. Vacantes activas
            $vacantes = Vacante::where('estado', 'activa')
                ->get()
                ->map(function ($v) {
                    return [
                        'id' => $v->id,
                        'tipo' => 'vacante',
                        'nombre' => "Vacante: {$v->empresa} ({$v->area})"
                    ];
                });
        } elseif ($rol === 'vice_decano') {
            // Vicedecano puede ver todos los informes y vacantes del sistema
            $vacantes = Vacante::get()
                ->map(function ($v) {
                    return [
                        'id' => $v->id,
                        'tipo' => 'vacante',
                        'nombre' => "Vacante: {$v->empresa} ({$v->area}) - " . ucfirst($v->estado)
                    ];
                });

            $informes = Informe::with('pasante.usuario')
                ->get()
                ->map(function ($i) {
                    $nombrePasante = $i->pasante && $i->pasante->usuario
                        ? $i->pasante->usuario->nombres . ' ' . $i->pasante->usuario->apellidos
                        : 'Estudiante';
                    $fecha = date('d/m/Y', strtotime($i->created_at));
                    return [
                        'id' => $i->id,
                        'tipo' => 'informe',
                        'nombre' => "Informe de {$nombrePasante} (" . strtoupper($i->tipo) . ") - " . ucfirst($i->estado)
                    ];
                });
        }

        return response()->json([
            'cvs' => $cvs,
            'informes' => $informes,
            'vacantes' => $vacantes
        ]);
    }

    private function resolverSupervisorId($usuarioId)
    {
        $usuario = Usuario::find($usuarioId);
        if ($usuario) {
            $admin = PersonalAdministrativo::where('correo_institucional', $usuario->correo_institucional)->first();
            if ($admin) return $admin->id;
        }
        $admin = PersonalAdministrativo::find($usuarioId);
        if ($admin) return $admin->id;
        $primerSupervisor = PersonalAdministrativo::where('cargo', 'supervisor')->first();
        return $primerSupervisor ? $primerSupervisor->id : 1;
    }
}
