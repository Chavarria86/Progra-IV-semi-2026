<template>
  <div class="asignar-supervisores-view animate-fade-in">
    <div class="dashboard-section-card">
      <h5 class="card-title"><i class="bi bi-person-lines-fill text-accent"></i> Asignación de Pasantes a Supervisores</h5>
      <p class="text-muted mb-4">Selecciona un pasante registrado y asígnale un supervisor para que coordine y evalúe su proceso de práctica profesional.</p>

      <div class="row">
        <!-- Columna de Pasantes -->
        <div class="col-md-6 mb-4">
          <label class="form-label fw-semibold">1. Seleccionar Pasante</label>
          <div class="list-group list-group-custom">
            <button 
              v-for="pasante in pasantes" :key="pasante.id"
              class="list-group-item list-group-item-action"
              :class="{ 'active-selection': pasanteSeleccionado === pasante.id }"
              @click="pasanteSeleccionado = pasante.id"
            >
              <div class="d-flex w-100 justify-content-between align-items-center">
                <h6 class="mb-1 fw-bold">{{ pasante.nombres }} {{ pasante.apellidos }}</h6>
                <small class="badge bg-light text-dark border">{{ pasante.area }}</small>
              </div>
              <p class="mb-0 text-muted small"><i class="bi bi-envelope"></i> {{ pasante.correo }}</p>
              <small class="text-warning fw-semibold" v-if="!pasante.supervisor_id">
                <i class="bi bi-exclamation-circle"></i> Sin supervisor asignado
              </small>
              <small class="text-success" v-else>
                <i class="bi bi-check-circle"></i> Ya asignado
              </small>
            </button>
            <div v-if="pasantes.length === 0" class="text-center p-4 text-muted border rounded">
              <p class="mb-0">No hay pasantes registrados en el sistema.</p>
            </div>
          </div>
        </div>

        <!-- Columna de Supervisores -->
        <div class="col-md-6 mb-4">
          <label class="form-label fw-semibold">2. Asignar a Supervisor</label>
          <div class="list-group list-group-custom">
            <button 
              v-for="supervisor in supervisores" :key="supervisor.id"
              class="list-group-item list-group-item-action"
              :class="{ 'active-selection': supervisorSeleccionado === supervisor.id }"
              @click="supervisorSeleccionado = supervisor.id"
            >
              <div class="d-flex w-100 align-items-center">
                <div class="avatar-sm me-3">
                  <i class="bi bi-person-badge"></i>
                </div>
                <div>
                  <h6 class="mb-1 fw-bold">{{ supervisor.nombres }} {{ supervisor.apellidos }}</h6>
                  <p class="mb-0 text-muted small">{{ supervisor.correo }}</p>
                </div>
              </div>
            </button>
            <div v-if="supervisores.length === 0" class="text-center p-4 text-muted border rounded">
              <p class="mb-0">No hay supervisores disponibles.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Botón de acción -->
      <div class="d-flex justify-content-end border-top pt-4 mt-2">
        <button class="btn btn-accent px-5 py-2" :disabled="!pasanteSeleccionado || !supervisorSeleccionado || cargando" @click="asignarSupervisor">
          <span v-if="cargando" class="spinner-border spinner-border-sm me-2"></span>
          <i v-else class="bi bi-link-45deg me-2 fs-5"></i>
          {{ cargando ? 'Asignando...' : 'Confirmar Asignación' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const cargando = ref(false);
const pasanteSeleccionado = ref(null);
const supervisorSeleccionado = ref(null);

const pasantes = ref([]);
const supervisores = ref([]);

const cargarDatos = async () => {
  try {
    const res = await axios.get('/api/vicedecano/asignaciones-data');
    pasantes.value = res.data.pasantes || [];
    supervisores.value = res.data.supervisores || [];
  } catch (error) {
    console.error(error);
    alertify.error('Error al cargar la lista de asignaciones.');
  }
};

onMounted(() => {
  cargarDatos();
});

const asignarSupervisor = async () => {
  if (!pasanteSeleccionado.value || !supervisorSeleccionado.value) return;
  cargando.value = true;
  try {
    const res = await axios.post('/api/vicedecano/asignar-supervisor', { 
      pasante_id: pasanteSeleccionado.value, 
      supervisor_id: supervisorSeleccionado.value 
    });
    
    // Actualizar vista local
    const p = pasantes.value.find(x => x.id === pasanteSeleccionado.value);
    if (p) p.supervisor_id = supervisorSeleccionado.value;
    
    alertify.success(res.data.mensaje || 'Se ha asignado el supervisor correctamente.');
    pasanteSeleccionado.value = null;
    supervisorSeleccionado.value = null;
  } catch (error) {
    console.error(error);
    const msg = error.response?.data?.mensaje || 'Error en la asignación.';
    alertify.error(msg);
  } finally {
    cargando.value = false;
  }
};
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.dashboard-section-card {
  background: var(--surface, #fff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: var(--radius, 12px);
  padding: 30px;
  box-shadow: 0 4px 18px rgba(0,0,0,0.03);
}

.card-title { font-family: 'Lora', serif; font-weight: 600; font-size: 1.25rem; display: flex; align-items: center; gap: 10px; }
.text-accent { color: var(--accent, #67000F); }

.list-group-custom {
  max-height: 400px;
  overflow-y: auto;
  border-radius: 8px;
}

.list-group-item {
  border: 1px solid #e2e8f0;
  margin-bottom: 8px;
  border-radius: 8px !important;
  transition: all 0.2s;
  cursor: pointer;
}

.list-group-item:hover {
  background-color: #f8fafc;
  border-color: #cbd5e1;
}

.active-selection {
  background-color: color-mix(in srgb, var(--accent, #67000F) 8%, white) !important;
  border-color: var(--accent, #67000F) !important;
}

.avatar-sm {
  width: 40px; height: 40px;
  background: #f1f5f9;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.2rem; color: #475569;
}

.btn-accent {
  background-color: var(--accent, #67000F);
  color: white;
  font-weight: 600;
  border: none;
  transition: opacity 0.2s;
  display: flex; align-items: center;
}
.btn-accent:hover:not(:disabled) { background-color: color-mix(in srgb, var(--accent, #67000F) 85%, black); color: white; }
</style>
