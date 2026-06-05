<template>
  <div class="chat-wrapper" :style="themeStyles">
    <div class="chat-container">
      
      <!-- SIDEBAR -->
      <aside class="chat-sidebar">
        <!-- Search and Filters -->
        <div class="sidebar-search-container">
          <div class="search-input-wrapper">
            <i class="bi bi-search"></i>
            <input 
              type="text" 
              placeholder="Buscar contacto..." 
              v-model="busqueda"
              class="search-input"
            />
          </div>
          
          <!-- Quick filters for supervisor/vicedecano -->
          <div class="filter-pills" v-if="usuario.rol !== 'pasante'">
            <button 
              :class="['filter-pill', { active: filtroRol === 'todos' }]" 
              @click="filtroRol = 'todos'"
            >
              Todos
            </button>
            <button 
              :class="['filter-pill', { active: filtroRol === 'pasantes' }]" 
              @click="filtroRol = 'pasantes'"
            >
              Pasantes
            </button>
            <button 
              :class="['filter-pill', { active: filtroRol === 'colegas' }]" 
              @click="filtroRol = 'colegas'"
            >
              Colegas
            </button>
          </div>
        </div>

        <!-- Connection Status -->
        <div class="connection-status" :class="{ 'connected': socketConnected, 'disconnected': !socketConnected && !socketError, 'error': socketError }">
          <span class="status-dot"></span>
          <span class="status-text">{{ statusText }}</span>
        </div>

        <!-- Contacts list -->
        <div class="contacts-list">
          <div v-if="cargandoContactos" class="contacts-state">
            <div class="spinner"></div>
            <p>Cargando contactos...</p>
          </div>
          
          <div v-else-if="filteredContacts.length === 0" class="contacts-state">
            <i class="bi bi-people"></i>
            <p>No se encontraron contactos</p>
          </div>
          
          <div 
            v-else
            v-for="contact in filteredContacts" 
            :key="contact.usuario_id"
            :class="['contact-card', { active: contactoSeleccionado?.usuario_id === contact.usuario_id }]"
            @click="seleccionarContacto(contact)"
          >
            <div class="contact-avatar" :style="{ background: getRoleColor(contact.rol) }">
              {{ getInitials(contact.nombre, contact.apellido) }}
            </div>
            
            <div class="contact-info">
              <div class="contact-meta">
                <span class="contact-name">{{ contact.nombre }} {{ contact.apellido }}</span>
                <span class="role-badge" :style="{ color: getRoleColor(contact.rol), backgroundColor: getRoleBgColor(contact.rol) }">
                  {{ contact.cargo }}
                </span>
              </div>
              <div class="contact-sub">
                <span class="contact-email">{{ contact.correo }}</span>
                <span v-if="contact.area" class="contact-area">| {{ contact.area }}</span>
              </div>
            </div>
          </div>
        </div>
      </aside>

      <!-- CONVERSATION WINDOW -->
      <main class="chat-main">
        <!-- Chat header -->
        <header v-if="contactoSeleccionado" class="chat-header">
          <div class="active-contact-profile">
            <div class="active-avatar" :style="{ background: getRoleColor(contactoSeleccionado.rol) }">
              {{ getInitials(contactoSeleccionado.nombre, contactoSeleccionado.apellido) }}
            </div>
            <div class="active-contact-details">
              <h3>{{ contactoSeleccionado.nombre }} {{ contactoSeleccionado.apellido }}</h3>
              <div class="active-contact-badge-row">
                <span class="role-badge" :style="{ color: getRoleColor(contactoSeleccionado.rol), backgroundColor: getRoleBgColor(contactoSeleccionado.rol) }">
                  {{ contactoSeleccionado.cargo }}
                </span>
                <span v-if="contactoSeleccionado.area" class="info-tag">
                  <i class="bi bi-briefcase"></i> {{ contactoSeleccionado.area }}
                </span>

              </div>
            </div>
          </div>
        </header>

        <!-- Chat messages area -->
        <div v-if="contactoSeleccionado" class="chat-body" ref="chatBody">
          <div v-if="mensajes.length === 0" class="no-messages-prompt">
            <i class="bi bi-chat-left-dots"></i>
            <p>No hay mensajes en esta sala. ¡Comienza la conversación!</p>
          </div>

          <div 
            v-else
            v-for="(msg, index) in mensajes" 
            :key="msg._id || index"
            :class="['message-row', { 'my-message': msg.remitente_id === usuario.id, 'their-message': msg.remitente_id !== usuario.id }]"
          >
            <!-- Date header split if different day -->
            <div v-if="shouldShowDateHeader(msg, index)" class="date-divider">
              <span>{{ formatDateHeader(msg.fecha) }}</span>
            </div>

            <div class="message-bubble-wrapper">
              <div class="message-bubble">
                <p class="message-text">{{ msg.texto }}</p>
                <span class="message-time">{{ formatTime(msg.fecha) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- No contact selected empty state -->
        <div v-else class="empty-chat-state">
          <div class="empty-state-content">
            <div class="empty-chat-icon">
              <i class="bi bi-chat-square-text-fill"></i>
            </div>
            <h2>Mensajería Institucional</h2>
            <p>Selecciona un contacto de la barra lateral para iniciar o continuar una conversación en tiempo real.</p>
          </div>
        </div>

        <!-- Chat input area -->
        <footer v-if="contactoSeleccionado" class="chat-footer">
          <form @submit.prevent="enviarMensaje" class="input-form">
            <input 
              type="text" 
              placeholder="Escribe un mensaje aquí..." 
              v-model="textoMensaje"
              class="chat-input"
              required
              :disabled="!socketConnected"
            />
            <button 
              type="submit" 
              class="send-btn" 
              :disabled="!textoMensaje.trim() || !socketConnected"
              title="Enviar mensaje"
            >
              <i class="bi bi-send-fill"></i>
            </button>
          </form>
        </footer>

      </main>
      
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';
import { io } from 'socket.io-client';

const props = defineProps({
  usuario: {
    type: Object,
    required: true
  },
  socket: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['activeChatChanged']);

// State
const socket = ref(null);
const socketConnected = ref(false);
const socketError = ref(false);
const contactos = ref([]);
const contactoSeleccionado = ref(null);
const mensajes = ref([]);
const textoMensaje = ref('');
const cargandoContactos = ref(false);
const busqueda = ref('');
const filtroRol = ref('todos');
const chatBody = ref(null);

// Connection Status label
const statusText = computed(() => {
  if (socketError.value) return 'Error de conexión';
  if (socketConnected.value) return 'En línea';
  return 'Conectando...';
});

// Role-based CSS variables
const themeColors = computed(() => {
  const rol = props.usuario?.rol;
  if (rol === 'vice_decano') {
    return {
      primary: '#881337',
      primaryLight: '#fdf2f8',
      accent: '#db2777',
      gradient: 'linear-gradient(135deg, #881337 0%, #4c0519 100%)'
    };
  } else if (rol === 'supervisor') {
    return {
      primary: '#d97706',
      primaryLight: '#fef3c7',
      accent: '#f59e0b',
      gradient: 'linear-gradient(135deg, #d97706 0%, #78350f 100%)'
    };
  } else {
    return {
      primary: '#001374',
      primaryLight: '#eff6ff',
      accent: '#2563eb',
      gradient: 'linear-gradient(135deg, #001374 0%, #000B58 100%)'
    };
  }
});

const themeStyles = computed(() => {
  return {
    '--theme-primary': themeColors.value.primary,
    '--theme-primary-light': themeColors.value.primaryLight,
    '--theme-accent': themeColors.value.accent,
    '--theme-gradient': themeColors.value.gradient
  };
});

// Contacts filter logic
const filteredContacts = computed(() => {
  let list = contactos.value;
  
  // Search filter
  if (busqueda.value.trim()) {
    const q = busqueda.value.toLowerCase();
    list = list.filter(c => 
      `${c.nombre} ${c.apellido}`.toLowerCase().includes(q) || 
      c.correo.toLowerCase().includes(q)
    );
  }

  // Role filter
  if (filtroRol.value === 'pasantes') {
    list = list.filter(c => c.rol === 'pasante');
  } else if (filtroRol.value === 'colegas') {
    list = list.filter(c => c.rol === 'supervisor' || c.rol === 'vice_decano');
  }

  return list;
});

// Normalize contacts data structure
const mapContact = (raw, type) => {
  return {
    usuario_id: raw.usuario_id,
    nombre: String(raw.nombre || raw.nombres || '').trim(),
    apellido: String(raw.apellido || raw.apellidos || '').trim(),
    correo: String(raw.correo || raw.correo_institucional || ''),
    cargo: String(raw.cargo || (type === 'pasante' ? 'Pasante' : 'Personal')),
    rol: String(raw.rol || type),
    area: raw.area ? String(raw.area) : null,
    estado: raw.estado ? String(raw.estado) : null,
    fase_actual: raw.fase_actual ? String(raw.fase_actual) : null
  };
};

// Fetch Contacts
const cargarContactos = async () => {
  if (!props.usuario.id) return;
  cargandoContactos.value = true;
  try {
    if (props.usuario.rol === 'pasante') {
      const res = await axios.get('/api/pasante/mi-supervisor', {
        headers: { 'X-User-Id': props.usuario.id }
      });
      const list = [];
      if (res.data.supervisor && res.data.supervisor.usuario_id) {
        list.push(mapContact(res.data.supervisor, 'supervisor'));
      }
      if (res.data.vicedecano && res.data.vicedecano.usuario_id) {
        list.push(mapContact(res.data.vicedecano, 'vice_decano'));
      }
      contactos.value = list;
    } else {
      const res = await axios.get('/api/supervisor/pasantes', {
        headers: { 'X-User-Id': props.usuario.id }
      });
      const list = [];
      if (res.data.pasantes) {
        res.data.pasantes.forEach(p => {
          if (p.usuario_id) list.push(mapContact(p, 'pasante'));
        });
      }
      if (res.data.colegas) {
        res.data.colegas.forEach(c => {
          if (c.usuario_id) list.push(mapContact(c, c.rol || 'supervisor'));
        });
      }
      contactos.value = list;
    }
  } catch (err) {
    console.error('Error al cargar contactos de chat:', err);
  } finally {
    cargandoContactos.value = false;
  }
};

// Socket logic setup
const connectSocket = () => {
  if (props.socket) {
    socket.value = props.socket;
    socketConnected.value = socket.value.connected;
  } else {
    socket.value = io('http://localhost:3000');
  }

  socket.value.on('connect', () => {
    socketConnected.value = true;
    socketError.value = false;
    
    // Re-join active room if connection was interrupted
    if (contactoSeleccionado.value) {
      socket.value.emit('join_room', {
        remitente_id: props.usuario.id,
        destinatario_id: contactoSeleccionado.value.usuario_id
      });
    }
  });

  socket.value.on('disconnect', () => {
    socketConnected.value = false;
  });

  socket.value.on('connect_error', () => {
    socketConnected.value = false;
    socketError.value = true;
  });

  if (socketConnected.value && contactoSeleccionado.value) {
    socket.value.emit('join_room', {
      remitente_id: props.usuario.id,
      destinatario_id: contactoSeleccionado.value.usuario_id
    });
  }

  socket.value.on('chat_history', (historial) => {
    mensajes.value = historial;
    scrollToBottom();
  });

  socket.value.on('new_message', (mensaje) => {
    const ids = [Number(props.usuario.id), Number(contactoSeleccionado.value?.usuario_id)].sort((a, b) => a - b);
    const activeRoom = `sala_${ids[0]}_${ids[1]}`;
    
    if (mensaje.sala === activeRoom) {
      mensajes.value.push(mensaje);
      scrollToBottom();
    }
  });
};

const seleccionarContacto = (contact) => {
  contactoSeleccionado.value = contact;
  mensajes.value = []; // clear current UI state
  emit('activeChatChanged', contact.usuario_id);
  
  if (socket.value && socketConnected.value) {
    socket.value.emit('join_room', {
      remitente_id: props.usuario.id,
      destinatario_id: contact.usuario_id
    });
  }
};

const enviarMensaje = () => {
  if (!textoMensaje.value.trim() || !contactoSeleccionado.value) return;

  if (socket.value && socketConnected.value) {
    socket.value.emit('send_message', {
      remitente_id: props.usuario.id,
      remitente_nombre: `${props.usuario.nombres || ''} ${props.usuario.apellidos || ''}`.trim(),
      destinatario_id: contactoSeleccionado.value.usuario_id,
      texto: textoMensaje.value
    });
    textoMensaje.value = '';
  }
};

const scrollToBottom = () => {
  nextTick(() => {
    if (chatBody.value) {
      chatBody.value.scrollTop = chatBody.value.scrollHeight;
    }
  });
};

// Formatting & Design Helpers
const getInitials = (nombre, apellido) => {
  return `${(nombre || '').charAt(0)}${(apellido || '').charAt(0)}`.toUpperCase();
};

const getRoleColor = (rol) => {
  if (rol === 'vice_decano') return '#881337';
  if (rol === 'supervisor') return '#d97706';
  return '#2563eb';
};

const getRoleBgColor = (rol) => {
  if (rol === 'vice_decano') return '#fdf2f8';
  if (rol === 'supervisor') return '#fef3c7';
  return '#eff6ff';
};

const formatTime = (fechaStr) => {
  if (!fechaStr) return '';
  const d = new Date(fechaStr);
  return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const formatDateHeader = (fechaStr) => {
  const d = new Date(fechaStr);
  return d.toLocaleDateString([], { weekday: 'long', day: 'numeric', month: 'long' });
};

const shouldShowDateHeader = (msg, index) => {
  if (index === 0) return true;
  const currentMsgDate = new Date(msg.fecha).toDateString();
  const prevMsgDate = new Date(mensajes.value[index - 1].fecha).toDateString();
  return currentMsgDate !== prevMsgDate;
};

// Lifecycle
onMounted(() => {
  cargarContactos();
  connectSocket();
});

onUnmounted(() => {
  emit('activeChatChanged', null);
  if (socket.value) {
    socket.value.off('chat_history');
    socket.value.off('new_message');
    if (!props.socket) {
      socket.value.disconnect();
    }
  }
});
</script>

<style scoped>
.chat-wrapper {
  height: 100%;
  width: 100%;
  display: flex;
  flex-direction: column;
}

.chat-container {
  display: flex;
  flex: 1;
  background: #ffffff;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
  border: 1px solid #e2e8f0;
  height: calc(100vh - 150px);
}

/* SIDEBAR STYLES */
.chat-sidebar {
  width: 320px;
  display: flex;
  flex-direction: column;
  border-right: 1px solid #e2e8f0;
  background: #f8fafc;
}

.sidebar-search-container {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  border-bottom: 1px solid #e2e8f0;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input-wrapper i {
  position: absolute;
  left: 12px;
  color: #94a3b8;
  font-size: 16px;
}

.search-input {
  width: 100%;
  padding: 10px 12px 10px 36px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  font-size: 14px;
  outline: none;
  transition: all 0.2s ease;
}

.search-input:focus {
  border-color: var(--theme-primary);
  box-shadow: 0 0 0 3px var(--theme-primary-light);
}

.filter-pills {
  display: flex;
  gap: 6px;
}

.filter-pill {
  flex: 1;
  padding: 6px 12px;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  font-size: 12px;
  font-weight: 500;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: center;
}

.filter-pill:hover {
  background: #f1f5f9;
}

.filter-pill.active {
  background: var(--theme-primary);
  color: #ffffff;
  border-color: var(--theme-primary);
}

/* Connection Status indicator */
.connection-status {
  padding: 6px 16px;
  font-size: 12px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
  border-bottom: 1px solid #e2e8f0;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.connection-status.connected {
  color: #16a34a;
  background: #f0fdf4;
}
.connection-status.connected .status-dot {
  background: #16a34a;
}

.connection-status.disconnected {
  color: #d97706;
  background: #fffbeb;
}
.connection-status.disconnected .status-dot {
  background: #d97706;
}

.connection-status.error {
  color: #dc2626;
  background: #fef2f2;
}
.connection-status.error .status-dot {
  background: #dc2626;
}

/* Contacts list */
.contacts-list {
  flex: 1;
  overflow-y: auto;
}

.contacts-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  color: #94a3b8;
  text-align: center;
  gap: 12px;
}

.contacts-state i {
  font-size: 32px;
}

.spinner {
  width: 28px;
  height: 28px;
  border: 3px solid #cbd5e1;
  border-top-color: var(--theme-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.contact-card {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  gap: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  border-bottom: 1px solid #f1f5f9;
}

.contact-card:hover {
  background: #f1f5f9;
}

.contact-card.active {
  background: var(--theme-primary-light);
}

.contact-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
  font-weight: 600;
  font-size: 14px;
  flex-shrink: 0;
  box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.contact-info {
  flex: 1;
  min-width: 0;
}

.contact-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
  gap: 8px;
}

.contact-name {
  font-weight: 600;
  color: #1e293b;
  font-size: 14px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.role-badge {
  font-size: 10px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: nowrap;
}

.contact-sub {
  display: flex;
  gap: 4px;
  font-size: 12px;
  color: #64748b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.contact-email {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* CHAT MAIN WINDOW */
.chat-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: #f8fafc;
}

.chat-header {
  padding: 16px 24px;
  background: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.active-contact-profile {
  display: flex;
  align-items: center;
  gap: 14px;
}

.active-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
  font-weight: 600;
  font-size: 16px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.06);
}

.active-contact-details h3 {
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 4px 0;
}

.active-contact-badge-row {
  display: flex;
  gap: 8px;
  align-items: center;
}

.info-tag {
  font-size: 11px;
  font-weight: 500;
  color: #64748b;
  background: #f1f5f9;
  padding: 2px 8px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  gap: 4px;
}

/* CHAT BODY STYLES */
.chat-body {
  flex: 1;
  overflow-y: auto;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  background: #f1f5f9;
}

.no-messages-prompt {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #94a3b8;
  gap: 12px;
}

.no-messages-prompt i {
  font-size: 44px;
}

.message-row {
  display: flex;
  flex-direction: column;
  width: 100%;
}

.date-divider {
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 16px 0;
  position: relative;
}

.date-divider::before {
  content: '';
  position: absolute;
  left: 0;
  right: 0;
  height: 1px;
  background: #e2e8f0;
  z-index: 1;
}

.date-divider span {
  background: #f1f5f9;
  padding: 4px 14px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
  z-index: 2;
  text-transform: capitalize;
}

.message-bubble-wrapper {
  display: flex;
  width: 100%;
}

.message-bubble {
  max-width: 65%;
  padding: 12px 16px;
  border-radius: 16px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.03);
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 4px;
  animation: popIn 0.25s ease-out;
}

@keyframes popIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

.my-message .message-bubble-wrapper {
  justify-content: flex-end;
}

.my-message .message-bubble {
  background: #001374;
  color: #ffffff;
  border-bottom-right-radius: 4px;
}

.their-message .message-bubble-wrapper {
  justify-content: flex-start;
}

.their-message .message-bubble {
  background: #ffffff;
  color: #1e293b;
  border-bottom-left-radius: 4px;
  border: 1px solid #e2e8f0;
}

.message-text {
  margin: 0;
  font-size: 14px;
  line-height: 1.5;
  word-break: break-word;
  white-space: pre-wrap;
}

.message-time {
  font-size: 10px;
  align-self: flex-end;
  opacity: 0.8;
}

.my-message .message-time {
  color: #eff6ff;
}

.their-message .message-time {
  color: #64748b;
}

/* EMPTY CHAT STATE */
.empty-chat-state {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8fafc;
  padding: 40px;
}

.empty-state-content {
  max-width: 420px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.empty-chat-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: var(--theme-primary-light);
  color: var(--theme-accent);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 36px;
  box-shadow: 0 10px 20px rgba(0,0,0,0.03);
}

.empty-state-content h2 {
  font-size: 22px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.empty-state-content p {
  font-size: 14px;
  color: #64748b;
  line-height: 1.6;
  margin: 0;
}

/* CHAT INPUT AREA */
.chat-footer {
  padding: 16px 24px;
  background: #ffffff;
  border-top: 1px solid #e2e8f0;
}

.input-form {
  display: flex;
  gap: 12px;
}

.chat-input {
  flex: 1;
  padding: 12px 16px;
  border-radius: 12px;
  border: 1px solid #cbd5e1;
  background: #f8fafc;
  font-size: 14px;
  outline: none;
  transition: all 0.2s ease;
}

.chat-input:focus {
  border-color: var(--theme-primary);
  background: #ffffff;
  box-shadow: 0 0 0 3px var(--theme-primary-light);
}

.send-btn {
  width: 46px;
  height: 46px;
  border-radius: 12px;
  background: var(--theme-gradient);
  color: #ffffff;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.send-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 14px rgba(0,0,0,0.15);
}

.send-btn:active:not(:disabled) {
  transform: translateY(0);
}

.send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}
</style>
