<template>
  <div class="informes-view animate-fade-in">
    
    <!-- ============================================== -->
    <!-- VISTA PRINCIPAL: HISTORIAL DE INFORMES         -->
    <!-- ============================================== -->
    <div v-if="!vistaCreacion" class="historial-container">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <h4 class="fw-bold m-0"><i class="bi bi-journal-text text-primary"></i> Mis Informes</h4>
        <button class="btn btn-accent px-4 py-2" @click="vistaCreacion = true">
          <i class="bi bi-plus-circle me-2"></i>Crear Nuevo Informe
        </button>
      </div>

      <div class="dashboard-section-card">
        <!-- Barra de búsqueda multiparamétrica -->
        <div class="search-bar-wrapper mb-4">
          <div class="input-group">
            <span class="input-group-text bg-white text-muted border-end-0">
              <i class="bi bi-search"></i>
            </span>
            <input 
              type="text" 
              class="form-control border-start-0 ps-0 search-input" 
              placeholder="Buscar por nombre del informe, tipo o estado..." 
              v-model="filtroBusqueda"
            >
          </div>
        </div>
        
        <div v-if="informesFiltrados.length === 0" class="text-center py-5 text-muted bg-light rounded border">
          <i class="bi bi-inbox-fill d-block fs-1 mb-3 text-secondary"></i>
          <span v-if="filtroBusqueda">No se encontraron informes que coincidan con la búsqueda.</span>
          <span v-else>No has creado ningún informe todavía. Haz clic en "Crear Nuevo Informe" para empezar.</span>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-hover align-middle custom-table">
            <thead class="table-light">
              <tr>
                <th>Nombre del Informe</th>
                <th>Tipo</th>
                <th>Fecha de Creación</th>
                <th>Estado</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="inf in informesFiltrados" :key="inf.id">
                <td class="fw-semibold text-dark">{{ inf.nombre || ('Reporte de ' + (inf.horas || 0) + ' horas') }}</td>
                <td>
                  <span class="badge rounded-pill" :class="inf.tipo === 'final' ? 'bg-primary' : 'bg-secondary'">
                    {{ inf.tipo === 'final' ? 'Informe Final' : 'Informe Mensual' }}
                  </span>
                </td>
                <td>{{ formatearFecha(inf.created_at) }}</td>
                <td>
                  <span class="badge" :class="getEstadoBadgeClass(inf.estado)">
                    <i :class="getEstadoIconClass(inf.estado)" class="me-1"></i>
                    {{ getEstadoTexto(inf.estado) }}
                  </span>
                </td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-primary me-1" title="Ver Detalles" @click="verDetalles(inf)">
                    <i class="bi bi-eye"></i>
                  </button>
                  <button v-if="inf.estado === 'en_espera' || inf.estado === 'revision'" class="btn btn-sm btn-outline-warning me-1" title="Editar Informe" @click="editarInforme(inf)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button v-if="inf.estado === 'en_espera' || inf.estado === 'revision'" class="btn btn-sm btn-outline-danger" title="Eliminar Informe" @click="eliminarInforme(inf.id)">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ============================================== -->
    <!-- VISTA SECUNDARIA: FORMULARIO DE CREACIÓN       -->
    <!-- ============================================== -->
    <div v-else class="creacion-container animate-fade-in">
      <div class="dashboard-section-card max-w-900 mx-auto">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 border-bottom pb-3">
          <h4 class="fw-bold m-0"><i class="bi bi-pencil-square text-accent"></i> {{ editandoId ? 'Editar Informe' : 'Redactar Nuevo Informe' }}</h4>
          <div class="action-buttons d-flex gap-2">
            <button class="btn-ds-back-outline" @click="cerrarFormulario"><i class="bi bi-arrow-left me-1"></i> Atrás</button>
          </div>
        </div>

        <form @submit.prevent="guardarInforme">
          <div class="row">
            <div class="col-md-6 mb-4">
              <label class="form-label fw-bold">Nombre del Informe</label>
              <input type="text" class="form-control form-control-lg" v-model="nuevoInforme.nombre" placeholder="Ej: Informe Mensual - Mayo 2026" required>
            </div>
            <div class="col-md-3 mb-4">
              <label class="form-label fw-bold">Tipo de Informe</label>
              <select class="form-select form-select-lg" v-model="nuevoInforme.tipo" required>
                <option value="parcial">Mensual / Avance</option>
                <option value="final">Informe Final</option>
              </select>
            </div>
            <div class="col-md-3 mb-4">
              <label class="form-label fw-bold">Horas a Reportar</label>
              <input
                type="number"
                min="0.01"
                step="0.01"
                max="600"
                class="form-control form-control-lg"
                :class="{ 'is-invalid': horasError }"
                v-model.number="nuevoInforme.horas"
                placeholder="Ej: 80"
                required
                @input="horasError = false"
              >
              <div class="invalid-feedback" v-if="horasError">Debes ingresar las horas (mín. 0.01).</div>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-bold">1. Objetivos del Período</label>
            <p class="small text-muted mb-2">Describe brevemente cuáles eran las metas principales trazadas para este periodo de trabajo.</p>
            <textarea class="form-control" rows="3" v-model="nuevoInforme.objetivos" placeholder="Ej: Finalizar el diseño de la base de datos y construir las API REST..." required></textarea>
          </div>

          <div class="mb-4">
            <label class="form-label fw-bold">2. Actividades Realizadas</label>
            <p class="small text-muted mb-2">Detalla las tareas ejecutadas. Se recomienda usar guiones o viñetas para mejor lectura.</p>
            <textarea class="form-control" rows="6" v-model="nuevoInforme.actividades" placeholder="- Configuración del servidor Ubuntu.&#10;- Diseño de UI en Figma.&#10;- Programación de controladores en Laravel..." required></textarea>
          </div>

          <div class="mb-4">
            <label class="form-label fw-bold">3. Logros y Conclusiones</label>
            <p class="small text-muted mb-2">Menciona qué habilidades adquiriste y cuáles fueron los resultados finales de las actividades.</p>
            <textarea class="form-control" rows="4" v-model="nuevoInforme.conclusiones" placeholder="Se logró optimizar el tiempo de carga en un 20%. Aprendí a utilizar Docker en entornos de producción..." required></textarea>
          </div>

          <div class="border-top pt-4 mt-2 d-flex justify-content-end">
            <button type="submit" class="btn btn-accent px-5 py-2 fs-5 d-flex align-items-center" :disabled="guardando">
              <span v-if="guardando" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="bi bi-save2 me-2"></i>
              {{ guardando ? 'Guardando...' : 'Guardar Informe' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ============================================== -->
    <!-- DETALLES DEL INFORME (MODAL)                  -->
    <!-- ============================================== -->
    <div v-if="mostrarDetalles" class="modal-detalle-overlay animate-fade-in" @click.self="cerrarDetalles">
      <div class="modal-detalle-card">
        <div class="modal-detalle-header">
          <h5 class="fw-bold m-0"><i class="bi bi-file-earmark-text-fill text-accent"></i> {{ informeDetalle?.nombre || 'Detalles del Informe' }}</h5>
          <button class="btn-cerrar-modal" @click="cerrarDetalles"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-detalle-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <span class="small text-muted d-block fw-bold">Tipo de Informe</span>
              <span class="badge rounded-pill mt-1" :class="informeDetalle?.tipo === 'final' ? 'bg-primary' : 'bg-secondary'">
                {{ informeDetalle?.tipo === 'final' ? 'Informe Final' : 'Informe Mensual' }}
              </span>
            </div>
            <div class="col-md-6">
              <span class="small text-muted d-block fw-bold">Horas Reportadas</span>
              <span class="fw-bold text-dark mt-1 d-block fs-5">{{ informeDetalle?.horas }} horas</span>
            </div>
          </div>
          
          <div class="mb-3">
            <span class="small text-muted d-block fw-bold mb-1">1. Objetivos del Período:</span>
            <div class="detalle-texto-caja">{{ informeDetalle?.objetivos || 'No registrados' }}</div>
          </div>

          <div class="mb-3">
            <span class="small text-muted d-block fw-bold mb-1">2. Actividades Realizadas:</span>
            <div class="detalle-texto-caja pre-wrap">{{ informeDetalle?.actividades || 'No registradas' }}</div>
          </div>

          <div class="mb-3">
            <span class="small text-muted d-block fw-bold mb-1">3. Logros y Conclusiones:</span>
            <div class="detalle-texto-caja">{{ informeDetalle?.conclusiones || 'No registradas' }}</div>
          </div>

          <div class="mb-3" v-if="informeDetalle?.observaciones">
            <span class="small text-danger d-block fw-bold mb-1">Observaciones del Supervisor:</span>
            <div class="detalle-texto-caja border-danger-subtle bg-danger-subtle text-danger">{{ informeDetalle?.observaciones }}</div>
          </div>
        </div>
        <div class="modal-detalle-footer d-flex justify-content-between align-items-center">
          <a :href="informeDetalle?.archivo_url" target="_blank" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i> Ver PDF Oficial</a>
          <button class="btn btn-secondary btn-sm" @click="cerrarDetalles">Cerrar</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  usuario: { type: Object, default: () => ({}) },
  informes: { type: Array, default: () => [] },
  cargandoInformes: { type: Boolean, default: false }
});

const emit = defineEmits(['informeEnviado']);

const vistaCreacion = ref(false);
const editandoId = ref(null);
const guardando = ref(false);
const filtroBusqueda = ref('');
const horasError = ref(false);

const mostrarDetalles = ref(false);
const informeDetalle = ref(null);

const verDetalles = (inf) => {
  informeDetalle.value = inf;
  mostrarDetalles.value = true;
};

const cerrarDetalles = () => {
  informeDetalle.value = null;
  mostrarDetalles.value = false;
};

// Modelo reactivo para el nuevo informe
const nuevoInforme = ref({
  nombre: '',
  tipo: 'parcial',
  horas: null,
  objetivos: '',
  actividades: '',
  conclusiones: ''
});

// Computed que filtra la tabla dinámicamente
const informesFiltrados = computed(() => {
  if (!props.informes) return [];
  if (!filtroBusqueda.value) return props.informes;
  
  const query = filtroBusqueda.value.toLowerCase();
  return props.informes.filter(inf => {
    return (
      (inf.tipo && inf.tipo.toLowerCase().includes(query)) ||
      (inf.estado && getEstadoTexto(inf.estado).toLowerCase().includes(query))
    );
  });
});

const cerrarFormulario = () => {
  vistaCreacion.value = false;
  editandoId.value = null;
  horasError.value = false;
  nuevoInforme.value = { nombre: '', tipo: 'parcial', horas: null, objetivos: '', actividades: '', conclusiones: '' };
};

const editarInforme = (inf) => {
  editandoId.value = inf.id;
  nuevoInforme.value = {
    nombre: inf.nombre || '',
    tipo: inf.tipo,
    horas: inf.horas,
    objetivos: inf.objetivos || '',
    actividades: inf.actividades || '',
    conclusiones: inf.conclusiones || ''
  };
  vistaCreacion.value = true;
};

const eliminarInforme = (id) => {
  alertify.confirm('Eliminar Informe', '¿Estás seguro de eliminar este informe pendiente?', async () => {
    try {
      await axios.delete(`/api/pasante/informes/${id}`, { headers: { 'X-User-Id': props.usuario.id } });
      alertify.success('Informe eliminado.');
      emit('informeEnviado');
    } catch (err) {
      alertify.error('Error al eliminar informe.');
    }
  }, () => {});
};

const guardarInforme = async () => {
  // Validacion de horas en el cliente
  const horas = Number(nuevoInforme.value.horas);
  if (!horas || horas < 0.01) {
    horasError.value = true;
    alertify.error('Debes ingresar las horas del informe (mínimo 0.01).');
    return;
  }

  guardando.value = true;
  try {
    const data = {
      tipo: nuevoInforme.value.tipo,
      horas: horas,
      nombre: nuevoInforme.value.nombre,
      objetivos: nuevoInforme.value.objetivos,
      actividades: nuevoInforme.value.actividades,
      conclusiones: nuevoInforme.value.conclusiones
    };
    
    if (editandoId.value) {
      await axios.put(`/api/pasante/informes/${editandoId.value}`, data, { headers: { 'X-User-Id': props.usuario.id } });
      alertify.success('Informe actualizado correctamente.');
    } else {
      await axios.post('/api/pasante/informes', data, { headers: { 'X-User-Id': props.usuario.id } });
      alertify.success('Informe subido y en revisión.');
    }
    
    cerrarFormulario();
    emit('informeEnviado');
  } catch (err) {
    console.error('Error al guardar informe:', err);
    const msg = err.response?.data?.mensaje || err.response?.data?.message || 'Error al guardar el informe.';
    alertify.error(msg);
  } finally {
    guardando.value = false;
  }
};

// Utilidades UI
const formatearFecha = (fecha) => {
  if (!fecha) return '';
  return new Date(fecha).toLocaleDateString('es-ES', {
    year: 'numeric', month: 'short', day: 'numeric'
  });
};

const getEstadoBadgeClass = (estado) => {
  if (estado === 'aprobado') return 'bg-success';
  if (estado === 'rechazado' || estado === 'correccion') return 'bg-danger';
  return 'bg-warning text-dark';
};

const getEstadoIconClass = (estado) => {
  if (estado === 'aprobado') return 'bi-check-circle-fill';
  if (estado === 'rechazado' || estado === 'correccion') return 'bi-x-circle-fill';
  return 'bi-clock-fill';
};

const getEstadoTexto = (estado) => {
  if (estado === 'aprobado') return 'Aprobado';
  if (estado === 'rechazado' || estado === 'correccion') return 'Corrección requerida';
  return 'En revisión';
};
</script>

<style scoped>
.informes-view {
  width: 100%;
}

.animate-fade-in {
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.dashboard-section-card {
  background-color: #ffffff;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
  border: 1px solid #e2e8f0;
}

.max-w-900 {
  max-width: 900px;
}

.text-primary { color: #001374 !important; }
.text-accent { color: var(--accent, #67000F) !important; }

/* Botones Principales */
.btn-accent {
  background-color: var(--accent, #67000F);
  color: white;
  font-weight: 600;
  border: none;
  transition: all 0.2s;
}
.btn-accent:hover:not(:disabled) {
  background-color: color-mix(in srgb, var(--accent, #67000F) 85%, black);
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(103, 0, 15, 0.2);
}

/* Barra de Búsqueda */
.search-bar-wrapper .input-group-text {
  border-color: #cbd5e1;
  border-radius: 8px 0 0 8px;
}
.search-input {
  border-color: #cbd5e1;
  border-radius: 0 8px 8px 0;
  box-shadow: none !important;
}
.search-input:focus {
  border-color: #cbd5e1;
}

/* Tabla Personalizada */
.custom-table th {
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
  color: #64748b;
  border-bottom: 2px solid #e2e8f0;
  padding-top: 15px;
  padding-bottom: 15px;
}
.custom-table td {
  padding-top: 15px;
  padding-bottom: 15px;
  vertical-align: middle;
}

/* Formulario de Creación */
.form-control:focus, .form-select:focus {
  border-color: var(--accent, #67000F);
  box-shadow: 0 0 0 0.25rem color-mix(in srgb, var(--accent, #67000F) 25%, transparent);
}
textarea {
  resize: vertical;
}

/* Modal de Detalles del Informe (Premium Glassmorphism) */
.modal-detalle-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(8px);
  z-index: 1050;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.modal-detalle-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  width: 100%;
  max-width: 650px;
  max-height: 90vh;
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: modalScaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes modalScaleIn {
  from { transform: scale(0.95); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
.modal-detalle-header {
  padding: 20px 24px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.btn-cerrar-modal {
  background: none;
  border: none;
  color: #64748b;
  font-size: 1.1rem;
  cursor: pointer;
  padding: 4px;
  line-height: 1;
  transition: color 0.15s;
}
.btn-cerrar-modal:hover {
  color: #0f172a;
}
.modal-detalle-body {
  padding: 24px;
  overflow-y: auto;
}
.detalle-texto-caja {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 12px 16px;
  font-size: 0.95rem;
  color: #334155;
  line-height: 1.5;
  min-height: 50px;
}
.detalle-texto-caja.pre-wrap {
  white-space: pre-wrap;
}
.modal-detalle-footer {
  padding: 16px 24px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
}
</style>
