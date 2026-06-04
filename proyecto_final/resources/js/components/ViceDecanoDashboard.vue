<template>
  <div class="vice-dashboard" :class="{ 'dark-mode': isDark }">

    <!-- Vista general: Banner hero, stats, actividad reciente -->
    <ViceDecanoOverview
      v-if="seccionActiva === 'dashboard'"
      :isDark="isDark"
      :informesPendientes="informesPendientes"
      :informesAprobados="informesAprobados"
      :informesCorreccion="informesCorreccion"
      :actividad="actividad"
      @toggleDark="isDark = !isDark"
    />

    <!-- Panel de evaluación de informes finales -->
    <ViceDecanoEvaluarInforme
      v-else-if="seccionActiva === 'evaluar_informe'"
      :cargando="cargando"
      @evaluar="ejecutarEvaluacion"
    />

    <!-- Módulos de Vice Decano -->
    <ViceDecanoCrearVacantes v-else-if="seccionActiva === 'crear_vacantes'" />
    <ViceDecanoAsignarSupervisores v-else-if="seccionActiva === 'asignar_supervisores'" />

    <!-- Modo Supervisor Global (Incrustado con Navegación por Pestañas) -->
    <div v-else-if="seccionActiva === 'vista_supervisores'" class="modo-supervisor-wrapper animate-fade-in">
      <div class="supervisor-nav-tabs mb-4">
        <button :class="{'active': tabSupervisor === 'dashboard'}" @click="tabSupervisor = 'dashboard'">
          <i class="bi bi-grid-fill"></i> Resumen
        </button>
        <button :class="{'active': tabSupervisor === 'validar_cv'}" @click="tabSupervisor = 'validar_cv'">
          <i class="bi bi-check2-square"></i> Validar CVs
        </button>
        <button :class="{'active': tabSupervisor === 'asignar'}" @click="tabSupervisor = 'asignar'">
          <i class="bi bi-building-fill-add"></i> Asignar Vacantes
        </button>
        <button :class="{'active': tabSupervisor === 'mis_pasantes'}" @click="tabSupervisor = 'mis_pasantes'">
          <i class="bi bi-people-fill"></i> Gestión Pasantes
        </button>
      </div>

      <div class="supervisor-content-box">
        <SupervisorDashboard :seccionActiva="tabSupervisor" />
      </div>
    </div>

    <!-- Estadísticas generales -->
    <div v-else-if="seccionActiva === 'estadisticas'" class="estadisticas-view animate-fade-in">
      <div class="stats-section-card">
        <h5 class="card-title"><i class="bi bi-bar-chart-fill text-accent"></i> Estadísticas Generales del Sistema</h5>
        <div class="stats-summary-grid mt-4">
          <div class="summary-card">
            <div class="summary-icon">👥</div>
            <div class="summary-num">{{ estadisticas.totalPasantes }}</div>
            <div class="summary-label">Total Pasantes</div>
          </div>
          <div class="summary-card">
            <div class="summary-icon">🛡️</div>
            <div class="summary-num">{{ estadisticas.totalSupervisores }}</div>
            <div class="summary-label">Supervisores</div>
          </div>
          <div class="summary-card">
            <div class="summary-icon">📋</div>
            <div class="summary-num">{{ estadisticas.informesPendientes }}</div>
            <div class="summary-label">Informes Pendientes</div>
          </div>
          <div class="summary-card">
            <div class="summary-icon">✅</div>
            <div class="summary-num">{{ estadisticas.informesAprobados }}</div>
            <div class="summary-label">Informes Aprobados</div>
          </div>
        </div>

        <div class="mt-4">
          <h6 class="area-chart-title">Pasantes por Área</h6>
          <div v-if="cargandoStats" class="text-center py-4">
            <span class="spinner-border text-primary" style="width:1.5rem;height:1.5rem;"></span>
          </div>
          <div v-else class="area-bars">
            <div v-for="area in estadisticasPorArea" :key="area.area" class="area-row">
              <div class="area-label">{{ area.area }}</div>
              <div class="area-bar-track">
                <div class="area-bar-fill" :style="{ width: (area.total / maxAreaTotal * 100) + '%' }"></div>
              </div>
              <div class="area-count">{{ area.total }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

// Importación de subcomponentes
import ViceDecanoOverview from './ViceDecanoOverview.vue';
import ViceDecanoEvaluarInforme from './ViceDecanoEvaluarInforme.vue';
import ViceDecanoCrearVacantes from './ViceDecanoCrearVacantes.vue';
import ViceDecanoAsignarSupervisores from './ViceDecanoAsignarSupervisores.vue';

// Dashboard completo del supervisor
import SupervisorDashboard from './SupervisorDashboard.vue';

const props = defineProps({
  seccionActiva: String,
  isDark: { type: Boolean, default: false }
});

const isDark = ref(props.isDark);
watch(() => props.isDark, (newVal) => {
  isDark.value = newVal;
});
const cargando = ref(false);
const cargandoStats = ref(false);
const informesPendientes = ref(0);
const informesAprobados = ref(0);
const informesCorreccion = ref(0);

const tabSupervisor = ref('dashboard');
const usuarioDummy = ref({ nombres: 'Vicedecano' });

const estadisticas = ref({
  totalPasantes: 0,
  totalSupervisores: 0,
  informesPendientes: 0,
  informesAprobados: 0,
});
const estadisticasPorArea = ref([]);
const maxAreaTotal = ref(1);

const actividad = ref([]);

onMounted(() => {
  cargarEstadisticas();
});

const cargarEstadisticas = async () => {
  cargandoStats.value = true;
  try {
    const res = await axios.get('/api/vicedecano/dashboard');
    const metricas = res.data.metricas || {};
    informesPendientes.value = metricas.informesFinalesPendientes ?? 0;
    informesAprobados.value = metricas.informesAprobados ?? 0;
    informesCorreccion.value = metricas.informesCorreccion ?? 0;

    estadisticas.value = {
      totalPasantes: metricas.totalPasantes ?? 0,
      totalSupervisores: metricas.totalSupervisores ?? 0,
      informesPendientes: informesPendientes.value,
      informesAprobados: informesAprobados.value
    };
    
    actividad.value = res.data.actividad || [];
    estadisticasPorArea.value = res.data.estadisticasPorArea || [];
    maxAreaTotal.value = Math.max(...estadisticasPorArea.value.map(a => a.total), 1);
  } catch (err) {
    console.error('Error cargando estadísticas:', err);
  } finally {
    cargandoStats.value = false;
  }
};

const ejecutarEvaluacion = async (payload) => {
  if (!payload.informe_id) {
    alertify.error('Selecciona un informe antes de guardar.');
    return;
  }
  cargando.value = true;
  try {
    const res = await axios.put(
      `/api/vicedecano/informes/${payload.informe_id}/evaluar`,
      { veredicto: payload.veredicto, observaciones: payload.observaciones }
    );
    if (payload.veredicto === 'aprobado') {
      informesAprobados.value++;
      informesPendientes.value = Math.max(0, informesPendientes.value - 1);
      alertify.success('El informe final ha sido aprobado. Carta de finalización generada.');
    } else {
      informesCorreccion.value++;
      informesPendientes.value = Math.max(0, informesPendientes.value - 1);
      alertify.warning('Se han solicitado correcciones al pasante.');
    }
  } catch (err) {
    console.error('Error al evaluar informe:', err);
    const msg = err.response?.data?.mensaje || 'Error al guardar la evaluación.';
    alertify.error(msg);
  } finally {
    cargando.value = false;
  }
};
</script>

<style scoped>
/* ─── Variables CSS ──────────────────────────────────── */
.vice-dashboard {
  --bg:      #F8F9FA;
  --surface: #FFFFFF;
  --border:  #E8E8E4;
  --text:    #1B1B18;
  --sub:     #706F6C;
  --accent:  #67000F;
  --accent2: #F8B803;
  --success: #16A34A;
  --warning: #D97706;
  --radius:  12px;
  --shadow:  0 4px 24px rgba(0,0,0,0.07);

  background: var(--bg);
  color: var(--text);
  min-height: 100%;
  padding: 0;
  transition: background 0.3s, color 0.3s;
  position: relative;
  max-width: 1200px;
  margin: 0 auto;
}

/* Dark mode overrides */
.vice-dashboard.dark-mode {
  --bg:      #0A0A0A;
  --surface: #161615;
  --border:  #2E2E2A;
  --text:    #EDEDEC;
  --sub:     #A1A09A;
  --accent:  #FF750F;
  --accent2: #FFB347;
}

.animate-fade-in {
  animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Navegación Modo Supervisor */
.supervisor-nav-tabs {
  display: flex;
  gap: 10px;
  background: var(--surface);
  padding: 12px;
  border-radius: var(--radius);
  border: 1px solid var(--border);
  box-shadow: var(--shadow);
}
.supervisor-nav-tabs button {
  background: transparent;
  border: none;
  padding: 10px 18px;
  border-radius: 8px;
  font-weight: 600;
  color: var(--sub);
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
}
.supervisor-nav-tabs button:hover {
  background: var(--bg);
  color: var(--text);
}
.supervisor-nav-tabs button.active {
  background: var(--accent);
  color: white;
}

/* Estadísticas section */
.estadisticas-view {
  width: 100%;
}
.stats-section-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 28px;
  box-shadow: var(--shadow);
}
.card-title {
  font-family: 'Lora', serif;
  font-size: 20px;
  font-weight: 600;
  color: var(--text);
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 0;
}
.text-accent { color: var(--accent) !important; }

.stats-summary-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}
.summary-card {
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 20px;
  text-align: center;
  transition: transform 0.2s;
}
.summary-card:hover { transform: translateY(-2px); }
.summary-icon { font-size: 2rem; margin-bottom: 8px; }
.summary-num {
  font-size: 2rem;
  font-weight: 800;
  color: var(--accent);
  line-height: 1;
}
.summary-label {
  font-size: 0.78rem;
  color: var(--sub);
  margin-top: 4px;
}

/* Area bars */
.area-chart-title {
  font-size: 15px;
  font-weight: 600;
  color: var(--text);
  margin-bottom: 16px;
}
.area-bars {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.area-row {
  display: grid;
  grid-template-columns: 130px 1fr 40px;
  align-items: center;
  gap: 12px;
}
.area-label {
  font-size: 13px;
  font-weight: 600;
  color: var(--sub);
  text-transform: capitalize;
}
.area-bar-track {
  background: var(--border);
  border-radius: 20px;
  height: 8px;
  overflow: hidden;
}
.area-bar-fill {
  height: 100%;
  background: var(--accent);
  border-radius: 20px;
  transition: width 0.8s ease;
}
.area-count {
  font-size: 13px;
  font-weight: 700;
  color: var(--text);
  text-align: right;
}

@media (max-width: 768px) {
  .stats-summary-grid { grid-template-columns: repeat(2, 1fr); }
  .area-row { grid-template-columns: 90px 1fr 30px; }
}
</style>
