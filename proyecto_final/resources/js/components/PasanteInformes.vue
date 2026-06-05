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
                <td class="fw-semibold text-dark">
                  <div>{{ inf.nombre || ('Reporte de ' + (inf.horas || 0) + ' horas') }}</div>
                  <small v-if="inf.fecha_inicio && inf.fecha_fin" class="text-muted d-block mt-1">
                    <i class="bi bi-calendar-range me-1"></i> Período: {{ formatearFechaSencilla(inf.fecha_inicio) }} al {{ formatearFechaSencilla(inf.fecha_fin) }}
                  </small>
                </td>
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
                  <button v-if="inf.estado === 'en_espera' || inf.estado === 'revision' || inf.estado === 'correccion'" class="btn btn-sm btn-outline-warning me-1" title="Editar/Corregir Informe" @click="editarInforme(inf)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button v-if="inf.estado === 'en_espera' || inf.estado === 'revision' || inf.estado === 'correccion'" class="btn btn-sm btn-outline-danger" title="Eliminar Informe" @click="eliminarInforme(inf.id)">
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
          <button type="button" class="btn-cerrar-modal" @click="cerrarFormulario" title="Cerrar"><i class="bi bi-x-lg"></i></button>
        </div>

        <form @submit.prevent="guardarInforme">
          <div v-if="editandoId && nuevoInforme.observaciones" class="alert alert-warning border-warning p-3 mb-4 rounded-3 d-flex align-items-start gap-3">
            <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i>
            <div>
              <h6 class="fw-bold text-warning-emphasis mb-1">El supervisor solicitó correcciones para este informe</h6>
              <p class="mb-0 small text-dark-emphasis">
                <strong>Detalle de las observaciones:</strong> {{ obtenerDetalleObservacion(nuevoInforme.observaciones) }}
              </p>
              <div v-if="obtenerSeccionesObservacion(nuevoInforme.observaciones).length > 0" class="mt-2">
                <span class="small fw-bold text-warning-emphasis d-block mb-1">Secciones a corregir:</span>
                <div class="d-flex gap-2 flex-wrap">
                  <span v-for="sec in obtenerSeccionesObservacion(nuevoInforme.observaciones)" :key="sec" class="badge bg-danger text-white px-2 py-1">
                    {{ traduccirSeccion(sec) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-4">
              <label class="form-label fw-bold" :class="{ 'text-danger': tieneCorreccionEn('nombre') }">
                Nombre del Informe
                <span v-if="tieneCorreccionEn('nombre')" class="text-danger small fw-semibold ms-1">(Requiere Corrección)</span>
              </label>
              <input type="text" class="form-control form-control-lg" :class="{ 'is-invalid border-danger': tieneCorreccionEn('nombre') }" v-model="nuevoInforme.nombre" placeholder="Ej: Informe Mensual - Mayo 2026" required>
              <div v-if="tieneCorreccionEn('nombre')" class="invalid-feedback text-danger">El supervisor solicitó corregir esta sección.</div>
            </div>
            <div class="col-md-3 mb-4">
              <label class="form-label fw-bold">Tipo de Informe</label>
              <select class="form-select form-select-lg" v-model="nuevoInforme.tipo" required>
                <option value="parcial">Mensual / Avance</option>
                <option value="final">Informe Final</option>
              </select>
            </div>
            <div class="col-md-3 mb-4">
              <label class="form-label fw-bold" :class="{ 'text-danger': tieneCorreccionEn('horas') }">
                Horas a Reportar
                <span v-if="tieneCorreccionEn('horas')" class="text-danger small fw-semibold ms-1">(Requiere Corrección)</span>
              </label>
              <input
                type="number"
                min="0.01"
                step="0.01"
                max="600"
                class="form-control form-control-lg"
                :class="{ 'is-invalid': horasError || tieneCorreccionEn('horas') }"
                v-model.number="nuevoInforme.horas"
                placeholder="Ej: 80"
                required
                @input="horasError = false"
              >
              <div class="invalid-feedback" v-if="horasError">Debes ingresar las horas (mín. 0.01).</div>
              <div class="invalid-feedback text-danger" v-if="tieneCorreccionEn('horas')">El supervisor solicitó corregir esta sección.</div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-4">
              <label class="form-label fw-bold" :class="{ 'text-danger': tieneCorreccionEn('periodo') }">
                Desde (Fecha de Inicio)
                <span v-if="tieneCorreccionEn('periodo')" class="text-danger small fw-semibold ms-1">(Requiere Corrección)</span>
              </label>
              <input type="date" class="form-control form-control-lg" :class="{ 'is-invalid border-danger': tieneCorreccionEn('periodo') }" v-model="nuevoInforme.fecha_inicio" required>
              <div v-if="tieneCorreccionEn('periodo')" class="invalid-feedback text-danger">El supervisor solicitó corregir las fechas del período.</div>
            </div>
            <div class="col-md-6 mb-4">
              <label class="form-label fw-bold" :class="{ 'text-danger': tieneCorreccionEn('periodo') }">
                Hasta (Fecha de Fin)
                <span v-if="tieneCorreccionEn('periodo')" class="text-danger small fw-semibold ms-1">(Requiere Corrección)</span>
              </label>
              <input type="date" class="form-control form-control-lg" :class="{ 'is-invalid border-danger': tieneCorreccionEn('periodo') }" v-model="nuevoInforme.fecha_fin" :min="nuevoInforme.fecha_inicio" required>
              <div v-if="tieneCorreccionEn('periodo')" class="invalid-feedback text-danger">El supervisor solicitó corregir las fechas del período.</div>
            </div>
          </div>

          <div class="mb-4 p-3 rounded" :class="{ 'border border-danger bg-danger-light-subtle': tieneCorreccionEn('bitacora') }">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <label class="form-label fw-bold m-0" :class="{ 'text-danger': tieneCorreccionEn('bitacora') }">
                <i class="bi bi-list-task me-2" :class="tieneCorreccionEn('bitacora') ? 'text-danger' : 'text-primary'"></i>
                Bitácora de Actividades (Detallada por Día)
                <span v-if="tieneCorreccionEn('bitacora')" class="badge bg-danger ms-2">Requiere Corrección</span>
              </label>
              <button type="button" class="btn btn-sm btn-outline-primary" @click="agregarFilaBitacora">
                <i class="bi bi-plus-circle me-1"></i> Agregar Actividad / Día
              </button>
            </div>
            <div v-if="tieneCorreccionEn('bitacora')" class="text-danger small fw-semibold mb-2">
              <i class="bi bi-exclamation-circle-fill"></i> El supervisor solicitó revisar las actividades, objetivos o fechas registradas en la bitácora.
            </div>
            
            <div v-if="!nuevoInforme.bitacora || nuevoInforme.bitacora.length === 0" class="alert alert-info py-3 text-center">
              <i class="bi bi-info-circle fs-4 d-block mb-2"></i>
              No has agregado ninguna actividad a la bitácora. Haz clic en "Agregar Actividad / Día" para registrar tus actividades cronológicamente.
            </div>
            
            <div v-else class="table-responsive">
              <table class="table table-bordered align-middle bitacora-edit-table">
                <thead class="table-light">
                  <tr>
                    <th style="width: 18%;">Fecha</th>
                    <th style="width: 25%;">Objetivo</th>
                    <th style="width: 30%;">Actividades Realizadas</th>
                    <th style="width: 22%;">Logros y Conclusiones</th>
                    <th style="width: 5%;" class="text-center">Eliminar</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(fila, index) in nuevoInforme.bitacora" :key="index">
                    <td>
                      <input type="date" class="form-control form-control-sm" v-model="fila.fecha" required>
                    </td>
                    <td>
                      <textarea class="form-control form-control-sm" rows="2" v-model="fila.objetivo" placeholder="Objetivo de la actividad..." required></textarea>
                    </td>
                    <td>
                      <textarea class="form-control form-control-sm" rows="2" v-model="fila.actividades" placeholder="Actividades realizadas..." required></textarea>
                    </td>
                    <td>
                      <textarea class="form-control form-control-sm" rows="2" v-model="fila.logros" placeholder="Logros y conclusiones..." required></textarea>
                    </td>
                    <td class="text-center">
                      <button type="button" class="btn btn-sm btn-outline-danger" @click="eliminarFilaBitacora(index)" title="Eliminar fila">
                        <i class="bi bi-trash"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Apartado para subir imágenes (Máximo 4) -->
          <div class="mb-4 border rounded p-3" :class="tieneCorreccionEn('imagenes') ? 'bg-danger-subtle border-danger text-danger-emphasis' : 'bg-light'">
            <label class="form-label fw-bold d-flex align-items-center justify-content-between" :class="{ 'text-danger': tieneCorreccionEn('imagenes') }">
              <span>
                <i class="bi bi-images me-2" :class="tieneCorreccionEn('imagenes') ? 'text-danger' : 'text-primary'"></i>
                Imágenes de Evidencia / Anexos (Máximo 4)
                <span v-if="tieneCorreccionEn('imagenes')" class="badge bg-danger ms-2">Requiere Corrección</span>
              </span>
              <span class="small text-muted">{{ totalImagenesCount }}/4 seleccionadas</span>
            </label>
            <div v-if="tieneCorreccionEn('imagenes')" class="text-danger small fw-semibold mb-2">
              <i class="bi bi-exclamation-circle-fill"></i> El supervisor solicitó corregir, agregar o cambiar las imágenes de evidencia.
            </div>
            <p class="small mb-3" :class="tieneCorreccionEn('imagenes') ? 'text-danger-emphasis' : 'text-muted'">Sube capturas de pantalla o fotografías que respalden las actividades realizadas durante este periodo.</p>
            
            <div class="d-flex align-items-center gap-3 flex-wrap mb-3">
              <input 
                type="file" 
                ref="fileInputImagenes" 
                class="d-none" 
                multiple 
                accept="image/*" 
                @change="seleccionarImagenes" 
                :disabled="totalImagenesCount >= 4"
              >
              <button 
                type="button" 
                class="btn btn-outline-primary d-flex align-items-center"
                @click="$refs.fileInputImagenes.click()"
                :disabled="totalImagenesCount >= 4"
              >
                <i class="bi bi-cloud-upload me-2"></i> Seleccionar Imágenes
              </button>
              <span class="small text-muted" v-if="totalImagenesCount >= 4">Límite de 4 imágenes alcanzado.</span>
            </div>

            <!-- Previsualizaciones -->
            <div class="row row-cols-2 row-cols-md-4 g-3" v-if="totalImagenesCount > 0">
              <!-- Existentes -->
              <div class="col" v-for="(imgUrl, index) in imagenesExistentes" :key="'existente-' + index">
                <div class="position-relative border rounded p-1 bg-white img-preview-box">
                  <img :src="imgUrl" class="img-thumbnail border-0 w-100 img-preview" alt="Evidencia existente">
                  <button 
                    type="button" 
                    class="btn btn-danger btn-xs btn-delete-preview"
                    @click="eliminarImagenExistente(index)"
                    title="Eliminar imagen"
                  >
                    <i class="bi bi-trash-fill"></i>
                  </button>
                  <span class="badge bg-secondary position-absolute bottom-0 start-0 m-1">Guardada</span>
                </div>
              </div>

              <!-- Nuevas -->
              <div class="col" v-for="(preview, index) in imagenesNuevasPreviews" :key="'nueva-' + index">
                <div class="position-relative border rounded p-1 bg-white img-preview-box">
                  <img :src="preview" class="img-thumbnail border-0 w-100 img-preview" alt="Nueva evidencia">
                  <button 
                    type="button" 
                    class="btn btn-danger btn-xs btn-delete-preview"
                    @click="eliminarImagenNueva(index)"
                    title="Eliminar imagen"
                  >
                    <i class="bi bi-trash-fill"></i>
                  </button>
                  <span class="badge bg-primary position-absolute bottom-0 start-0 m-1">Nueva</span>
                </div>
              </div>
            </div>
          </div>


          <div class="border-top pt-4 mt-2 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-cancelar px-5 py-2 fs-5 d-flex align-items-center" @click="cerrarFormulario">Cancelar</button>
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

          <div class="mb-3" v-if="informeDetalle?.fecha_inicio && informeDetalle?.fecha_fin">
            <span class="small text-muted d-block fw-bold mb-1"><i class="bi bi-calendar-range me-1"></i> Período del Informe:</span>
            <div class="detalle-texto-caja">
              Desde el <strong>{{ formatearFechaSencilla(informeDetalle.fecha_inicio) }}</strong> hasta el <strong>{{ formatearFechaSencilla(informeDetalle.fecha_fin) }}</strong>
            </div>
          </div>
          
          <div v-if="informeDetalle?.bitacora && informeDetalle.bitacora.length > 0" class="mb-4">
            <span class="small text-muted d-block fw-bold mb-2"><i class="bi bi-list-columns-reverse me-1"></i> Bitácora de Actividades:</span>
            <div class="table-responsive">
              <table class="table table-bordered table-sm custom-detail-table">
                <thead class="table-light">
                  <tr>
                    <th style="width: 15%;">Fecha</th>
                    <th style="width: 25%;">Objetivo</th>
                    <th style="width: 35%;">Actividades</th>
                    <th style="width: 25%;">Logros y conclusiones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(fila, idx) in informeDetalle.bitacora" :key="idx">
                    <td class="text-nowrap">{{ formatearFechaSencilla(fila.fecha) }}</td>
                    <td>{{ fila.objetivo }}</td>
                    <td class="pre-wrap">{{ fila.actividades }}</td>
                    <td>{{ fila.logros }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div v-else>
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
          </div>

          <!-- Galería de imágenes de evidencia -->
          <div class="mb-4" v-if="informeDetalle?.imagenes && informeDetalle.imagenes.length > 0">
            <span class="small text-muted d-block fw-bold mb-2"><i class="bi bi-images me-1"></i> Imágenes de Evidencia / Anexos:</span>
            <div class="row row-cols-2 row-cols-md-4 g-2">
              <div class="col" v-for="(imgUrl, idx) in informeDetalle.imagenes" :key="idx">
                <a :href="imgUrl" target="_blank" title="Ver imagen ampliada">
                  <div class="border rounded p-1 bg-white img-preview-box" style="height: 100px;">
                    <img :src="imgUrl" class="img-preview" alt="Evidencia">
                  </div>
                </a>
              </div>
            </div>
          </div>

          <div class="mb-3" v-if="informeDetalle?.observaciones">
            <span class="small text-danger d-block fw-bold mb-1">Observaciones del Supervisor:</span>
            <div class="detalle-texto-caja border-danger-subtle bg-danger-subtle text-danger">
              <p class="mb-0 fw-semibold">{{ obtenerDetalleObservacion(informeDetalle.observaciones) }}</p>
              <div v-if="obtenerSeccionesObservacion(informeDetalle.observaciones).length > 0" class="mt-2">
                <span class="small d-block text-danger-emphasis mb-1 fw-bold">Secciones indicadas para corrección:</span>
                <div class="d-flex gap-2 flex-wrap">
                  <span v-for="sec in obtenerSeccionesObservacion(informeDetalle.observaciones)" :key="sec" class="badge bg-danger text-white">
                    {{ traduccirSeccion(sec) }}
                  </span>
                </div>
              </div>
            </div>
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
  fecha_inicio: '',
  fecha_fin: '',
  bitacora: []
});

// Reactividad para imágenes
const fileInputImagenes = ref(null);
const imagenesExistentes = ref([]);
const imagenesNuevas = ref([]);
const imagenesNuevasPreviews = ref([]);

const totalImagenesCount = computed(() => {
  return (imagenesExistentes.value?.length || 0) + (imagenesNuevas.value?.length || 0);
});

const seleccionarImagenes = (event) => {
  const files = Array.from(event.target.files);
  const slotsDisponibles = 4 - totalImagenesCount.value;

  if (slotsDisponibles <= 0) {
    alertify.error('Límite de 4 imágenes alcanzado.');
    return;
  }

  const filesParaSubir = files.slice(0, slotsDisponibles);
  if (files.length > slotsDisponibles) {
    alertify.warning(`Solo se pueden agregar ${slotsDisponibles} imágenes más.`);
  }

  filesParaSubir.forEach((file) => {
    if (!file.type.startsWith('image/')) {
      alertify.error('El archivo debe ser una imagen.');
      return;
    }
    if (file.size > 2 * 1024 * 1024) {
      alertify.error('La imagen no debe superar los 2MB.');
      return;
    }

    imagenesNuevas.value.push(file);
    const reader = new FileReader();
    reader.onload = (e) => {
      imagenesNuevasPreviews.value.push(e.target.result);
    };
    reader.readAsDataURL(file);
  });

  if (fileInputImagenes.value) {
    fileInputImagenes.value.value = '';
  }
};

const eliminarImagenNueva = (index) => {
  imagenesNuevas.value.splice(index, 1);
  imagenesNuevasPreviews.value.splice(index, 1);
};

const eliminarImagenExistente = (index) => {
  imagenesExistentes.value.splice(index, 1);
};

const agregarFilaBitacora = () => {
  if (!nuevoInforme.value.bitacora) {
    nuevoInforme.value.bitacora = [];
  }
  nuevoInforme.value.bitacora.push({
    fecha: '',
    objetivo: '',
    actividades: '',
    logros: ''
  });
};

const eliminarFilaBitacora = (index) => {
  nuevoInforme.value.bitacora.splice(index, 1);
};

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

const resetearFormulario = () => {
  vistaCreacion.value = false;
  editandoId.value = null;
  horasError.value = false;
  imagenesExistentes.value = [];
  imagenesNuevas.value = [];
  imagenesNuevasPreviews.value = [];
  nuevoInforme.value = {
    nombre: '',
    tipo: 'parcial',
    horas: null,
    fecha_inicio: '',
    fecha_fin: '',
    bitacora: []
  };
};

const cerrarFormulario = () => {
  const tieneCambios = nuevoInforme.value.nombre || 
                       nuevoInforme.value.horas || 
                       nuevoInforme.value.fecha_inicio || 
                       nuevoInforme.value.fecha_fin || 
                       (nuevoInforme.value.bitacora && nuevoInforme.value.bitacora.length > 0);
  if (tieneCambios) {
    alertify.confirm('Advertencia', '¿Estás seguro de cerrar el formulario? Se perderán todos los datos no guardados.', () => {
      resetearFormulario();
    }, () => {});
  } else {
    resetearFormulario();
  }
};

const editarInforme = (inf) => {
  editandoId.value = inf.id;
  imagenesExistentes.value = Array.isArray(inf.imagenes) ? [...inf.imagenes] : [];
  imagenesNuevas.value = [];
  imagenesNuevasPreviews.value = [];
  nuevoInforme.value = {
    nombre: inf.nombre || '',
    tipo: inf.tipo,
    horas: inf.horas,
    fecha_inicio: inf.fecha_inicio ? inf.fecha_inicio.substring(0, 10) : '',
    fecha_fin: inf.fecha_fin ? inf.fecha_fin.substring(0, 10) : '',
    bitacora: Array.isArray(inf.bitacora) ? JSON.parse(JSON.stringify(inf.bitacora)) : [],
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

  // Validación de fechas
  const inicio = nuevoInforme.value.fecha_inicio;
  const fin = nuevoInforme.value.fecha_fin;
  if (!inicio || !fin) {
    alertify.error('Debes seleccionar las fechas de inicio y fin del período del informe.');
    return;
  }
  if (new Date(fin) < new Date(inicio)) {
    alertify.error('La fecha de fin (Hasta) no puede ser anterior a la fecha de inicio (Desde).');
    return;
  }

  // Validación de la bitácora
  if (!nuevoInforme.value.bitacora || nuevoInforme.value.bitacora.length === 0) {
    alertify.error('Debes agregar al menos una actividad en la bitácora.');
    return;
  }

  // Validar campos de cada fila
  for (let i = 0; i < nuevoInforme.value.bitacora.length; i++) {
    const fila = nuevoInforme.value.bitacora[i];
    if (!fila.fecha || !fila.objetivo?.trim() || !fila.actividades?.trim() || !fila.logros?.trim()) {
      alertify.error(`Por favor completa todos los campos de la fila ${i + 1} de la bitácora.`);
      return;
    }
  }

  guardando.value = true;
  try {
    const formData = new FormData();
    formData.append('tipo', nuevoInforme.value.tipo);
    formData.append('horas', horas);
    if (nuevoInforme.value.nombre) {
      formData.append('nombre', nuevoInforme.value.nombre);
    }
    formData.append('fecha_inicio', inicio);
    formData.append('fecha_fin', fin);
    formData.append('bitacora', JSON.stringify(nuevoInforme.value.bitacora));

    if (nuevoInforme.value.objetivos) {
      formData.append('objetivos', nuevoInforme.value.objetivos);
    }
    if (nuevoInforme.value.actividades) {
      formData.append('actividades', nuevoInforme.value.actividades);
    }
    if (nuevoInforme.value.conclusiones) {
      formData.append('conclusiones', nuevoInforme.value.conclusiones);
    }

    // Imágenes existentes
    formData.append('imagenes_existentes', JSON.stringify(imagenesExistentes.value));

    // Nuevas imágenes
    imagenesNuevas.value.forEach((file) => {
      formData.append('imagenes[]', file);
    });
    
    if (editandoId.value) {
      // Método Spoofing para enviar multipart/form-data en PUT en Laravel
      formData.append('_method', 'PUT');
      await axios.post(`/api/pasante/informes/${editandoId.value}`, formData, {
        headers: { 
          'X-User-Id': props.usuario.id,
          'Content-Type': 'multipart/form-data'
        }
      });
      alertify.success('Informe actualizado correctamente.');
    } else {
      await axios.post('/api/pasante/informes', formData, {
        headers: { 
          'X-User-Id': props.usuario.id,
          'Content-Type': 'multipart/form-data'
        }
      });
      alertify.success('Informe subido y en revisión.');
    }
    
    resetearFormulario();
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

const formatearFechaSencilla = (fechaStr) => {
  if (!fechaStr) return '';
  const partes = fechaStr.split('-');
  if (partes.length === 3) {
    return `${partes[2]}/${partes[1]}/${partes[0]}`;
  }
  return fechaStr;
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

const obtenerDetalleObservacion = (obs) => {
  if (!obs) return '';
  if (obs.trim().startsWith('{')) {
    try {
      const parsed = JSON.parse(obs);
      return parsed.detalle || obs;
    } catch (e) {
      return obs;
    }
  }
  return obs;
};

const obtenerSeccionesObservacion = (obs) => {
  if (!obs) return [];
  if (obs.trim().startsWith('{')) {
    try {
      const parsed = JSON.parse(obs);
      return parsed.secciones || [];
    } catch (e) {
      return [];
    }
  }
  return [];
};

const tieneCorreccionEn = (seccion) => {
  if (!editandoId.value || !nuevoInforme.value.observaciones) return false;
  const obs = nuevoInforme.value.observaciones;
  if (obs.trim().startsWith('{')) {
    try {
      const parsed = JSON.parse(obs);
      return (parsed.secciones || []).includes(seccion);
    } catch (e) {
      return false;
    }
  }
  return false;
};

const traduccirSeccion = (seccion) => {
  const nombres = {
    nombre: 'Nombre del Informe',
    horas: 'Horas a Reportar',
    periodo: 'Período (Fechas)',
    bitacora: 'Bitácora de Actividades',
    imagenes: 'Imágenes de Evidencia'
  };
  return nombres[seccion] ?? seccion;
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
  background-color: #001374;
  color: white;
  font-weight: 600;
  border: none;
  transition: all 0.2s;
  border-radius: 8px;
}
.btn-accent:hover:not(:disabled) {
  background-color: #010c67;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 19, 116, 0.2);
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
  border-color: #001374;
  box-shadow: 0 0 0 0.25rem rgba(0, 19, 116, 0.15);
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

/* Botón Cancelar personalizado */
.btn-cancelar {
  background-color: #ffffff;
  color: #67000F;
  border: 2px solid #67000F;
  font-weight: 600;
  transition: all 0.2s;
  border-radius: 8px;
}
.btn-cancelar:hover {
  background-color: #67000F;
  color: #ffffff;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(103, 0, 15, 0.2);
}

.pre-wrap {
  white-space: pre-wrap;
}

.bitacora-edit-table textarea {
  resize: vertical;
}

.custom-detail-table {
  font-size: 0.875rem;
}

.custom-detail-table th {
  background-color: #f8fafc;
  font-weight: 600;
}

/* Previsualización de Imágenes */
.img-preview-box {
  height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  position: relative;
}
.img-preview {
  object-fit: cover;
  height: 100%;
  width: 100%;
}
.btn-delete-preview {
  position: absolute;
  top: 5px;
  right: 5px;
  padding: 2px 6px;
  font-size: 0.75rem;
  border-radius: 4px;
  opacity: 0.85;
  transition: opacity 0.15s;
}
.btn-delete-preview:hover {
  opacity: 1;
}
.btn-xs {
  padding: 1px 5px;
  font-size: 0.75rem;
}
</style>
