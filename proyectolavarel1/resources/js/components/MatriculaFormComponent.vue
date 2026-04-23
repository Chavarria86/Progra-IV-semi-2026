<template>
  <div class="row justify-content-center mt-3">
    <div class="col-md-10">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <h4 class="mb-0">{{ esEdicion ? 'MODIFICAR MATRÍCULA' : 'NUEVA MATRÍCULA' }}</h4>
          <button type="button" @click="$emit('cerrar')" class="btn btn-sm btn-outline-light">✕ Cerrar</button>
        </div>
        <div class="card-body">
          <div v-if="cargandoDeps" class="text-center py-3">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">Cargando datos...</p>
          </div>
          <form v-else @submit.prevent="guardar">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">ALUMNO:</label>
                <select v-model="f.idAlumno" required class="form-select">
                  <option value="" disabled>-- Seleccione alumno --</option>
                  <option v-for="a in alumnos" :key="a.id" :value="a.idAlumno || a.id">{{ a.nombre }}</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">MATERIA:</label>
                <select v-model="f.idMateria" required class="form-select">
                  <option value="" disabled>-- Seleccione materia --</option>
                  <option v-for="m in materias" :key="m.id" :value="m.idMateria || m.id">{{ m.nombre }}</option>
                </select>
              </div>
              <div class="col-md-12">
                <label class="form-label fw-bold">DOCENTE:</label>
                <select v-model="f.idDocente" required class="form-select">
                  <option value="" disabled>-- Seleccione docente --</option>
                  <option v-for="d in docentes" :key="d.id" :value="d.Id_Docentes || d.id">{{ d.nombre }}</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-bold">ESTADO:</label>
                <select v-model="f.estado" required class="form-select">
                  <option value="" disabled>-- Estado --</option>
                  <option value="Activo">Activo</option>
                  <option value="Inactivo">Inactivo</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-bold">PERIODO:</label>
                <input v-model="f.periodo" type="text" required class="form-control" placeholder="Ciclo 01" />
              </div>
              <div class="col-md-4">
                <label class="form-label fw-bold">GESTIÓN (AÑO):</label>
                <input v-model.number="f.gestion" type="number" required class="form-control" placeholder="2026" />
              </div>
              <div class="col-md-12">
                <label class="form-label fw-bold">FECHA:</label>
                <input v-model="f.fecha" type="date" required class="form-control" />
              </div>
            </div>
            <hr class="my-3">
            <button type="submit" :disabled="guardando" class="btn btn-success w-100 fw-bold py-2">
              <span v-if="guardando" class="spinner-border spinner-border-sm me-2"></span>
              {{ esEdicion ? 'ACTUALIZAR MATRÍCULA' : 'GUARDAR MATRÍCULA' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({ matriculaEdit: Object });
const emit = defineEmits(['saved', 'cerrar']);

const esEdicion = computed(function() { return !!props.matriculaEdit; });
const guardando = ref(false);
const cargandoDeps = ref(true);
const alumnos = ref([]);
const materias = ref([]);
const docentes = ref([]);

const hoy = new Date().toISOString().split('T')[0];
const anio = new Date().getFullYear();

const f = ref({
  idMatricula: props.matriculaEdit ? (props.matriculaEdit.idMatricula || props.matriculaEdit.id) : '',
  idAlumno:    props.matriculaEdit ? props.matriculaEdit.idAlumno  : '',
  idMateria:   props.matriculaEdit ? props.matriculaEdit.idMateria  : '',
  idDocente:   props.matriculaEdit ? props.matriculaEdit.idDocente  : '',
  estado:      props.matriculaEdit ? props.matriculaEdit.estado     : 'Activo',
  periodo:     props.matriculaEdit ? props.matriculaEdit.periodo    : '',
  gestion:     props.matriculaEdit ? props.matriculaEdit.gestion    : anio,
  fecha:       props.matriculaEdit ? props.matriculaEdit.fecha      : hoy,
});

onMounted(async function() {
  try {
    const [ra, rm, rd] = await Promise.all([
      fetch('/api/alumnos', { headers: { Accept: 'application/json' } }),
      fetch('/api/materias', { headers: { Accept: 'application/json' } }),
      fetch('/api/docentes', { headers: { Accept: 'application/json' } })
    ]);
    if (ra.ok) alumnos.value = await ra.json();
    if (rm.ok) materias.value = await rm.json();
    if (rd.ok) docentes.value = await rd.json();
  } catch (e) { console.error('Error cargando dependencias:', e); }
  cargandoDeps.value = false;
});

async function guardar() {
  guardando.value = true;
  try {
    // Generar ID si es nueva
    if (!esEdicion.value && !f.value.idMatricula) {
      f.value.idMatricula = crypto.randomUUID();
    }

    const method = esEdicion.value ? 'PUT' : 'POST';
    const url    = esEdicion.value ? '/api/matriculas/' + f.value.idMatricula : '/api/matriculas';
    
    const r = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(f.value)
    });
    
    const data = await r.json();
    if (!r.ok) {
      let msg = data.message || 'Error al procesar la solicitud';
      if (data.errors) msg = Object.values(data.errors).flat().join(', ');
      window.alertify.error(msg);
      return;
    }
    
    window.alertify.success(esEdicion.value ? 'Matrícula actualizada' : 'Matrícula guardada');
    emit('saved');
  } catch (e) {
    console.error(e);
    window.alertify.error('Error de conexión con el servidor');
  } finally {
    guardando.value = false;
  }
}
</script>
