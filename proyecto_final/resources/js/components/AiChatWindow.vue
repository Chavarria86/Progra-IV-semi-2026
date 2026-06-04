<template>
  <div class="ai-chat-workspace border">
    
    <!-- HISTORIAL / SIDEBAR IZQUIERDA -->
    <div class="ai-chat-sidebar border-end">
      <div class="sidebar-action-header p-3 border-bottom">
        <button class="btn btn-primary-custom w-100 py-2 d-flex align-items-center justify-content-center gap-2" @click="iniciarNuevoChat" :disabled="chatCargando">
          <i class="bi bi-plus-lg"></i>
          <span>Nuevo Chat</span>
        </button>
      </div>
      
      <div class="chats-history-scroll">
        <div v-if="chatCargando && chatsList.length === 0" class="text-center py-4">
          <span class="spinner-border spinner-border-sm text-primary"></span>
        </div>
        <div v-else-if="chatsList.length === 0" class="text-center py-4 text-muted small px-3">
          No tienes conversaciones anteriores.
        </div>
        <div v-else class="list-group list-group-flush">
          <button
            v-for="c in chatsList"
            :key="c.id"
            class="list-group-item list-group-item-action chat-history-item p-3 border-bottom text-start"
            :class="{ active: chatActivo?.id === c.id }"
            @click="seleccionarChat(c)"
          >
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="small fw-bold text-truncate title-text text-dark" style="max-width: 160px;">{{ c.titulo }}</span>
              <span class="badge bg-secondary-light text-primary" style="font-size: 9px; text-transform: uppercase;">{{ c.rol === 'vice_decano' ? 'Vicedecano' : c.rol }}</span>
            </div>
            <div class="text-muted d-flex justify-content-between align-items-center" style="font-size: 10px;">
              <span><i class="bi bi-calendar3 me-1"></i> {{ formatearFecha(c.updated_at) }}</span>
              <span><i class="bi bi-clock me-1"></i> {{ formatearHora(c.updated_at) }}</span>
            </div>
          </button>
        </div>
      </div>
    </div>

    <!-- AREA DE CONVERSACION -->
    <div class="ai-chat-content d-flex flex-column bg-light">
      
      <!-- HEADER DEL CHAT -->
      <div class="chat-content-header bg-white border-bottom p-3 d-flex align-items-center justify-content-between shadow-sm">
        <div class="d-flex align-items-center gap-3">
          <div class="robot-avatar-container">
            <i class="bi bi-robot"></i>
            <span class="status-online"></span>
          </div>
          <div>
            <h5 class="mb-0 fw-bold text-dark font-lora">Asistente Virtual de IA</h5>
            <div class="d-flex align-items-center gap-1 mt-1">
              <span class="badge bg-success-light text-success text-capitalize px-2 py-1" style="font-size: 11px;">
                Asistente de {{ labelRol }}
              </span>
              <span class="text-muted small">• Conectado a Gemini</span>
            </div>
          </div>
        </div>
        <div class="header-actions">
          <span class="text-muted small d-none d-md-inline-block"><i class="bi bi-shield-check text-success"></i> Servidor Seguro</span>
        </div>
      </div>

      <!-- CUERPO DE MENSAJES -->
      <div class="chat-messages-container p-4" id="chat-workspace-messages">
        <!-- Pantalla de bienvenida si no hay chat activo -->
        <div v-if="!chatActivo" class="welcome-screen text-center py-5">
          <div class="welcome-icon-box mb-4 animate-pulse">
            <i class="bi bi-robot"></i>
          </div>
          <h3 class="fw-bold font-lora text-dark">¡Hola! Soy tu Asistente Inteligente UGB</h3>
          <p class="text-muted mx-auto" style="max-width: 500px;">
            Estoy entrenado con los reglamentos, objetivos y requerimientos de pasantías de la Universidad Gerardo Barrios.
            Elige una conversación del panel izquierdo o crea una nueva para comenzar.
          </p>
          <button class="btn btn-primary-custom px-4 py-2 mt-3" @click="iniciarNuevoChat">
            <i class="bi bi-chat-left-dots me-2"></i> Comenzar Nueva Conversación
          </button>
        </div>

        <div v-else class="messages-flow">
          <!-- Mensaje de bienvenida inicial según el rol del usuario -->
          <div class="message-wrapper assistant-msg mb-4">
            <div class="message-bubble shadow-sm">
              <div class="bubble-header d-flex justify-content-between align-items-center mb-2">
                <span class="fw-bold text-primary text-capitalize small"><i class="bi bi-robot me-1"></i> Asistente de IA (UGB)</span>
                <span class="text-muted" style="font-size: 10px;">Mensaje del Sistema</span>
              </div>
              <div class="bubble-body text-dark">
                <p class="mb-0" v-if="usuario.rol === 'pasante'">
                  ¡Hola, <strong>{{ usuario.nombres }}</strong>! Soy tu Asesor de IA de la UGB. Puedo ayudarte con sugerencias para mejorar tu CV, redactar tus informes mensuales de horas, responder dudas sobre tu especialidad técnica de informática o guiarte sobre cómo aplicar a las vacantes del sistema. ¿En qué te colaboro hoy?
                </p>
                <p class="mb-0" v-else-if="usuario.rol === 'supervisor'">
                  Estimado supervisor, bienvenido. Soy su Asistente IA de Pasantías. Puedo apoyarle con sugerencias constructivas para la retroalimentación de sus estudiantes, redacción de cartas de recomendación, validación de currículums o resolver dudas sobre el uso del portal. ¿En qué le asisto?
                </p>
                <p class="mb-0" v-else>
                  Bienvenido al canal administrativo, Dr. Méndez. Soy su asistente estratégico. Puedo apoyarle en la interpretación de indicadores, distribución de carga entre supervisores y optimización del flujo global. ¿Qué tema desea abordar?
                </p>
              </div>
            </div>
          </div>

          <!-- Historial de mensajes -->
          <div 
            v-for="m in mensajesList" 
            :key="m.id" 
            class="message-wrapper mb-4 animate-fade-in"
            :class="m.remitente === 'user' ? 'user-msg' : 'assistant-msg'"
          >
            <div class="message-bubble shadow-sm">
              <div class="bubble-header d-flex justify-content-between align-items-center mb-2">
                <span class="fw-bold text-capitalize small" :class="m.remitente === 'user' ? 'text-white' : 'text-primary'">
                  <i class="bi me-1" :class="m.remitente === 'user' ? 'bi-person' : 'bi-robot'"></i>
                  {{ m.remitente === 'user' ? 'Tú' : 'Asistente de IA (UGB)' }}
                </span>
                <span style="font-size: 9px;" :class="m.remitente === 'user' ? 'text-white-50' : 'text-muted'">
                  {{ formatearFechaHora(m.created_at) }}
                </span>
              </div>

              <!-- Badge de adjunto si existe en el mensaje -->
              <div v-if="m.cv_id || m.informe_id || m.vacante_id" class="attachment-badge-msg p-2 mb-2 rounded bg-opacity-25 border d-flex align-items-center gap-2 small" :class="m.remitente === 'user' ? 'bg-light text-white border-white-50' : 'bg-primary text-primary border-primary-50'">
                <i class="bi" :class="obtenerIconoMensajeAdjunto(m)"></i>
                <span class="text-truncate">
                  Archivo analizado: <strong>{{ obtenerTextoMensajeAdjunto(m) }}</strong>
                </span>
              </div>

              <div class="bubble-body pre-wrap" :class="m.remitente === 'user' ? 'text-white' : 'text-dark'">{{ m.contenido }}</div>
            </div>
          </div>

          <!-- Spinner escribiendo -->
          <div class="message-wrapper assistant-msg mb-4" v-if="enviandoMensaje">
            <div class="message-bubble shadow-sm py-3 px-4 bg-white">
              <div class="d-flex align-items-center gap-3">
                <span class="spinner-grow spinner-grow-sm text-primary"></span>
                <span class="text-muted small">El Asistente de IA está analizando tu consulta...</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- BARRA DE PREVISUALIZACION DE ADJUNTO SELECCIONADO -->
      <div v-if="adjuntoSeleccionado" class="attachment-preview-bar px-3 py-2 bg-warning bg-opacity-10 border-top d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2 small text-dark">
          <i class="bi" :class="iconoAdjunto(adjuntoSeleccionado.tipo) + ' text-warning fs-5'"></i>
          <span>Archivo adjunto para análisis de IA: <strong>{{ adjuntoSeleccionado.nombre }}</strong></span>
        </div>
        <button type="button" class="btn btn-sm text-danger p-0 border-0" @click="quitarAdjunto" title="Remover archivo">
          <i class="bi bi-x-circle-fill fs-5"></i>
        </button>
      </div>

      <!-- INPUT DE TEXTO -->
      <div class="chat-content-footer bg-white border-top p-3 shadow-sm" v-if="chatActivo">
        <form @submit.prevent="enviarMensajeIa" class="chat-input-form-box d-flex gap-2 align-items-center">
          
          <!-- Dropdown personalizado de adjuntos -->
          <div class="dropdown-container">
            <button 
              type="button" 
              class="btn btn-outline-primary btn-attach rounded-circle d-flex align-items-center justify-content-center"
              style="width: 44px; height: 44px;"
              @click="toggleDropdownAdjuntos"
              :disabled="enviandoMensaje"
              title="Adjuntar archivo del proyecto para análisis de IA"
            >
              <i class="bi bi-paperclip fs-5"></i>
            </button>

            <!-- Dropdown Menu (Vue Custom Toggle) -->
            <div v-if="mostrarDropdownAdjuntos" class="custom-attach-dropdown shadow border rounded p-2 bg-white">
              <div class="d-flex justify-content-between align-items-center px-2 py-1 border-bottom">
                <span class="fw-bold text-dark small">Adjuntos del Proyecto</span>
                <button type="button" class="btn-close" style="font-size: 10px;" @click="mostrarDropdownAdjuntos = false"></button>
              </div>

              <div class="dropdown-scroll-area py-1">
                <!-- Seccion CVs -->
                <div v-if="usuario.rol === 'pasante' || usuario.rol === 'supervisor'">
                  <div class="dropdown-header text-primary fw-bold px-2 py-1 small">
                    <i class="bi bi-file-earmark-person me-1"></i> Currículums Vitae
                  </div>
                  <div v-if="adjuntosCvs.length === 0" class="text-muted small px-3 py-1">No hay CVs creados</div>
                  <button 
                    v-for="cv in adjuntosCvs" :key="cv.id" 
                    type="button" 
                    class="dropdown-item small text-truncate py-1 px-3"
                    @click="seleccionarAdjunto(cv)"
                  >
                    {{ cv.nombre }}
                  </button>
                  <hr class="my-1">
                </div>

                <!-- Seccion Informes -->
                <div>
                  <div class="dropdown-header text-primary fw-bold px-2 py-1 small">
                    <i class="bi bi-file-earmark-text me-1"></i> Informes de Horas
                  </div>
                  <div v-if="adjuntosInformes.length === 0" class="text-muted small px-3 py-1">No hay Informes subidos</div>
                  <button 
                    v-for="inf in adjuntosInformes" :key="inf.id" 
                    type="button" 
                    class="dropdown-item small text-truncate py-1 px-3"
                    @click="seleccionarAdjunto(inf)"
                  >
                    {{ inf.nombre }}
                  </button>
                  <hr class="my-1">
                </div>

                <!-- Seccion Vacantes -->
                <div v-if="usuario.rol === 'supervisor' || usuario.rol === 'vice_decano'">
                  <div class="dropdown-header text-primary fw-bold px-2 py-1 small">
                    <i class="bi bi-briefcase me-1"></i> Vacantes del Portal
                  </div>
                  <div v-if="adjuntosVacantes.length === 0" class="text-muted small px-3 py-1">No hay Vacantes registradas</div>
                  <button 
                    v-for="vac in adjuntosVacantes" :key="vac.id" 
                    type="button" 
                    class="dropdown-item small text-truncate py-1 px-3"
                    @click="seleccionarAdjunto(vac)"
                  >
                    {{ vac.nombre }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Input principal -->
          <input 
            type="text" 
            class="form-control chat-input-element" 
            placeholder="Haz una consulta o adjunta un archivo para que la IA lo analice..."
            v-model="nuevoMensajeText"
            :disabled="enviandoMensaje"
            ref="inputMensaje"
            @focus="mostrarDropdownAdjuntos = false"
          >
          
          <button type="submit" class="btn btn-primary-custom px-4" :disabled="!nuevoMensajeText.trim() || enviandoMensaje" style="height: 44px; border-radius: 22px;">
            <i class="bi bi-send-fill me-1"></i>
            <span class="d-none d-sm-inline">Enviar</span>
          </button>
        </form>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps({
  usuario: { type: Object, required: true }
});

// Estados
const chatCargando = ref(false);
const chatsList = ref([]);
const chatActivo = ref(null);
const mensajesList = ref([]);
const nuevoMensajeText = ref('');
const enviandoMensaje = ref(false);
const inputMensaje = ref(null);

// Estados para adjuntos
const adjuntosCvs = ref([]);
const adjuntosInformes = ref([]);
const adjuntosVacantes = ref([]);
const adjuntoSeleccionado = ref(null); // { id, tipo, nombre }
const mostrarDropdownAdjuntos = ref(false);

const labelRol = computed(() => {
  if (props.usuario.rol === 'pasante') return 'Pasante';
  if (props.usuario.rol === 'supervisor') return 'Supervisor';
  if (props.usuario.rol === 'vice_decano') return 'Vicedecano';
  return 'Usuario';
});

onMounted(async () => {
  await cargarChats();
  await cargarAdjuntosDisponibles();
});

const cargarChats = async () => {
  if (!props.usuario.id) return;
  chatCargando.value = true;
  try {
    const res = await axios.get('/api/ai/chats', {
      headers: { 'X-User-Id': props.usuario.id }
    });
    chatsList.value = res.data.chats || [];
    if (chatsList.value.length > 0 && !chatActivo.value) {
      await seleccionarChat(chatsList.value[0]);
    }
  } catch (err) {
    console.error('Error al cargar chats de IA:', err);
  } finally {
    chatCargando.value = false;
  }
};

const cargarAdjuntosDisponibles = async () => {
  if (!props.usuario.id) return;
  try {
    const res = await axios.get('/api/ai/adjuntos-disponibles', {
      headers: { 'X-User-Id': props.usuario.id }
    });
    adjuntosCvs.value = res.data.cvs || [];
    adjuntosInformes.value = res.data.informes || [];
    adjuntosVacantes.value = res.data.vacantes || [];
  } catch (err) {
    console.error('Error al cargar adjuntos del proyecto:', err);
  }
};

const iniciarNuevoChat = async () => {
  if (!props.usuario.id) return;
  chatCargando.value = true;
  try {
    const res = await axios.post('/api/ai/chats', {}, {
      headers: { 'X-User-Id': props.usuario.id }
    });
    if (res.data.chat) {
      chatsList.value.unshift(res.data.chat);
      await seleccionarChat(res.data.chat);
    }
  } catch (err) {
    console.error('Error al iniciar nuevo chat de IA:', err);
    alertify.error('No se pudo iniciar una nueva sesión de chat.');
  } finally {
    chatCargando.value = false;
  }
};

const seleccionarChat = async (chat) => {
  chatActivo.value = chat;
  mensajesList.value = [];
  chatCargando.value = true;
  adjuntoSeleccionado.value = null;
  mostrarDropdownAdjuntos.value = false;
  try {
    const res = await axios.get(`/api/ai/chats/${chat.id}/mensajes`, {
      headers: { 'X-User-Id': props.usuario.id }
    });
    mensajesList.value = res.data.mensajes || [];
    hacerScrollMensajes();
    nextTick(() => {
      if (inputMensaje.value) {
        inputMensaje.value.focus();
      }
    });
  } catch (err) {
    console.error('Error al cargar mensajes del chat:', err);
    alertify.error('Error al cargar la conversación.');
  } finally {
    chatCargando.value = false;
  }
};

const enviarMensajeIa = async () => {
  if (!nuevoMensajeText.value.trim() || !chatActivo.value || enviandoMensaje.value) return;
  const texto = nuevoMensajeText.value.trim();
  nuevoMensajeText.value = '';
  enviandoMensaje.value = true;
  mostrarDropdownAdjuntos.value = false;

  const adjunto = adjuntoSeleccionado.value;

  // Insertar local para feedback inmediato
  mensajesList.value.push({
    id: Date.now(),
    remitente: 'user',
    contenido: texto,
    cv_id: adjunto && adjunto.tipo === 'cv' ? adjunto.id : null,
    informe_id: adjunto && adjunto.tipo === 'informe' ? adjunto.id : null,
    vacante_id: adjunto && adjunto.tipo === 'vacante' ? adjunto.id : null,
    created_at: new Date().toISOString()
  });
  hacerScrollMensajes();

  // Preparar payload de envío
  const payload = {
    mensaje: texto
  };
  if (adjunto) {
    if (adjunto.tipo === 'cv') payload.cv_id = adjunto.id;
    if (adjunto.tipo === 'informe') payload.informe_id = adjunto.id;
    if (adjunto.tipo === 'vacante') payload.vacante_id = adjunto.id;
  }

  // Quitar previsualización inmediatamente
  adjuntoSeleccionado.value = null;

  try {
    const res = await axios.post(`/api/ai/chats/${chatActivo.value.id}/enviar`, payload, {
      headers: { 'X-User-Id': props.usuario.id }
    });
    
    // Añadir respuesta de la IA
    if (res.data.mensaje_ia) {
      mensajesList.value.push(res.data.mensaje_ia);
    }
    hacerScrollMensajes();
  } catch (err) {
    console.error('Error al enviar mensaje a la IA:', err);
    alertify.error('Error al obtener respuesta de la IA.');
  } finally {
    enviandoMensaje.value = false;
  }
};

const hacerScrollMensajes = () => {
  nextTick(() => {
    setTimeout(() => {
      const container = document.getElementById('chat-workspace-messages');
      if (container) {
        container.scrollTop = container.scrollHeight;
      }
    }, 100);
  });
};

// Controladores del dropdown de adjuntos
const toggleDropdownAdjuntos = () => {
  mostrarDropdownAdjuntos.value = !mostrarDropdownAdjuntos.value;
};

const seleccionarAdjunto = (item) => {
  adjuntoSeleccionado.value = item;
  mostrarDropdownAdjuntos.value = false;
};

const quitarAdjunto = () => {
  adjuntoSeleccionado.value = null;
};

const iconoAdjunto = (tipo) => {
  if (tipo === 'cv') return 'bi-file-earmark-person-fill';
  if (tipo === 'informe') return 'bi-file-check-fill';
  if (tipo === 'vacante') return 'bi-building-fill';
  return 'bi-paperclip';
};

const obtenerIconoMensajeAdjunto = (m) => {
  if (m.cv_id) return 'bi-file-earmark-person-fill';
  if (m.informe_id) return 'bi-file-check-fill';
  if (m.vacante_id) return 'bi-building-fill';
  return 'bi-paperclip';
};

const obtenerTextoMensajeAdjunto = (m) => {
  if (m.cv_id) {
    const cv = adjuntosCvs.value.find(c => c.id === m.cv_id);
    return cv ? cv.nombre : 'Documento Currículum Vitae';
  }
  if (m.informe_id) {
    const inf = adjuntosInformes.value.find(i => i.id === m.informe_id);
    return inf ? inf.nombre : 'Reporte de Horas';
  }
  if (m.vacante_id) {
    const vac = adjuntosVacantes.value.find(v => v.id === m.vacante_id);
    return vac ? vac.nombre : 'Oferta Laboral';
  }
  return 'Archivo Adjunto';
};

// Formateadores de fecha y hora
const formatearFecha = (fechaStr) => {
  if (!fechaStr) return '';
  const d = new Date(fechaStr);
  return d.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
};

const formatearHora = (fechaStr) => {
  if (!fechaStr) return '';
  const d = new Date(fechaStr);
  return d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', hour12: true });
};

const formatearFechaHora = (fechaStr) => {
  if (!fechaStr) return '';
  const d = new Date(fechaStr);
  return d.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' }) + ' ' + d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', hour12: true });
};
</script>

<style scoped>
.ai-chat-workspace {
  display: flex;
  height: calc(100vh - 120px);
  background-color: #ffffff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

/* Sidebar izquierda: Historial */
.ai-chat-sidebar {
  width: 280px;
  background-color: #f8fafc;
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
}

.chats-history-scroll {
  flex: 1;
  overflow-y: auto;
}

.chat-history-item {
  background-color: transparent;
  border-left: 4px solid transparent;
  transition: all 0.2s ease;
}
.chat-history-item:hover {
  background-color: #f1f5f9;
}
.chat-history-item.active {
  background-color: #eff6ff;
  border-left-color: #001374;
}
.chat-history-item.active .title-text {
  color: #001374 !important;
}

.bg-secondary-light {
  background-color: #e2e8f0;
}

/* Panel central de conversación */
.ai-chat-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.chat-content-header {
  z-index: 5;
}

.robot-avatar-container {
  width: 44px;
  height: 44px;
  background: linear-gradient(135deg, #001374 0%, #0c29d1 100%);
  color: #ffffff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  position: relative;
  box-shadow: 0 4px 10px rgba(12, 41, 209, 0.2);
}

.status-online {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background-color: #22c55e;
  border: 2px solid #ffffff;
}

.font-lora {
  font-family: 'Lora', serif;
}

.bg-success-light {
  background-color: #d1fae5;
}

/* Cuerpo de mensajes */
.chat-messages-container {
  flex: 1;
  overflow-y: auto;
  background-color: #f1f5f9;
  display: flex;
  flex-direction: column;
}

.welcome-screen {
  margin: auto;
  max-width: 600px;
  padding: 30px;
}

.welcome-icon-box {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #001374 0%, #0c29d1 100%);
  color: #ffffff;
  border-radius: 20px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 40px;
  box-shadow: 0 10px 25px rgba(12, 41, 209, 0.25);
}

.animate-pulse {
  animation: welcomePulse 2s infinite;
}
@keyframes welcomePulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.04); }
  100% { transform: scale(1); }
}

.messages-flow {
  width: 100%;
}

.message-wrapper {
  display: flex;
  width: 100%;
}

.message-bubble {
  max-width: 80%;
  padding: 14px 18px;
  border-radius: 12px;
  line-height: 1.5;
}

.assistant-msg {
  justify-content: flex-start;
}
.assistant-msg .message-bubble {
  background-color: #ffffff;
  border-left: 4px solid #001374;
}

.user-msg {
  justify-content: flex-end;
}
.user-msg .message-bubble {
  background-color: #001374;
  border-radius: 12px;
}

.pre-wrap {
  white-space: pre-wrap;
  word-break: break-word;
}

/* Badges de adjuntos en los mensajes */
.attachment-badge-msg {
  margin-top: 2px;
  font-weight: 500;
}
.user-msg .attachment-badge-msg {
  background-color: rgba(255, 255, 255, 0.15);
  color: #ffffff;
  border-color: rgba(255, 255, 255, 0.25);
}
.assistant-msg .attachment-badge-msg {
  background-color: rgba(0, 19, 116, 0.05);
  color: #001374;
  border-color: rgba(0, 19, 116, 0.15);
}

/* Previsualización del adjunto arriba del formulario */
.attachment-preview-bar {
  border-bottom: 1px solid #e2e8f0;
  animation: slideDown 0.2s ease-out;
}
@keyframes slideDown {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Input footer */
.chat-content-footer {
  z-index: 5;
}

.chat-input-element {
  border-radius: 30px;
  padding: 12px 20px;
  border: 1px solid #cbd5e1;
  font-size: 14px;
}
.chat-input-element:focus {
  border-color: #001374;
  box-shadow: 0 0 0 3px rgba(0, 19, 116, 0.15);
}

.btn-primary-custom {
  background-color: #001374;
  color: #ffffff;
  border: none;
  border-radius: 30px;
  font-weight: 600;
  transition: all 0.2s ease;
}
.btn-primary-custom:hover:not(:disabled) {
  background-color: #010c67;
  transform: translateY(-1px);
}
.btn-primary-custom:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

/* Dropdown personalizado de adjuntos */
.dropdown-container {
  position: relative;
}

.btn-attach {
  border-color: #cbd5e1;
  color: #64748b;
  transition: all 0.2s ease;
}
.btn-attach:hover {
  border-color: #001374;
  color: #001374;
  background-color: rgba(0, 19, 116, 0.05);
}

.custom-attach-dropdown {
  position: absolute;
  bottom: 55px;
  left: 0;
  width: 280px;
  max-height: 350px;
  z-index: 50;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  animation: slideUp 0.2s ease-out;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(5px); }
  to { opacity: 1; transform: translateY(0); }
}

.dropdown-scroll-area {
  overflow-y: auto;
  flex: 1;
}

.custom-attach-dropdown .dropdown-item {
  width: 100%;
  background: none;
  border: none;
  text-align: left;
  transition: background-color 0.15s;
}
.custom-attach-dropdown .dropdown-item:hover {
  background-color: #f1f5f9;
  color: #001374;
}

/* Animación de entrada */
.animate-fade-in {
  animation: fadeInMsg 0.3s ease-out;
}
@keyframes fadeInMsg {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
  .ai-chat-sidebar {
    width: 80px;
  }
  .chat-history-item {
    padding: 10px !important;
    text-align: center !important;
  }
  .chat-history-item .title-text,
  .chat-history-item .text-muted,
  .chat-history-item .badge,
  .sidebar-action-header span {
    display: none !important;
  }
  .sidebar-action-header {
    padding: 10px !important;
  }
  .sidebar-action-header button {
    padding: 8px 0 !important;
  }
}
</style>
