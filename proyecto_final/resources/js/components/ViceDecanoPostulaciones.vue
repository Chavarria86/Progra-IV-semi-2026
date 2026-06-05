<template>
  <div class="postulaciones-view animate-fade-in" :class="{ 'dark-mode-active': isDark }">
    
    <!-- HEADER CARD -->
    <div class="dashboard-section-card header-gradient-card mb-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h5 class="card-title mb-2 header-title-text">
            <i class="bi bi-list-check me-2"></i> Gestión y Evaluación de Postulaciones
          </h5>
          <p class="mb-0 header-sub-text">
            Administra las postulaciones de los estudiantes para sus prácticas. Aprueba o rechaza de manera final las postulaciones evaluadas por los supervisores.
          </p>
        </div>
        <button class="btn btn-light-accent text-white" @click="cargarPostulaciones" :disabled="cargando">
          <i class="bi bi-arrow-clockwise me-1" :class="{ 'spin': cargando }"></i>
          Actualizar Listado
        </button>
      </div>
    </div>

    <!-- METRICS CARDS -->
    <div class="row g-3 mb-4">
      <div class="col-md-3 col-sm-6">
        <div class="metric-card" @click="filtroActivo = 'todas'" :class="{ active: filtroActivo === 'todas' }">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="metric-label">Total Postulaciones</div>
              <div class="metric-value text-primary">{{ stats.total }}</div>
            </div>
            <div class="metric-icon bg-soft-primary"><i class="bi bi-folder-fill"></i></div>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="metric-card" @click="filtroActivo = 'pendientes'" :class="{ active: filtroActivo === 'pendientes' }">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="metric-label">Pendientes</div>
              <div class="metric-value text-warning">{{ stats.pendientes }}</div>
            </div>
            <div class="metric-icon bg-soft-warning"><i class="bi bi-clock-history"></i></div>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="metric-card" @click="filtroActivo = 'aprobadas'" :class="{ active: filtroActivo === 'aprobadas' }">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="metric-label">Aprobadas (Aceptadas)</div>
              <div class="metric-value text-success">{{ stats.aprobadas }}</div>
            </div>
            <div class="metric-icon bg-soft-success"><i class="bi bi-check-circle-fill"></i></div>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="metric-card" @click="filtroActivo = 'rechazadas'" :class="{ active: filtroActivo === 'rechazadas' }">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="metric-label">Rechazadas</div>
              <div class="metric-value text-danger">{{ stats.rechazadas }}</div>
            </div>
            <div class="metric-icon bg-soft-danger"><i class="bi bi-x-circle-fill"></i></div>
          </div>
        </div>
      </div>
    </div>

    <!-- MAIN INTERFACE -->
    <div class="dashboard-section-card">
      
      <!-- CONTROLS ROW -->
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <!-- Filter Tabs -->
        <div class="filter-tabs">
          <button :class="{ active: filtroActivo === 'todas' }" @click="filtroActivo = 'todas'">
            Todas
          </button>
          <button :class="{ active: filtroActivo === 'pendientes' }" @click="filtroActivo = 'pendientes'">
            Pendientes <span class="badge bg-warning ms-1 text-dark">{{ stats.pendientes }}</span>
          </button>
          <button :class="{ active: filtroActivo === 'aprobadas' }" @click="filtroActivo = 'aprobadas'">
            Aprobadas
          </button>
          <button :class="{ active: filtroActivo === 'rechazadas' }" @click="filtroActivo = 'rechazadas'">
            Rechazadas
          </button>
        </div>

        <!-- Search input -->
        <div class="search-box">
          <i class="bi bi-search"></i>
          <input 
            type="text" 
            class="form-control" 
            placeholder="Buscar por estudiante, empresa o área..."
            v-model="filtroBusqueda"
          >
        </div>
      </div>

      <!-- TABLE & LOADING/EMPTY STATES -->
      <div class="table-responsive">
        <table class="table align-middle table-hover" v-if="!cargando && postulacionesFiltradas.length > 0">
          <thead>
            <tr>
              <th scope="col">Estudiante</th>
              <th scope="col">Vacante / Empresa</th>
              <th scope="col">Área</th>
              <th scope="col" class="text-center">CV Adjunto</th>
              <th scope="col">Supervisor a Cargo</th>
              <th scope="col">Fecha Postulación</th>
              <th scope="col" class="text-center">Estado</th>
              <th scope="col" class="text-end" style="min-width: 170px;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="post in postulacionesFiltradas" :key="post.id" class="table-row-hover">
              <td>
                <div class="d-flex align-items-center">
                  <div class="student-avatar me-2">
                    {{ obtenerIniciales(post.pasante_nombre) }}
                  </div>
                  <div>
                    <span class="fw-semibold text-color-main">{{ post.pasante_nombre }}</span>
                  </div>
                </div>
              </td>
              <td>
                <div class="fw-semibold text-color-main">{{ post.empresa }}</div>
              </td>
              <td>
                <span class="badge bg-area">{{ post.area }}</span>
              </td>
              <td class="text-center">
                <a 
                  v-if="post.cv_url" 
                  :href="post.cv_url" 
                  target="_blank" 
                  class="btn btn-outline-pdf btn-sm"
                  title="Ver Documento CV"
                >
                  <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i>
                  Ver CV
                </a>
                <span v-else class="text-muted small italic">No cargado</span>
              </td>
              <td>
                <span class="text-color-sub small"><i class="bi bi-person me-1"></i>{{ post.supervisor }}</span>
              </td>
              <td>
                <span class="text-color-sub small">{{ post.fecha }}</span>
              </td>
              <td class="text-center">
                <span :class="['status-badge', 'status-' + post.estado]">
                  {{ formatearEstado(post.estado) }}
                </span>
              </td>
              <td class="text-end">
                <div class="d-flex justify-content-end gap-2" v-if="post.estado === 'aprobado_por_supervisor'">
                  <button 
                    class="btn btn-success-custom btn-sm px-3" 
                    @click="confirmarEvaluacion(post, 'aceptar')"
                    :disabled="evaluandoId === post.id"
                  >
                    <span v-if="evaluandoId === post.id && veredictoAccion === 'aceptar'" class="spinner-border spinner-border-sm me-1"></span>
                    <i v-else class="bi bi-check-lg me-1"></i>
                    Aprobar
                  </button>
                  <button 
                    class="btn btn-danger-custom btn-sm px-3" 
                    @click="confirmarEvaluacion(post, 'rechazar')"
                    :disabled="evaluandoId === post.id"
                  >
                    <span v-if="evaluandoId === post.id && veredictoAccion === 'rechazar'" class="spinner-border spinner-border-sm me-1"></span>
                    <i v-else class="bi bi-x-lg me-1"></i>
                    Rechazar
                  </button>
                </div>
                <span v-else class="text-muted small italic">Sin acciones</span>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- LOADING STATE -->
        <div v-if="cargando" class="text-center py-5">
          <div class="spinner-border text-accent mb-3" style="width: 2.5rem; height: 2.5rem;" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
          <p class="text-muted">Obteniendo postulaciones del servidor...</p>
        </div>

        <!-- EMPTY STATE -->
        <div v-if="!cargando && postulacionesFiltradas.length === 0" class="empty-state text-center py-5">
          <div class="empty-icon-wrapper mb-3">
            <i class="bi bi-file-earmark-x"></i>
          </div>
          <h6 class="fw-semibold text-color-main">No se encontraron postulaciones</h6>
          <p class="text-muted max-w-400 mx-auto">
            {{ filtroBusqueda ? 'Prueba ajustando los términos de tu búsqueda para ver otros resultados.' : 'Actualmente no hay postulaciones que coincidan con esta pestaña de filtro.' }}
          </p>
          <button 
            v-if="filtroBusqueda || filtroActivo !== 'todas'" 
            class="btn btn-outline-accent btn-sm mt-2" 
            @click="limpiarFiltros"
          >
            Restaurar Filtros
          </button>
        </div>

      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  isDark: { type: Boolean, default: false }
});

const postulaciones = ref([]);
const cargando = ref(false);
const filtroActivo = ref('todas');
const filtroBusqueda = ref('');

const evaluandoId = ref(null);
const veredictoAccion = ref(''); // 'aceptar' o 'rechazar'

// Cargar postulaciones desde backend
const cargarPostulaciones = async () => {
  cargando.value = true;
  try {
    const res = await axios.get('/api/vicedecano/postulaciones');
    postulaciones.value = res.data.postulaciones || [];
  } catch (err) {
    console.error('Error cargando postulaciones:', err);
    if (window.alertify) {
      alertify.error('Error al obtener el listado de postulaciones.');
    }
  } finally {
    cargando.value = false;
  }
};

onMounted(() => {
  cargarPostulaciones();
});

// Mostrar todas las postulaciones en el listado general (Todas), pero mantener los filtros correspondientes
const postulacionesFiltradasPorVicedecano = computed(() => {
  return postulaciones.value;
});

// Métricas de postulaciones
const stats = computed(() => {
  const all = postulacionesFiltradasPorVicedecano.value;
  return {
    total: all.length,
    pendientes: all.filter(p => p.estado === 'aprobado_por_supervisor').length,
    aprobadas: all.filter(p => p.estado === 'aceptada').length,
    rechazadas: all.filter(p => p.estado === 'rechazada').length
  };
});

// Filtrado y búsqueda
const postulacionesFiltradas = computed(() => {
  let res = postulacionesFiltradasPorVicedecano.value;

  // Filtro por Pestaña
  if (filtroActivo.value === 'pendientes') {
    res = res.filter(p => p.estado === 'aprobado_por_supervisor');
  } else if (filtroActivo.value === 'aprobadas') {
    res = res.filter(p => p.estado === 'aceptada');
  } else if (filtroActivo.value === 'rechazadas') {
    res = res.filter(p => p.estado === 'rechazada');
  }

  // Filtro por Búsqueda de Texto
  const search = filtroBusqueda.value.toLowerCase().trim();
  if (search) {
    res = res.filter(p => {
      const nombre = (p.pasante_nombre || '').toLowerCase();
      const empresa = (p.empresa || '').toLowerCase();
      const area = (p.area || '').toLowerCase();
      return nombre.includes(search) || empresa.includes(search) || area.includes(search);
    });
  }

  return res;
});

// Limpiar filtros
const limpiarFiltros = () => {
  filtroActivo.value = 'todas';
  filtroBusqueda.value = '';
};

// Formateadores y utilidades visuales
const obtenerIniciales = (nombre) => {
  if (!nombre) return 'E';
  const parts = nombre.split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return nombre[0].toUpperCase();
};

const formatearEstado = (estado) => {
  const mappings = {
    pendiente: 'Pendiente Supervisor',
    sugerida: 'Sugerida',
    aprobado_por_supervisor: 'Pendiente Vicedecano',
    aceptada: 'Aprobada (F3)',
    rechazada: 'Rechazada'
  };
  return mappings[estado] ?? estado;
};

// Diálogo de confirmación antes de guardar
const confirmarEvaluacion = (post, veredicto) => {
  const esAprobacion = veredicto === 'aceptar';
  const titulo = esAprobacion ? 'Aprobar Postulación' : 'Rechazar Postulación';
  const mensaje = esAprobacion
    ? `¿Estás seguro de aprobar la postulación de <strong>${post.pasante_nombre}</strong> en <strong>${post.empresa}</strong>?<br><br>Al aprobarla, la postulación cambiará a estado Aceptada y el pasante avanzará automáticamente a la <strong>Fase 3 (F3)</strong> del proceso.`
    : `¿Estás seguro de rechazar la postulación de <strong>${post.pasante_nombre}</strong> en <strong>${post.empresa}</strong>?<br><br>Esta postulación quedará registrada como Rechazada.`;

  if (window.alertify) {
    alertify.confirm(titulo, mensaje, 
      () => {
        ejecutarEvaluacion(post.id, veredicto);
      }, 
      () => {
        // Cancelado
      }
    );
  } else {
    // Fallback si alertify no está disponible
    if (confirm(mensaje.replace(/<[^>]*>/g, ''))) {
      ejecutarEvaluacion(post.id, veredicto);
    }
  }
};

// Acción del Backend
const ejecutarEvaluacion = async (id, veredicto) => {
  evaluandoId.value = id;
  veredictoAccion.value = veredicto;

  try {
    const res = await axios.put(`/api/vicedecano/postulaciones/${id}/evaluar`, {
      veredicto: veredicto
    });
    
    if (window.alertify) {
      if (veredicto === 'aceptar') {
        alertify.success(res.data.mensaje || 'Postulación aprobada con éxito. El pasante avanza a Fase 3.');
      } else {
        alertify.warning(res.data.mensaje || 'Postulación rechazada.');
      }
    }

    // Refrescar listado
    await cargarPostulaciones();

  } catch (err) {
    console.error('Error al evaluar postulación:', err);
    const msg = err.response?.data?.mensaje || 'Error al intentar guardar la evaluación.';
    if (window.alertify) {
      alertify.error(msg);
    }
  } finally {
    evaluandoId.value = null;
    veredictoAccion.value = '';
  }
};
</script>

<style scoped>
.postulaciones-view {
  --text-main: #1B1B18;
  --text-sub: #706F6C;
  --bg-card: #FFFFFF;
  --bg-table-hover: #F8F9FA;
  --border-color: #E8E8E4;
  --accent-color: var(--accent, #67000F);
  
  font-family: 'Inter', sans-serif;
  animation: fadeIn 0.4s ease-out;
}

/* Modo oscuro local */
.postulaciones-view.dark-mode-active {
  --text-main: #EDEDEC;
  --text-sub: #A1A09A;
  --bg-card: #161615;
  --bg-table-hover: #1E1E1D;
  --border-color: #2E2E2A;
  --accent-color: var(--accent, #FF750F);
}

.animate-fade-in {
  animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.text-color-main {
  color: var(--text-main);
}
.text-color-sub {
  color: var(--text-sub);
}
.max-w-400 {
  max-width: 400px;
}

/* Header Card gradient - Overriding background with important to fix the white box issue */
.header-gradient-card {
  background: linear-gradient(135deg, #67000F 0%, #a31621 100%) !important;
  border: none !important;
  box-shadow: 0 4px 20px rgba(103, 0, 15, 0.15);
  border-radius: var(--radius, 12px);
  padding: 24px;
}

.dark-mode-active .header-gradient-card {
  background: linear-gradient(135deg, #241409 0%, #FF750F 100%) !important;
  box-shadow: 0 4px 20px rgba(255, 117, 15, 0.15);
}

.header-title-text {
  color: #FFFFFF !important;
  font-weight: 700;
}
.header-sub-text {
  color: rgba(255, 255, 255, 0.85) !important;
}

.btn-light-accent {
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.25);
  font-weight: 500;
  transition: background 0.2s, transform 0.2s;
  backdrop-filter: blur(4px);
}
.btn-light-accent:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-1px);
}

.spin {
  animation: rotation 1s infinite linear;
}
@keyframes rotation {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Card Box style */
.dashboard-section-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius, 12px);
  padding: 28px;
  box-shadow: 0 4px 18px rgba(0,0,0,0.03);
  transition: background 0.3s, border-color 0.3s;
}

.card-title {
  font-family: 'Lora', serif;
  font-weight: 600;
  font-size: 1.25rem;
  display: flex;
  align-items: center;
  gap: 10px;
}

/* Metric Cards style */
.metric-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius, 12px);
  padding: 20px;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0,0,0,0.02);
  transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
}
.metric-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.05);
  border-color: var(--accent-color);
}
.metric-card.active {
  border-color: var(--accent-color);
  box-shadow: 0 0 0 1px var(--accent-color);
}
.metric-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-sub);
  margin-bottom: 6px;
}
.metric-value {
  font-size: 1.8rem;
  font-weight: 800;
  line-height: 1.1;
}

/* Color definitions for soft icons */
.metric-icon {
  width: 44px;
  height: 44px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
}
.bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; }
.bg-soft-warning { background-color: rgba(255, 193, 7, 0.15); color: #ffc107; }
.bg-soft-success { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
.bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }

/* Filter Tabs */
.filter-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  background: var(--bg-table-hover);
  padding: 6px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
}
.filter-tabs button {
  background: transparent;
  border: none;
  padding: 8px 16px;
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--text-sub);
  border-radius: 6px;
  transition: all 0.2s;
}
.filter-tabs button:hover {
  color: var(--text-main);
  background: var(--bg-card);
}
.filter-tabs button.active {
  background: var(--accent-color);
  color: #FFFFFF !important;
}

/* Search Box */
.search-box {
  position: relative;
  min-width: 280px;
}
.search-box i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-sub);
  font-size: 0.95rem;
}
.search-box .form-control {
  padding-left: 38px;
  font-size: 0.9rem;
  height: 40px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background-color: var(--bg-card);
  color: var(--text-main);
  transition: border-color 0.2s, box-shadow 0.2s;
}
.search-box .form-control:focus {
  border-color: var(--accent-color);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent-color) 25%, transparent);
}

/* Table Style */
.table {
  color: var(--text-main);
}
.table th {
  border-bottom: 2px solid var(--border-color);
  color: var(--text-sub);
  font-weight: 600;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 14px 12px;
}
.table td {
  border-bottom: 1px solid var(--border-color);
  padding: 16px 12px;
}
.table-row-hover {
  transition: background-color 0.15s;
}
.table-row-hover:hover {
  background-color: var(--bg-table-hover);
}

/* Student Avatar */
.student-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background-color: var(--accent-color);
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.85rem;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Badges */
.badge.bg-area {
  background-color: rgba(103, 0, 15, 0.08) !important;
  color: #67000F !important;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.75rem;
  padding: 5px 8px;
  border-radius: 4px;
}
.dark-mode-active .badge.bg-area {
  background-color: rgba(255, 117, 15, 0.12) !important;
  color: #FF750F !important;
}

/* PDF Button styling */
.btn-outline-pdf {
  border: 1px solid #dc3545;
  color: #dc3545;
  font-size: 0.8rem;
  font-weight: 600;
  border-radius: 6px;
  padding: 5px 10px;
  background-color: transparent;
  transition: background 0.2s, color 0.2s;
}
.btn-outline-pdf:hover {
  background-color: #dc3545;
  color: #FFFFFF !important;
}
.btn-outline-pdf:hover i {
  color: #FFFFFF !important;
}

/* Status Pill Badges */
.status-badge {
  display: inline-block;
  padding: 6px 12px;
  font-size: 0.75rem;
  font-weight: 700;
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  text-align: center;
}
.status-pendiente {
  background-color: rgba(13, 110, 253, 0.08);
  color: #0d6efd;
}
.status-sugerida {
  background-color: rgba(111, 66, 193, 0.08);
  color: #6f42c1;
}
.status-aprobado_por_supervisor {
  background-color: rgba(255, 193, 7, 0.15);
  color: #b58200;
}
.dark-mode-active .status-aprobado_por_supervisor {
  color: #ffca2c;
}
.status-aceptada {
  background-color: rgba(25, 135, 84, 0.08);
  color: #198754;
}
.status-rechazada {
  background-color: rgba(220, 53, 69, 0.08);
  color: #dc3545;
}

/* Custom action buttons */
.btn-success-custom {
  background-color: #198754;
  color: #FFFFFF;
  border: none;
  font-weight: 600;
  border-radius: 6px;
  transition: opacity 0.2s, transform 0.1s;
}
.btn-success-custom:hover:not(:disabled) {
  opacity: 0.9;
  color: #FFFFFF;
}
.btn-success-custom:active:not(:disabled) {
  transform: scale(0.97);
}

.btn-danger-custom {
  background-color: #dc3545;
  color: #FFFFFF;
  border: none;
  font-weight: 600;
  border-radius: 6px;
  transition: opacity 0.2s, transform 0.1s;
}
.btn-danger-custom:hover:not(:disabled) {
  opacity: 0.9;
  color: #FFFFFF;
}
.btn-danger-custom:active:not(:disabled) {
  transform: scale(0.97);
}

/* Empty State styling */
.empty-state {
  color: var(--text-sub);
}
.empty-icon-wrapper {
  font-size: 3rem;
  color: var(--text-sub);
  opacity: 0.6;
}
.btn-outline-accent {
  border: 1px solid var(--accent-color);
  color: var(--accent-color);
  font-weight: 600;
  background: transparent;
  transition: background 0.2s, color 0.2s;
}
.btn-outline-accent:hover {
  background: var(--accent-color);
  color: #FFFFFF;
}
</style>
