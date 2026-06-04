<template>
  <div class="sugerir-vacantes-view animate-fade-in">
    <div class="dashboard-section-card">
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
          <h5 class="card-title mb-1"><i class="bi bi-building-fill-add text-primary"></i> Banco de Vacantes Disponibles</h5>
          <p class="text-muted small mb-0">Explora las ofertas laborales y sugiérelas a tus pasantes para impulsar su inserción profesional.</p>
        </div>
      </div>

      <!-- Filtro por área -->
      <div class="vacantes-filter-bar mb-4">
        <button
          v-for="area in areasDisponibles" :key="area.val"
          class="filter-chip"
          :class="{ active: filtroArea === area.val }"
          @click="cambiarFiltro(area.val)"
        >
          <i :class="area.icon + ' me-1'"></i> {{ area.label }}
        </button>
      </div>

      <!-- Listado de Vacantes -->
      <div v-if="cargandoVacantes" class="text-center py-5">
        <span class="spinner-border text-primary" style="width:2.5rem;height:2.5rem;"></span>
        <p class="mt-3 text-muted">Cargando vacantes...</p>
      </div>

      <div v-else-if="vacantes.length === 0" class="text-center py-5 text-muted bg-light rounded border">
        <i class="bi bi-building-slash d-block fs-1 mb-3 text-secondary"></i>
        <p class="mb-0">No hay vacantes activas en este momento para el área seleccionada.</p>
      </div>

      <div v-else class="row">
        <div v-for="v in vacantes" :key="v.id" class="col-md-6 mb-4">
          <div class="vacante-card border rounded p-4 h-100 d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                <span class="vacante-empresa-badge">
                  <i class="bi bi-building me-2"></i>{{ v.empresa }}
                </span>
                <span class="area-chip" :class="'area-' + v.area">{{ v.area }}</span>
              </div>
              <p class="vacante-descripcion text-muted small mt-3">{{ v.descripcion }}</p>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 border-top pt-3 flex-wrap gap-2">
              <span class="text-secondary small"><i class="bi bi-briefcase me-1"></i> Puesto Activo</span>
              <button v-if="usuario.rol !== 'vice_decano'" class="btn btn-outline-primary btn-sm px-4" @click="abrirModalSugerencia(v)">
                <i class="bi bi-lightbulb-fill me-1"></i> Sugerir a Pasante
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── MODAL SELECCIONAR PASANTE ── -->
    <div class="modal fade show" v-if="modalSugerencia.abierto" style="display: block; background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(2px); z-index: 1050;" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="modal-title fw-bold text-dark"><i class="bi bi-share text-primary me-2"></i> Sugerir Vacante</h5>
            <button type="button" class="btn-close" @click="cerrarModal" :disabled="sugiriendo"></button>
          </div>
          <div class="modal-body py-3">
            <p class="small text-muted mb-3">Sugerirás la vacante de <strong>{{ modalSugerencia.vacante.empresa }}</strong> ({{ modalSugerencia.vacante.area }}) al siguiente estudiante:</p>

            <div v-if="cargandoPasantes" class="text-center py-4">
              <span class="spinner-border text-primary spinner-border-sm"></span>
              <p class="text-muted small mt-2">Cargando tus pasantes...</p>
            </div>

            <div v-else-if="pasantes.length === 0" class="text-center py-4 bg-light rounded border text-muted">
              <i class="bi bi-people d-block fs-2 mb-2 text-secondary"></i>
              <span>No tienes pasantes asignados bajo tu supervisión actualmente.</span>
            </div>

            <div v-else>
              <label class="form-label small fw-bold text-muted mb-2">Selecciona un Pasante</label>
              <div class="pasantes-list-group border rounded overflow-hidden" style="max-height: 250px; overflow-y: auto;">
                <div 
                  v-for="p in pasantes" :key="p.id"
                  class="pasante-select-item p-3 border-bottom d-flex align-items-center gap-3 cursor-pointer"
                  :class="{ selected: pasanteSeleccionadoId === p.id }"
                  @click="pasanteSeleccionadoId = p.id"
                >
                  <div class="checkbox-container">
                    <i v-if="pasanteSeleccionadoId === p.id" class="bi bi-check-circle-fill text-success fs-5"></i>
                    <i v-else class="bi bi-circle text-muted fs-5"></i>
                  </div>
                  <div>
                    <div class="fw-semibold text-dark small">{{ p.nombre }}</div>
                    <div class="text-muted" style="font-size: 11px;">{{ p.correo }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer border-top-0 pt-0 d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary px-3" @click="cerrarModal" :disabled="sugiriendo">Cancelar</button>
            <button type="button" class="btn btn-primary px-4" @click="enviarSugerencia" :disabled="!pasanteSeleccionadoId || sugiriendo">
              <span class="spinner-border spinner-border-sm me-1" v-if="sugiriendo"></span>
              {{ sugiriendo ? 'Enviando...' : 'Enviar Sugerencia' }}
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

const vacantes = ref([]);
const pasantes = ref([]);
const cargandoVacantes = ref(false);
const cargandoPasantes = ref(false);
const sugiriendo = ref(false);
const filtroArea = ref('');
const pasanteSeleccionadoId = ref(null);

const modalSugerencia = ref({
  abierto: false,
  vacante: {}
});

const areasDisponibles = [
  { val: '', label: 'Todas', icon: 'bi bi-grid' },
  { val: 'desarrollo', label: 'Desarrollo', icon: 'bi bi-code-slash' },
  { val: 'diseño', label: 'Diseño', icon: 'bi bi-palette' },
  { val: 'infraestructura', label: 'Infraestructura', icon: 'bi bi-server' },
  { val: 'seguridad', label: 'Seguridad', icon: 'bi bi-shield-check' },
];

onMounted(() => {
  cargarVacantes();
  cargarPasantes();
});

const cargarVacantes = async () => {
  cargandoVacantes.value = true;
  try {
    const params = filtroArea.value ? { area: filtroArea.value } : {};
    const res = await axios.get('/api/supervisor/vacantes', { 
      params,
      headers: { 'X-User-Id': props.usuario?.id || 3 }
    });
    vacantes.value = res.data.vacantes || [];
  } catch (err) {
    console.error('Error al cargar vacantes:', err);
    alertify.error('Error al cargar las vacantes.');
  } finally {
    cargandoVacantes.value = false;
  }
};

const cargarPasantes = async () => {
  cargandoPasantes.value = true;
  try {
    const res = await axios.get('/api/supervisor/pasantes', {
      headers: { 'X-User-Id': props.usuario?.id || 3 }
    });
    pasantes.value = res.data.pasantes || [];
  } catch (err) {
    console.error('Error cargando pasantes:', err);
  } finally {
    cargandoPasantes.value = false;
  }
};

const cambiarFiltro = (area) => {
  filtroArea.value = area;
  cargarVacantes();
};

const abrirModalSugerencia = (vacante) => {
  modalSugerencia.value = {
    abierto: true,
    vacante: vacante
  };
  pasanteSeleccionadoId.value = pasantes.value.length === 1 ? pasantes.value[0].id : null;
};

const cerrarModal = () => {
  modalSugerencia.value.abierto = false;
  modalSugerencia.value.vacante = {};
  pasanteSeleccionadoId.value = null;
};

const enviarSugerencia = async () => {
  if (!pasanteSeleccionadoId.value || !modalSugerencia.value.vacante.id) return;
  sugiriendo.value = true;
  try {
    await axios.post('/api/supervisor/sugerir-vacante', {
      pasante_id: pasanteSeleccionadoId.value,
      vacante_id: modalSugerencia.value.vacante.id
    }, {
      headers: { 'X-User-Id': props.usuario?.id || 3 }
    });

    alertify.success('Vacante sugerida al pasante correctamente.');
    cerrarModal();
  } catch (err) {
    console.error('Error enviando sugerencia:', err);
    if (err.response?.status === 409) {
      alertify.warning(err.response.data.mensaje || 'Esta vacante ya está en proceso para este pasante.');
    } else {
      alertify.error('Ocurrió un error al enviar la sugerencia.');
    }
  } finally {
    sugiriendo.value = false;
  }
};
</script>

<style scoped>
.sugerir-vacantes-view {
  width: 100%;
}
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

/* Filter chips */
.vacantes-filter-bar {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}
.filter-chip {
  display: inline-flex;
  align-items: center;
  padding: 8px 18px;
  border-radius: 30px;
  border: 1.5px solid #e2e8f0;
  background: #ffffff;
  color: #475569;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.filter-chip:hover {
  border-color: #001374;
  color: #001374;
}
.filter-chip.active {
  background: #001374;
  color: #ffffff;
  border-color: #001374;
}

/* Vacancy Card */
.vacante-card {
  background: #ffffff;
  transition: all 0.25s;
  box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}
.vacante-card:hover {
  box-shadow: 0 8px 20px rgba(0, 19, 116, 0.06);
  border-color: #c7d2fe !important;
  transform: translateY(-1px);
}

.vacante-empresa-badge {
  font-size: 15px;
  font-weight: 700;
  color: #0f172a;
}

.area-chip {
  font-size: 10px;
  font-weight: 600;
  padding: 3px 12px;
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.area-desarrollo  { background: #eff6ff; color: #1d4ed8; }
.area-diseño     { background: #faf5ff; color: #7c3aed; }
.area-infraestructura { background: #fff7ed; color: #c2410c; }
.area-seguridad   { background: #fef2f2; color: #b91c1c; }

.vacante-descripcion {
  line-height: 1.6;
}

/* Modal and Select items */
.pasante-select-item {
  transition: background-color 0.15s;
}
.pasante-select-item:hover {
  background-color: #f8fafc;
}
.pasante-select-item.selected {
  background-color: #f1f5f9;
}
.pasante-select-item:last-child {
  border-bottom: none !important;
}
.cursor-pointer {
  cursor: pointer;
}
</style>
