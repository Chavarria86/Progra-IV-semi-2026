<template>
  <div class="progreso-view animate-fade-in">
    <div class="dashboard-section-card max-w-800">
      <h5 class="card-title"><i class="bi bi-bar-chart-steps text-accent"></i> Historial de Progreso de Pasantías</h5>
      <p class="text-muted mb-4">Sigue tu avance respecto a la meta institucional de <strong>600 horas</strong> requeridas para la culminación de tu práctica profesional.</p>

      <!-- Panel de Métricas -->
      <div class="progreso-metrics-grid mb-5">
        <div class="metric-box bg-light border">
          <div class="metric-icon text-accent"><i class="bi bi-check-circle-fill"></i></div>
          <div class="metric-value text-accent">{{ horasAprobadas }}h</div>
          <div class="metric-label">Horas Aprobadas</div>
        </div>
        <div class="metric-box bg-light border position-relative">
          <div class="metric-icon text-warning"><i class="bi bi-hourglass-split"></i></div>
          <div class="metric-value text-warning">{{ horasPendientes }}h</div>
          <div class="metric-label">Pendientes de Revisión</div>
          <span v-if="horasPendientes > 0" class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle">
            <span class="visually-hidden">Alert</span>
          </span>
        </div>
        <div class="metric-box bg-light border">
          <div class="metric-icon text-secondary"><i class="bi bi-flag-fill"></i></div>
          <div class="metric-value text-secondary">{{ horasTotales }}h</div>
          <div class="metric-label">Meta Global</div>
        </div>
      </div>

      <!-- Barra de Progreso Principal -->
      <div class="progress-container mb-4">
        <div class="d-flex justify-content-between mb-2">
          <span class="fw-bold text-dark">Avance Aprobado: {{ porcentajeAprobado }}%</span>
          <span class="fw-bold text-muted">{{ horasAprobadas }} / {{ horasTotales }} horas</span>
        </div>
        
        <div class="progress" style="height: 25px; border-radius: 12px; background-color: #e2e8f0;">
          <!-- Horas Aprobadas -->
          <div class="progress-bar bg-accent progress-bar-striped progress-bar-animated" role="progressbar" 
               :style="{ width: porcentajeAprobado + '%' }" :aria-valuenow="porcentajeAprobado" aria-valuemin="0" aria-valuemax="100">
            <span v-if="porcentajeAprobado > 5">{{ porcentajeAprobado }}%</span>
          </div>
          <!-- Horas Pendientes (Visualizadas pero no sumadas al porcentaje oficial) -->
          <div v-if="horasPendientes > 0" class="progress-bar bg-warning opacity-50" role="progressbar" 
               :style="{ width: porcentajePendiente + '%' }" title="Horas en revisión">
          </div>
        </div>
      </div>

      <!-- Alerta Crítica según Regla de Negocio -->
      <div v-if="horasPendientes > 0" class="alert alert-custom-warning d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-3 me-3 text-warning"></i>
        <div>
          <h6 class="alert-heading fw-bold mb-1">ESTADO: PENDIENTE DE REVISIÓN</h6>
          <p class="mb-0 small text-muted">Tienes <strong>{{ horasPendientes }} horas</strong> registradas en tu calendario de este mes. Estas horas NO se sumarán a tu progreso principal hasta que tu supervisor apruebe tu informe mensual. <em>El progreso aumentará una vez que el supervisor apruebe este reporte.</em></p>
        </div>
      </div>
      
      <div v-else-if="horasAprobadas > 0" class="alert alert-custom-success d-flex align-items-center" role="alert">
        <i class="bi bi-check-circle-fill fs-3 me-3 text-success"></i>
        <div>
          <h6 class="alert-heading fw-bold mb-1">¡Todo al día!</h6>
          <p class="mb-0 small text-muted">Todas tus horas reportadas han sido validadas por tu supervisor.</p>
        </div>
      </div>

      <div v-else class="alert alert-custom-info d-flex align-items-center" role="alert">
        <i class="bi bi-info-circle-fill fs-3 me-3 text-info"></i>
        <div>
          <h6 class="alert-heading fw-bold mb-1">Sin Informes Registrados</h6>
          <p class="mb-0 small text-muted">Aún no has registrado ningún informe de horas. Ve a la sección de <strong>Informes</strong> para comenzar a reportar tu progreso.</p>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  usuario: { type: Object, default: () => ({}) },
  perfilCompleto: { type: Object, default: () => ({}) }
});

const horasTotales = ref(600);

const horasAprobadas = computed(() => {
  return props.perfilCompleto?.horas_aprobadas || 0;
});

const horasPendientes = computed(() => {
  return props.perfilCompleto?.horas_pendientes || 0;
});

const porcentajeAprobado = computed(() => {
  const p = (horasAprobadas.value / horasTotales.value) * 100;
  return Math.min(Math.round(p), 100);
});

const porcentajePendiente = computed(() => {
  const p = (horasPendientes.value / horasTotales.value) * 100;
  return Math.min(Math.round(p), 100);
});
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.max-w-800 { max-width: 800px; margin: 0 auto; }
.dashboard-section-card {
  background: var(--surface, #fff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: var(--radius, 12px);
  padding: 35px;
  box-shadow: 0 4px 18px rgba(0,0,0,0.03);
}

.card-title { font-family: 'Lora', serif; font-weight: 600; font-size: 1.35rem; display: flex; align-items: center; gap: 10px; }
.text-accent { color: var(--accent, #001374) !important; }
.bg-accent { background-color: var(--accent, #001374) !important; }

/* Grid de Métricas */
.progreso-metrics-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}
.metric-box {
  padding: 20px;
  border-radius: 12px;
  text-align: center;
  transition: transform 0.2s ease;
}
.metric-box:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
.metric-icon { font-size: 2rem; margin-bottom: 5px; }
.metric-value { font-size: 2.2rem; font-weight: 800; line-height: 1; margin-bottom: 5px; }
.metric-label { font-size: 0.85rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }

/* Alertas Personalizadas */
.alert-custom-warning {
  background-color: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: 10px;
  color: #92400e;
}
.alert-custom-success {
  background-color: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 10px;
  color: #166534;
}
.alert-custom-info {
  background-color: #f0f9ff;
  border: 1px solid #bae6fd;
  border-radius: 10px;
  color: #0369a1;
}

@media (max-width: 768px) {
  .progreso-metrics-grid { grid-template-columns: 1fr; }
}
</style>
