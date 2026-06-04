<template>
  <div class="dashboard-overview animate-fade-in">
    <!-- Welcome Card -->
    <div class="welcome-card">
      <div class="welcome-left">
        <h3>¡Hola de nuevo, {{ usuario.nombres }}!</h3>
        <p>Bienvenido al portal de Génesis Profesional. Aquí puedes gestionar tu currículum, subir informes y monitorear tu progreso de pasantía.</p>
      </div>
      <div class="welcome-right">
        <div class="welcome-crest">
          <i class="bi bi-mortarboard-fill"></i>
        </div>
      </div>
    </div>

    <!-- Mi Supervisor Section -->
    <div v-if="cargandoSupervisor" class="text-center py-4">
      <span class="spinner-border text-primary"></span>
    </div>
    <div v-else-if="supervisorData" class="dashboard-section-card border-primary" style="background-color: #f8faff;">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="card-title m-0"><i class="bi bi-person-badge-fill text-primary"></i> Mi Supervisor Asignado</h5>
      </div>
      <div class="d-flex align-items-center mb-4">
        <div class="supervisor-avatar me-3">
          <i class="bi bi-person-fill"></i>
        </div>
        <div>
          <h6 class="fw-bold mb-1">{{ supervisorData.nombres }} {{ supervisorData.apellidos }}</h6>
          <p class="text-muted small mb-0"><i class="bi bi-envelope me-1"></i> {{ supervisorData.correo }}</p>
        </div>
      </div>
      
      <div v-if="notificaciones.length > 0" class="notificaciones-list">
        <h6 class="fw-bold text-danger mb-2"><i class="bi bi-bell-fill"></i> Notificaciones Recientes</h6>
        <div v-for="(notif, index) in notificaciones" :key="index" class="alert alert-warning py-2 px-3 mb-2 d-flex justify-content-between align-items-center">
          <span>{{ notif.mensaje }}</span>
          <small class="text-muted ms-3">{{ notif.fecha }}</small>
        </div>
      </div>
    </div>

    <!-- Stepper Progress Tracker -->
    <div class="dashboard-section-card">
      <h5 class="card-title"><i class="bi bi-bar-chart-steps text-primary"></i> Estado de mi Pasantía</h5>
      <div class="stepper">
        <div class="step" :class="getStepClass('F1')">
          <div class="step-circle"><i class="bi bi-file-earmark-person-fill"></i></div>
          <div class="step-content">
            <span class="step-title">Fase 1: Currículum</span>
            <span class="step-badge">{{ getStepStatus('F1') }}</span>
          </div>
        </div>
        <div class="step-line" :class="getStepLineClass('F1')"></div>

        <div class="step" :class="getStepClass('F2')">
          <div class="step-circle"><i class="bi bi-building-fill-add"></i></div>
          <div class="step-content">
            <span class="step-title">Fase 2: Vacante</span>
            <span class="step-badge">{{ getStepStatus('F2') }}</span>
          </div>
        </div>
        <div class="step-line" :class="getStepLineClass('F2')"></div>

        <div class="step" :class="getStepClass('F3')">
          <div class="step-circle"><i class="bi bi-briefcase-fill"></i></div>
          <div class="step-content">
            <span class="step-title">Fase 3: Prácticas</span>
            <span class="step-badge">{{ getStepStatus('F3') }}</span>
          </div>
        </div>
        <div class="step-line" :class="getStepLineClass('F3')"></div>

        <div class="step" :class="getStepClass('F4')">
          <div class="step-circle"><i class="bi bi-file-earmark-check-fill"></i></div>
          <div class="step-content">
            <span class="step-title">Fase 4: Informe Final</span>
            <span class="step-badge">{{ getStepStatus('F4') }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick stats & shortcuts -->
    <div class="quick-grid">
      <div class="shortcut-card" @click="$emit('cambiarSeccion', 'perfil')">
        <div class="shortcut-icon bg-blue"><i class="bi bi-person-bounding-box"></i></div>
        <div class="shortcut-info">
          <h5>Perfil Profesional</h5>
          <p>Mira y edita tus datos institucionales y enlaces de contacto.</p>
        </div>
        <div class="shortcut-arrow"><i class="bi bi-arrow-right-short"></i></div>
      </div>

      <div class="shortcut-card" @click="$emit('abrirWizard')">
        <div class="shortcut-icon bg-green"><i class="bi bi-file-earmark-plus-fill"></i></div>
        <div class="shortcut-info">
          <h5>Crear Nuevo CV</h5>
          <p>Genera un currículum PDF estructurado usando nuestra plantilla.</p>
        </div>
        <div class="shortcut-arrow"><i class="bi bi-arrow-right-short"></i></div>
      </div>

      <div class="shortcut-card" @click="$emit('cambiarSeccion', 'informes')">
        <div class="shortcut-icon bg-purple"><i class="bi bi-cloud-arrow-up-fill"></i></div>
        <div class="shortcut-info">
          <h5>Subir Informe</h5>
          <p>Entrega tus reportes de progreso mensuales o tu informe final.</p>
        </div>
        <div class="shortcut-arrow"><i class="bi bi-arrow-right-short"></i></div>
      </div>

      <div class="shortcut-card" @click="solicitarSupervisor" :class="{ 'disabled-card': solicitando }">
        <div class="shortcut-icon bg-orange">
          <span v-if="solicitando" class="spinner-border spinner-border-sm"></span>
          <i v-else class="bi bi-person-raised-hand"></i>
        </div>
        <div class="shortcut-info">
          <h5>Solicitar Supervisor</h5>
          <p>Pide asignación a un supervisor para iniciar tus prácticas.</p>
        </div>
        <div class="shortcut-arrow"><i class="bi bi-arrow-right-short"></i></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
  usuario: { type: Object, default: () => ({}) },
  perfilCompleto: { type: Object, default: () => ({ fase_actual: 'F1' }) }
});

const emit = defineEmits(['cambiarSeccion', 'abrirWizard']);

const solicitando = ref(false);
const supervisorData = ref(null);
const notificaciones = ref([]);
const cargandoSupervisor = ref(false);

import { onMounted } from 'vue';

onMounted(() => {
  cargarSupervisor();
});

const cargarSupervisor = async () => {
  if (!props.usuario?.id) return;
  cargandoSupervisor.value = true;
  try {
    const res = await axios.get('/api/pasante/mi-supervisor', {
      headers: { 'X-User-Id': props.usuario.id }
    });
    supervisorData.value = res.data.supervisor;
    notificaciones.value = res.data.notificaciones || [];
  } catch (err) {
    console.error('Error cargando supervisor:', err);
  } finally {
    cargandoSupervisor.value = false;
  }
};

const solicitarSupervisor = async () => {
  if (solicitando.value) return;
  solicitando.value = true;
  try {
    const res = await axios.post('/api/pasante/solicitar-supervisor', {
      mensaje: 'Solicito asignación a su supervisión para iniciar mi proceso de pasantía.'
    }, {
      headers: { 'X-User-Id': props.usuario?.id || 4 }
    });
    alertify.success('Solicitud enviada exitosamente.');
  } catch (err) {
    if (err.response?.status === 409) {
      alertify.warning('Ya tienes una solicitud pendiente.');
    } else {
      alertify.error('Error al enviar la solicitud.');
    }
  } finally {
    solicitando.value = false;
  }
};

/* Stepper helpers */
const getStepClass = (stepPhase) => {
  const phases = ['F1', 'F2', 'F3', 'F4'];
  const currentPhase = props.perfilCompleto.fase_actual || 'F1';
  
  const currentIdx = phases.indexOf(currentPhase);
  const stepIdx = phases.indexOf(stepPhase);
  
  if (stepIdx < currentIdx) {
    return 'completed';
  } else if (stepIdx === currentIdx) {
    return 'active';
  } else {
    return 'pending';
  }
};

const getStepLineClass = (stepPhase) => {
  const phases = ['F1', 'F2', 'F3', 'F4'];
  const currentPhase = props.perfilCompleto.fase_actual || 'F1';
  
  const currentIdx = phases.indexOf(currentPhase);
  const stepIdx = phases.indexOf(stepPhase);
  
  if (stepIdx < currentIdx) {
    return 'completed';
  } else {
    return 'pending';
  }
};

const getStepStatus = (stepPhase) => {
  const phases = ['F1', 'F2', 'F3', 'F4'];
  const currentPhase = props.perfilCompleto.fase_actual || 'F1';
  
  const currentIdx = phases.indexOf(currentPhase);
  const stepIdx = phases.indexOf(stepPhase);
  
  if (stepIdx < currentIdx) {
    return 'Completado ✓';
  } else if (stepIdx === currentIdx) {
    return 'En proceso';
  } else {
    return 'Pendiente';
  }
};
</script>

<style scoped>
.dashboard-overview {
  width: 100%;
}

.animate-fade-in {
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Cards global styling */
.dashboard-section-card {
  background-color: #ffffff;
  border-radius: 12px;
  padding: 28px;
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
  border: 1px solid #e2e8f0;
  margin-bottom: 24px;
}

.card-title {
  font-family: 'Lora', serif;
  font-size: 20px;
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.text-primary {
  color: #001374 !important;
}

/* Welcome Card */
.welcome-card {
  background: linear-gradient(135deg, #001374 0%, #010c67 100%);
  border-radius: 14px;
  padding: 30px;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 28px;
  box-shadow: 0 10px 25px -5px rgba(0, 19, 116, 0.2);
}

.welcome-left h3 {
  font-family: 'Lora', serif;
  font-size: 26px;
  font-weight: 600;
  margin: 0 0 8px;
}

.welcome-left p {
  font-size: 15px;
  opacity: 0.85;
  max-width: 700px;
  margin: 0;
  line-height: 1.5;
}

.welcome-crest {
  font-size: 60px;
  color: rgba(255, 255, 255, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Supervisor Card */
.supervisor-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background-color: #001374;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
}

/* Stepper Progress */
.stepper {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 0;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  flex: 1;
}

.step-circle {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  transition: all 0.3s;
  background-color: #f1f5f9;
  color: #94a3b8;
  border: 2px solid #e2e8f0;
}

.step-content {
  margin-top: 10px;
}

.step-title {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #475569;
}

.step-badge {
  display: inline-block;
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 20px;
  background-color: #f1f5f9;
  color: #64748b;
  margin-top: 4px;
  font-weight: 500;
}

.step-line {
  height: 3px;
  flex: 1;
  background-color: #e2e8f0;
  margin-top: -38px;
}

/* Active & completed step styles */
.step.active .step-circle {
  background-color: #001374;
  color: #ffffff;
  border-color: #001374;
  box-shadow: 0 0 0 4px rgba(0, 19, 116, 0.15);
}
.step.active .step-title { color: #001374; }
.step.active .step-badge { background-color: #eff6ff; color: #1d4ed8; }

.step.completed .step-circle {
  background-color: #10b981;
  color: #ffffff;
  border-color: #10b981;
}
.step.completed .step-badge { background-color: #d1fae5; color: #065f46; }

.step-line.completed {
  background-color: #10b981;
}

/* Quick Cards Grid */
.quick-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
  margin-bottom: 24px;
}

.shortcut-card {
  background-color: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.01);
}

.shortcut-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04);
  border-color: #cbd5e1;
}

.shortcut-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  flex-shrink: 0;
  margin-right: 16px;
}

.bg-blue { background-color: #eff6ff; color: #1d4ed8; }
.bg-green { background-color: #ecfdf5; color: #059669; }
.bg-purple { background-color: #faf5ff; color: #7c3aed; }
.bg-orange { background-color: #fff7ed; color: #c2410c; }

.disabled-card {
  opacity: 0.6;
  pointer-events: none;
}

.shortcut-info {
  flex: 1;
}

.shortcut-info h5 {
  font-size: 15px;
  font-weight: 600;
  color: #0f172a;
  margin: 0 0 3px;
}

.shortcut-info p {
  font-size: 12px;
  color: #64748b;
  margin: 0;
  line-height: 1.4;
}

.shortcut-arrow {
  font-size: 22px;
  color: #94a3b8;
  display: flex;
  align-items: center;
}

@media (max-width: 768px) {
  .welcome-card {
    flex-direction: column;
    text-align: center;
    gap: 16px;
    padding: 20px;
  }
  .welcome-crest {
    display: none;
  }
  .stepper {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  .step {
    flex-direction: row;
    align-items: center;
    text-align: left;
    width: 100%;
    gap: 16px;
  }
  .step-content {
    margin-top: 0;
  }
  .step-line {
    display: none;
  }
  .quick-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
}
</style>
