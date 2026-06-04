<template>
  <div class="supervisor-dashboard animate-fade-in">

    <!-- ── Vista general (Dashboard inicial) ── -->
    <SupervisorOverview
      v-if="seccionActiva === 'dashboard'"
      :stats="stats"
      :actividadReciente="actividadReciente"
    />

    <!-- ── Validar CV de Pasante ── -->
    <SupervisorValidarCv
      v-else-if="seccionActiva === 'validar_cv'"
      :cargandoCV="cargandoCV"
      :usuario="usuario"
      @validar="ejecutarValidacionCv"
    />

    <SupervisorSugerirVacante
      v-else-if="seccionActiva === 'ver_vacantes'"
      :usuario="usuario"
    />

    <SupervisorEvaluarInformes
      v-else-if="seccionActiva === 'evaluar_informes'"
      :usuario="usuario"
    />

    <!-- ── Solicitudes de Pasantes ── -->
    <SupervisorSolicitudes
      v-else-if="seccionActiva === 'solicitudes'"
      :usuario="usuario"
    />

    <!-- ── Recomendaciones ── -->
    <SupervisorRecomendaciones
      v-else-if="seccionActiva === 'recomendaciones'"
      :usuario="usuario"
    />

    <!-- ── Mis Pasantes ── -->
    <div v-else-if="seccionActiva === 'mis_pasantes'" class="mis-pasantes-view animate-fade-in">
      <div class="dashboard-section-card">
        <h5 class="card-title mb-4"><i class="bi bi-people-fill text-primary"></i> Mis Pasantes Asignados</h5>
        
        <!-- Filtros Avanzados -->
        <div class="advanced-filters-bar mb-4 p-3 bg-light rounded border d-flex gap-3 flex-wrap align-items-end">
          <div class="flex-grow-1 min-w-200">
            <label class="form-label small fw-bold text-muted mb-1">Buscar</label>
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
              <input type="text" class="form-control" placeholder="Nombre o correo..." v-model="filtros.busqueda">
            </div>
          </div>
          <div>
            <label class="form-label small fw-bold text-muted mb-1">Área Especialidad</label>
            <select class="form-select" v-model="filtros.area">
              <option value="">Todas las áreas</option>
              <option value="desarrollo">Desarrollo de Software</option>
              <option value="diseño">Diseño UI/UX</option>
              <option value="infraestructura">Infraestructura</option>
              <option value="seguridad">Seguridad Informática</option>
            </select>
          </div>
          <div>
            <label class="form-label small fw-bold text-muted mb-1">Estado</label>
            <select class="form-select" v-model="filtros.estado">
              <option value="">Todos los estados</option>
              <option value="en_proceso">En Proceso</option>
              <option value="aprobado">Aprobado / Culminado</option>
            </select>
          </div>
          <div>
            <button class="btn btn-outline-secondary h-100 px-3" @click="limpiarFiltros" title="Limpiar Filtros">
              <i class="bi bi-eraser-fill"></i>
            </button>
          </div>
        </div>

        <div v-if="cargandoPasantes" class="text-center py-5">
          <span class="spinner-border text-primary" style="width:2rem;height:2rem;"></span>
          <p class="mt-3 text-muted">Cargando listado de pasantes...</p>
        </div>
        <div v-else-if="pasantesFiltrados.length === 0" class="text-center py-5 text-muted bg-light rounded">
          <i class="bi bi-person-x d-block fs-1 mb-3 text-secondary"></i>
          <p class="mb-0">No se encontraron pasantes que coincidan con los filtros seleccionados.</p>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover align-middle custom-table">
            <thead class="table-light">
              <tr>
                <th>Pasante</th>
                <th>Área</th>
                <th>Fase Actual</th>
                <th>Estado</th>
                <th v-if="usuario.rol !== 'vice_decano'" class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in pasantesFiltrados" :key="p.pasante_id">
                <td>
                  <div class="fw-semibold text-dark">{{ p.nombre }} {{ p.apellido }}</div>
                  <div class="small text-muted">{{ p.correo }}</div>
                </td>
                <td><span class="area-chip"><i class="bi bi-laptop me-1"></i>{{ p.area }}</span></td>
                <td>
                  <span class="fase-badge">{{ p.fase_actual }}</span>
                </td>
                <td>
                  <span class="estado-badge" :class="'estado-' + p.estado">{{ estadoTexto(p.estado) }}</span>
                </td>
                <td v-if="usuario.rol !== 'vice_decano'" class="text-end">
                  <div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      Acciones <i class="bi bi-chevron-down ms-1"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                      <li><a class="dropdown-item" href="#" @click.prevent="() => {}"><i class="bi bi-chat-dots me-2 text-primary"></i> Enviar Mensaje</a></li>
                      <li><hr class="dropdown-divider"></li>
                      <li v-if="p.estado !== 'aprobado'">
                        <a class="dropdown-item" href="#" @click.prevent="actualizarEstadoPasante(p, 'aprobado')">
                          <i class="bi bi-check-circle me-2 text-success"></i> Marcar Culminado
                        </a>
                      </li>
                      <li v-if="p.estado !== 'en_proceso'">
                        <a class="dropdown-item" href="#" @click.prevent="actualizarEstadoPasante(p, 'en_proceso')">
                          <i class="bi bi-arrow-counterclockwise me-2 text-warning"></i> Revertir a En Proceso
                        </a>
                      </li>
                    </ul>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// Importación de subcomponentes refactorizados
import SupervisorOverview from './SupervisorOverview.vue';
import SupervisorValidarCv from './SupervisorValidarCv.vue';
import SupervisorAsignarVacante from './SupervisorAsignarVacante.vue';
import SupervisorEvaluarInformes from './SupervisorEvaluarInformes.vue';
import SupervisorSolicitudes from './SupervisorSolicitudes.vue';
import SupervisorRecomendaciones from './SupervisorRecomendaciones.vue';
import SupervisorSugerirVacante from './SupervisorSugerirVacante.vue';

const props = defineProps({ seccionActiva: String });

const usuario = ref({});
const stats = ref({ pasantesPendientes: 0, cvsAprobados: 0, vacantesActivas: 0 });
const cargandoCV = ref(false);
const cargandoAsignacion = ref(false);
const cargandoPasantes = ref(false);
const pasantes = ref([]);

const actividadReciente = ref([]);

onMounted(async () => {
  const userJson = localStorage.getItem('usuario');
  if (userJson) {
    usuario.value = JSON.parse(userJson);
    await cargarDashboardStats();
    await cargarPasantes();
  }
});

const cargarDashboardStats = async () => {
  if (!usuario.value.id) return;
  try {
    const res = await axios.get('/api/supervisor/dashboard', {
      headers: { 'X-User-Id': usuario.value.id }
    });
    if (res.data) {
      stats.value = res.data.stats || { pasantesPendientes: 0, cvsAprobados: 0, vacantesActivas: 0 };
      actividadReciente.value = res.data.actividadReciente || [];
    }
  } catch (err) {
    console.error('Error cargando stats:', err);
  }
};

const cargarPasantes = async () => {
  if (!usuario.value.id) return;
  cargandoPasantes.value = true;
  try {
    const res = await axios.get('/api/supervisor/pasantes', {
      headers: { 'X-User-Id': usuario.value.id }
    });
    pasantes.value = (res.data.pasantes || []).map(p => ({
      ...p,
      area: p.area || 'desarrollo'
    }));
  } catch (err) {
    console.error('Error cargando pasantes:', err);
  } finally {
    cargandoPasantes.value = false;
  }
};

// ── Filtros y Computed para Mis Pasantes ──
const filtros = ref({ busqueda: '', area: '', estado: '' });

const limpiarFiltros = () => {
  filtros.value = { busqueda: '', area: '', estado: '' };
};

const pasantesFiltrados = computed(() => {
  return pasantes.value.filter(p => {
    const matchBusqueda = p.nombre.toLowerCase().includes(filtros.value.busqueda.toLowerCase()) || 
                          p.apellido.toLowerCase().includes(filtros.value.busqueda.toLowerCase()) || 
                          p.correo.toLowerCase().includes(filtros.value.busqueda.toLowerCase());
    const matchArea = filtros.value.area === '' || p.area.toLowerCase() === filtros.value.area.toLowerCase();
    const matchEstado = filtros.value.estado === '' || p.estado === filtros.value.estado;
    return matchBusqueda && matchArea && matchEstado;
  });
});

const actualizarEstadoPasante = (pasante, nuevoEstado) => {
  // Simulamos actualización
  pasante.estado = nuevoEstado;
  alertify.success(`Estado de ${pasante.nombre} actualizado a: ${estadoTexto(nuevoEstado)}`);
  stats.value.pasantesPendientes = pasantes.value.filter(p => p.estado === 'en_proceso').length;
};

const ejecutarValidacionCv = async () => {
  await cargarDashboardStats();
  await cargarPasantes();
};

const ejecutarAsignacionVacante = async () => {
  await cargarDashboardStats();
  await cargarPasantes();
};

const estadoTexto = (estado) => {
  if (estado === 'aprobado') return 'Aprobado';
  if (estado === 'en_proceso') return 'En proceso';
  if (estado === 'rechazado') return 'Rechazado';
  return estado;
};
</script>

<style scoped>
.supervisor-dashboard {
  max-width: 1200px;
  margin: 0 auto;
}

.animate-fade-in {
  animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.mis-pasantes-view {
  width: 100%;
}

/* Section Card */
.dashboard-section-card {
  background-color: #ffffff;
  border-radius: 12px;
  padding: 28px;
  box-shadow: 0 4px 18px rgba(0,0,0,0.03);
  border: 1px solid #e2e8f0;
  margin-bottom: 24px;
}
.card-title {
  font-family: 'Lora', serif;
  font-size: 20px;
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}
.text-primary { color: #001374 !important; }

/* Badges */
.area-chip {
  display: inline-block;
  font-size: 11px;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 20px;
  background: #eff6ff;
  color: #1d4ed8;
  text-transform: capitalize;
}

.fase-badge {
  display: inline-block;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 20px;
  background: #f1f5f9;
  color: #475569;
  letter-spacing: 0.05em;
}

.estado-badge {
  display: inline-block;
  font-size: 11px;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 20px;
}
.estado-aprobado { background: #d1fae5; color: #065f46; }
.estado-en_proceso { background: #fef3c7; color: #92400e; }
.estado-rechazado { background: #fee2e2; color: #991b1b; }

.min-w-200 { min-width: 200px; }

/* Dropdown override para que no se corte en la tabla */
.table-responsive { overflow: visible; }
.dropdown-item { font-size: 14px; padding: 8px 16px; font-weight: 500; }
.dropdown-item i { font-size: 16px; vertical-align: middle; }

@media (max-width: 768px) {
  .dashboard-section-card { padding: 18px; }
}
</style>
