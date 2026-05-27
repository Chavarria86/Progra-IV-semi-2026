<template>
  <div class="evaluar-informes-view animate-fade-in">
    <div class="dashboard-section-card">
      <h5 class="card-title mb-4"><i class="bi bi-file-earmark-check-fill text-primary"></i> Evaluar Informes y Validar Horas</h5>
      
      <p class="text-muted small mb-4">Revisa los informes mensuales de tus pasantes. Al aprobar un informe, las horas reportadas se sumarán automáticamente al progreso validado del estudiante.</p>

      <div v-if="cargando" class="text-center py-4">
        <span class="spinner-border text-primary"></span>
        <p class="text-muted mt-2">Cargando informes...</p>
      </div>

      <div v-else-if="informesPendientes.length === 0" class="text-center py-5 text-muted bg-light rounded border">
        <i class="bi bi-check-circle d-block fs-1 mb-3 text-success"></i>
        <span>No tienes informes pendientes por evaluar. ¡Todo al día!</span>
      </div>

      <div v-else class="informes-grid">
        <div v-for="informe in informesPendientes" :key="informe.id" class="informe-card border rounded p-4 mb-4">
          <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
            <div>
              <h5 class="fw-bold text-dark mb-1">{{ informe.nombre }}</h5>
              <p class="text-muted small mb-0"><i class="bi bi-person-fill me-1"></i> {{ informe.pasante }} | <i class="bi bi-calendar-event me-1"></i> {{ informe.fecha }}</p>
            </div>
            <span class="badge bg-warning text-dark fs-6"><i class="bi bi-hourglass-split me-1"></i> {{ informe.horas }} horas reportadas</span>
          </div>

          <div class="informe-content mb-4">
            <h6 class="fw-bold text-primary">Objetivos</h6>
            <p class="small text-muted">{{ informe.objetivos }}</p>
            
            <h6 class="fw-bold text-primary mt-3">Actividades Realizadas</h6>
            <p class="small text-muted">{{ informe.actividades }}</p>
          </div>

          <div class="informe-actions d-flex gap-3 justify-content-end bg-light p-3 rounded">
            <button class="btn btn-outline-danger px-4" @click="rechazar(informe)" :disabled="procesando === informe.id">
              <i class="bi bi-x-circle me-1"></i> Solicitar Correcciones
            </button>
            <button class="btn btn-success px-4" @click="aprobar(informe)" :disabled="procesando === informe.id">
              <span v-if="procesando === informe.id" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="bi bi-check-circle me-1"></i> Aprobar y Validar Horas
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  usuario: { type: Object, default: () => ({}) }
});

const procesando = ref(null);
const informesPendientes = ref([]);
const cargando = ref(true);

onMounted(() => {
  cargarInformes();
});

const cargarInformes = async () => {
  cargando.value = true;
  try {
    const res = await axios.get('/api/supervisor/informes', {
      headers: { 'X-User-Id': props.usuario?.id || 2 }
    });
    informesPendientes.value = res.data.informes || [];
  } catch (error) {
    console.error('Error cargando informes pendientes:', error);
  } finally {
    cargando.value = false;
  }
};

const aprobar = async (informe) => {
  procesando.value = informe.id;
  try {
    await axios.put(`/api/supervisor/informes/${informe.id}/evaluar`, { decision: 'aprobar' }, {
      headers: { 'X-User-Id': props.usuario?.id || 2 }
    });
    informesPendientes.value = informesPendientes.value.filter(i => i.id !== informe.id);
    alertify.success(`Informe aprobado. Se validaron las horas del pasante.`);
  } catch (err) {
    alertify.error('Error al aprobar informe.');
  } finally {
    procesando.value = null;
  }
};

const rechazar = (informe) => {
  alertify.prompt(
    'Solicitar Correcciones',
    'Ingresa los motivos por los que el informe no fue aprobado:',
    '',
    async function(evt, value) {
      if (!value) {
        alertify.error('Debes proporcionar un motivo.');
        return;
      }
      procesando.value = informe.id;
      try {
        await axios.put(`/api/supervisor/informes/${informe.id}/evaluar`, { decision: 'rechazar', observaciones: value }, {
          headers: { 'X-User-Id': props.usuario?.id || 2 }
        });
        alertify.warning('Notificación enviada al pasante para que corrija su informe.');
        informesPendientes.value = informesPendientes.value.filter(i => i.id !== informe.id);
      } catch (err) {
        alertify.error('Error al rechazar informe.');
      } finally {
        procesando.value = null;
      }
    },
    function() {}
  );
};
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.dashboard-section-card {
  background-color: #ffffff;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 4px 18px rgba(0,0,0,0.03);
  border: 1px solid #e2e8f0;
}
.card-title {
  font-family: 'Lora', serif;
  font-size: 20px;
  font-weight: 600;
  color: #0f172a;
}
.text-primary { color: #001374 !important; }

.informe-card {
  transition: transform 0.2s, box-shadow 0.2s;
  background-color: #fafcff;
}
.informe-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(0,0,0,0.05);
}
.informe-content p {
  white-space: pre-line;
}
</style>
