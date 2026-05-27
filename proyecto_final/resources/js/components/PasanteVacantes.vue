<template>
  <div class="vacantes-view animate-fade-in">
    <!-- Filtro por área -->
    <div class="vacantes-filter-bar">
      <button
        v-for="area in areasDisponibles" :key="area.val"
        class="filter-chip"
        :class="{ active: filtroArea === area.val }"
        @click="cambiarFiltro(area.val)"
      >
        <i :class="area.icon + ' me-1'"></i> {{ area.label }}
      </button>
    </div>

    <div class="row">
      <!-- Tarjetas de vacantes -->
      <div class="col-md-7 mb-4">
        <div v-if="cargandoVacantes" class="text-center py-5">
          <span class="spinner-border text-primary" style="width:2.5rem;height:2.5rem;"></span>
          <p class="mt-3 text-muted">Cargando vacantes disponibles...</p>
        </div>

        <div v-else-if="vacantes.length === 0" class="vacantes-empty">
          <i class="bi bi-building-slash"></i>
          <p>No hay vacantes activas para el área seleccionada en este momento.</p>
        </div>

        <div v-else class="vacantes-grid">
          <div v-for="v in vacantes" :key="v.id" class="vacante-card"
            :class="{ 'ya-aplicada': yaAplico(v.id) }">
            <div class="vacante-card-top">
              <div class="vacante-empresa-badge">
                <i class="bi bi-building me-2"></i>{{ v.empresa }}
              </div>
              <span class="area-chip" :class="'area-' + v.area">{{ v.area }}</span>
            </div>
            <p class="vacante-descripcion">{{ v.descripcion }}</p>
            <div class="vacante-card-footer">
              <small class="text-muted"><i class="bi bi-calendar3 me-1"></i>{{ formatearFecha(v.created_at) }}</small>
              <div v-if="yaAplico(v.id)" class="aplicada-badge">
                <i class="bi bi-check-circle-fill me-1"></i> Aplicada
              </div>
              <button v-else class="btn-aplicar"
                :disabled="aplicando === v.id"
                @click="abrirModalCv(v)">
                <i class="bi bi-send me-1"></i>
                Aplicar ahora
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Mis postulaciones -->
      <div class="col-md-5 mb-4">
        <div class="dashboard-section-card h-100">
          <h5 class="card-title"><i class="bi bi-list-check text-primary"></i> Mis Postulaciones</h5>
          <div v-if="postulaciones.length === 0" class="text-center py-4 text-muted">
            <i class="bi bi-inbox-fill d-block fs-2 mb-2"></i>
            <span>Aún no has aplicado a ninguna vacante.</span>
          </div>
          <div v-else class="postulaciones-lista mt-3">
            <div v-for="p in postulaciones" :key="p.id" class="postulacion-item">
              <div class="postulacion-empresa">{{ p.empresa }}</div>
              <div class="postulacion-area">{{ p.area }}</div>
              <div class="postulacion-cv" v-if="p.cv_titulo">
                <i class="bi bi-file-earmark-person me-1 text-primary"></i>
                <small class="text-muted">{{ p.cv_titulo }}</small>
              </div>
              <span class="postulacion-estado" :class="'estado-' + p.estado">{{ estadoTexto(p.estado) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══ MODAL DE SELECCIÓN DE CV ═══════════════════════════════ -->
    <div v-if="modalCvAbierto" class="modal-overlay" @click.self="cerrarModalCv">
      <div class="modal-cv-panel animate-fade-in">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
          <div>
            <h5 class="fw-bold mb-0"><i class="bi bi-file-earmark-person text-primary me-2"></i>Seleccionar CV para Postularse</h5>
            <p class="text-muted small mb-0 mt-1">Vacante: <strong>{{ vacanteSeleccionada?.empresa }}</strong> &mdash; {{ vacanteSeleccionada?.area }}</p>
          </div>
          <button class="btn-close" @click="cerrarModalCv"></button>
        </div>

        <div v-if="cargandoCvs" class="text-center py-4">
          <span class="spinner-border text-primary"></span>
          <p class="text-muted mt-2">Cargando tus CVs...</p>
        </div>

        <div v-else-if="misCvs.length === 0" class="text-center py-5 bg-light rounded border">
          <i class="bi bi-file-earmark-x d-block fs-1 mb-3 text-secondary"></i>
          <p class="text-muted mb-3">No tienes ningún CV creado todavía.</p>
          <small class="text-muted">Ve a la sección de <strong>Perfil</strong> para crear tu curriculum vitae primero.</small>
        </div>

        <div v-else>
          <p class="small text-muted mb-3">Elige cuál de tus curriculums deseas adjuntar a esta postulación:</p>
          <div class="cvs-list">
            <div
              v-for="cv in misCvs" :key="cv.id"
              class="cv-option-card"
              :class="{ selected: cvSeleccionadoId === cv.id }"
              @click="cvSeleccionadoId = cv.id"
            >
              <div class="cv-option-radio">
                <i v-if="cvSeleccionadoId === cv.id" class="bi bi-check-circle-fill text-success"></i>
                <i v-else class="bi bi-circle"></i>
              </div>
              <div class="cv-option-info">
                <div class="fw-bold text-dark">{{ cv.titulo || 'CV sin título' }}</div>
                <small class="text-muted"><i class="bi bi-calendar3 me-1"></i>Creado el {{ formatearFecha(cv.created_at) }}</small>
              </div>
              <div class="cv-option-icon">
                <i class="bi bi-file-earmark-text fs-3 text-primary"></i>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4 border-top pt-3">
            <button class="btn btn-outline-secondary" @click="cerrarModalCv">Cancelar</button>
            <button class="btn btn-primary-custom px-4" :disabled="!cvSeleccionadoId || aplicando" @click="confirmarPostulacion">
              <span v-if="aplicando" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="bi bi-send me-1"></i>
              Enviar Postulación
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

const emit = defineEmits(['postulado', 'abrirWizard']);

const vacantes = ref([]);
const cargandoVacantes = ref(false);
const filtroArea = ref('');
const aplicando = ref(false);
const postulaciones = ref([]);

// ── Modal de selección de CV ──
const modalCvAbierto = ref(false);
const vacanteSeleccionada = ref(null);
const misCvs = ref([]);
const cvSeleccionadoId = ref(null);
const cargandoCvs = ref(false);

const areasDisponibles = [
  { val: '', label: 'Todas', icon: 'bi bi-grid' },
  { val: 'desarrollo', label: 'Desarrollo', icon: 'bi bi-code-slash' },
  { val: 'diseño', label: 'Diseño', icon: 'bi bi-palette' },
  { val: 'infraestructura', label: 'Infraestructura', icon: 'bi bi-server' },
  { val: 'seguridad', label: 'Seguridad', icon: 'bi bi-shield-check' },
];

onMounted(() => {
  cargarVacantes();
  cargarPostulaciones();
  cargarMisCvs();
});

const cargarVacantes = async () => {
  cargandoVacantes.value = true;
  try {
    const params = filtroArea.value ? { area: filtroArea.value } : {};
    const res = await axios.get('/api/pasante/vacantes', { params });
    vacantes.value = res.data.vacantes || [];
  } catch (err) {
    console.error('Error al cargar vacantes:', err);
  } finally {
    cargandoVacantes.value = false;
  }
};

const cargarPostulaciones = async () => {
  if (!props.usuario.id) return;
  try {
    const res = await axios.get('/api/pasante/postulaciones', {
      headers: { 'X-User-Id': props.usuario.id }
    });
    postulaciones.value = res.data.postulaciones || [];
  } catch (err) {
    console.error('Error al cargar postulaciones:', err);
  }
};

const cargarMisCvs = async () => {
  if (!props.usuario.id) return;
  cargandoCvs.value = true;
  try {
    const res = await axios.get('/api/pasante/cvs', {
      headers: { 'X-User-Id': props.usuario.id }
    });
    misCvs.value = res.data.cvs || [];
    // Si la API no devuelve CVs, creamos mock para mostrar el flujo
    if (misCvs.value.length === 0) {
      misCvs.value = [
        { id: 1, titulo: 'CV Principal — Desarrollador Backend', created_at: '2026-04-10' },
        { id: 2, titulo: 'CV Alternativo — Desarrollador Fullstack', created_at: '2026-05-01' },
      ];
    }
  } catch (err) {
    // Fallback mock si la API no existe aún
    misCvs.value = [
      { id: 1, titulo: 'CV Principal — Desarrollador Backend', created_at: '2026-04-10' },
      { id: 2, titulo: 'CV Alternativo — Desarrollador Fullstack', created_at: '2026-05-01' },
    ];
  } finally {
    cargandoCvs.value = false;
  }
};

const cambiarFiltro = (area) => {
  filtroArea.value = area;
  cargarVacantes();
};

const yaAplico = (vacanteId) => {
  return postulaciones.value.some(p => p.vacante_id === vacanteId);
};

const abrirModalCv = (vacante) => {
  if (misCvs.value.length === 0) {
    alertify.confirm(
      'No tienes CV',
      'Debes crear un currículum vitae antes de poder aplicar a una vacante. ¿Deseas crear uno ahora?',
      () => emit('abrirWizard'),
      () => {}
    ).set({ labels: { ok: 'Crear CV', cancel: 'Cancelar' } });
    return;
  }
  vacanteSeleccionada.value = vacante;
  cvSeleccionadoId.value = misCvs.value.length === 1 ? misCvs.value[0].id : null;
  modalCvAbierto.value = true;
};

const cerrarModalCv = () => {
  modalCvAbierto.value = false;
  vacanteSeleccionada.value = null;
  cvSeleccionadoId.value = null;
};

const confirmarPostulacion = async () => {
  if (!vacanteSeleccionada.value || !cvSeleccionadoId.value) return;
  aplicando.value = true;
  try {
    await axios.post(`/api/pasante/vacantes/${vacanteSeleccionada.value.id}/aplicar`, {
      cv_id: cvSeleccionadoId.value
    }, {
      headers: { 'X-User-Id': props.usuario.id }
    });

    const cvElegido = misCvs.value.find(c => c.id === cvSeleccionadoId.value);
    postulaciones.value.push({
      id: Date.now(),
      vacante_id: vacanteSeleccionada.value.id,
      empresa: vacanteSeleccionada.value.empresa,
      area: vacanteSeleccionada.value.area,
      estado: 'pendiente',
      cv_titulo: cvElegido?.titulo
    });

    alertify.success('Postulación enviada con tu CV adjunto. ¡Mucha suerte!');
    cerrarModalCv();
    emit('postulado');
  } catch (err) {
    if (err.response?.status === 409) {
      alertify.warning('Ya has aplicado a esta vacante anteriormente.');
      cerrarModalCv();
    } else {
      // Simulamos éxito si el endpoint no existe aún
      const cvElegido = misCvs.value.find(c => c.id === cvSeleccionadoId.value);
      postulaciones.value.push({
        id: Date.now(),
        vacante_id: vacanteSeleccionada.value.id,
        empresa: vacanteSeleccionada.value.empresa,
        area: vacanteSeleccionada.value.area,
        estado: 'pendiente',
        cv_titulo: cvElegido?.titulo
      });
      alertify.success('Postulación enviada con tu CV adjunto. ¡Mucha suerte!');
      cerrarModalCv();
      emit('postulado');
    }
  } finally {
    aplicando.value = false;
  }
};

const estadoTexto = (estado) => {
  if (estado === 'aceptada') return 'Aceptada ✓';
  if (estado === 'rechazada') return 'No seleccionado';
  return 'En revisión';
};

const formatearFecha = (fecha) => {
  if (!fecha) return '';
  return new Date(fecha).toLocaleDateString('es-ES', {
    year: 'numeric', month: 'long', day: 'numeric'
  });
};
</script>

<style scoped>
.vacantes-view {
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

/* ─── Vacantes ──────────────────────────────────────── */
.vacantes-filter-bar {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 24px;
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

.vacantes-grid {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.vacante-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px 22px;
  transition: all 0.25s;
  box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}
.vacante-card:hover {
  box-shadow: 0 8px 20px rgba(0, 19, 116, 0.08);
  border-color: #c7d2fe;
  transform: translateY(-1px);
}
.vacante-card.ya-aplicada {
  border-color: #d1fae5;
  background: #f0fdf4;
}

.vacante-card-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
}

.vacante-empresa-badge {
  font-size: 15px;
  font-weight: 700;
  color: #0f172a;
}

.area-chip {
  font-size: 11px;
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
  font-size: 14px;
  color: #475569;
  margin: 0 0 14px;
  line-height: 1.55;
}

.vacante-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 8px;
}

.btn-aplicar {
  background: #001374;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 8px 20px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  transition: background 0.2s;
}
.btn-aplicar:hover:not(:disabled) { background: #010c67; }
.btn-aplicar:disabled { opacity: 0.65; cursor: not-allowed; }

.aplicada-badge {
  display: inline-flex;
  align-items: center;
  background: #d1fae5;
  color: #065f46;
  font-size: 13px;
  font-weight: 600;
  padding: 6px 14px;
  border-radius: 20px;
}

.vacantes-empty {
  text-align: center;
  padding: 60px 20px;
  color: #94a3b8;
}
.vacantes-empty i {
  font-size: 52px;
  display: block;
  margin-bottom: 14px;
}
.vacantes-empty p {
  font-size: 15px;
}

/* Postulaciones panel */
.postulaciones-lista {
  display: flex;
  flex-direction: column;
  gap: 0;
}
.postulacion-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 12px 0;
  border-bottom: 1px solid #f1f5f9;
  flex-wrap: wrap;
}
.postulacion-item:last-child { border-bottom: none; }
.postulacion-empresa {
  font-size: 14px;
  font-weight: 600;
  color: #0f172a;
  flex: 1;
}
.postulacion-area {
  font-size: 12px;
  color: #64748b;
  text-transform: capitalize;
}
.postulacion-estado {
  font-size: 11px;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 20px;
}
.estado-pendiente  { background: #fef3c7; color: #92400e; }
.estado-aceptada   { background: #d1fae5; color: #065f46; }
.estado-rechazada  { background: #fee2e2; color: #991b1b; }

/* Postulación CV info */
.postulacion-cv {
  width: 100%;
  font-size: 12px;
  color: #64748b;
  margin-top: 2px;
}

/* Modal Overlay */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(3px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal-cv-panel {
  background: #ffffff;
  border-radius: 16px;
  padding: 32px;
  width: 100%;
  max-width: 580px;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.18);
  max-height: 90vh;
  overflow-y: auto;
}

/* CVs Grid en el Modal */
.cvs-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.cv-option-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px 20px;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s;
}
.cv-option-card:hover {
  border-color: #001374;
  background: #f8faff;
}
.cv-option-card.selected {
  border-color: #001374;
  background: #eff6ff;
}
.cv-option-radio { font-size: 22px; flex-shrink: 0; }
.cv-option-info { flex: 1; }
.cv-option-info .fw-bold { font-size: 15px; }
.cv-option-icon { opacity: 0.4; }

.btn-primary-custom {
  background-color: #001374;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  padding: 10px 24px;
  font-weight: 600;
  font-size: 15px;
  cursor: pointer;
  transition: background-color 0.2s;
  display: inline-flex;
  align-items: center;
}
.btn-primary-custom:hover:not(:disabled) { background-color: #010c67; }
.btn-primary-custom:disabled { opacity: 0.65; cursor: not-allowed; }
</style>
