<template>
  <div class="row justify-content-center mt-3">
    <div class="col-md-10">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <h4 class="mb-0">{{ esEdicion ? 'MODIFICAR INSCRIPCIÓN' : 'NUEVA INSCRIPCIÓN' }}</h4>
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
                <label class="form-label fw-bold">FECHA:</label>
                <input v-model="f.fecha" type="date" required class="form-control" />
              </div>
            </div>
            <hr class="my-3">
            <button type="submit" :disabled="guardando" class="btn btn-success w-100 fw-bold py-2">
              <span v-if="guardando" class="spinner-border spinner-border-sm me-2"></span>
              {{ esEdicion ? 'ACTUALIZAR INSCRIPCIÓN' : 'GUARDAR INSCRIPCIÓN' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({ inscripcionEdit: Object });
const emit = defineEmits(['saved', 'cerrar']);

const esEdicion = computed(function() { return !!props.inscripcionEdit; });
const guardando = ref(false);
const cargandoDeps = ref(true);
const alumnos = ref([]);
const materias = ref([]);

const hoy = new Date().toISOString().split('T')[0];

const f = ref({
  idInscripcion: props.inscripcionEdit ? props.inscripcionEdit.idInscripcion : null,
  idAlumno:      props.inscripcionEdit ? props.inscripcionEdit.idAlumno  : '',
  idMateria:     props.inscripcionEdit ? props.inscripcionEdit.idMateria  : '',
  fecha:         props.inscripcionEdit ? props.inscripcionEdit.fecha      : hoy,
});

onMounted(async function() {
  try {
    const ra = await fetch('/api/alumnos', { headers: { Accept: 'application/json' } });
    const rm = await fetch('/api/materias', { headers: { Accept: 'application/json' } });
    if (ra.ok) alumnos.value = await ra.json();
    if (rm.ok) materias.value = await rm.json();
  } catch (e) { console.error(e); }
  cargandoDeps.value = false;
});

async function guardar() {
  guardando.value = true;
  try {
    const method = esEdicion.value ? 'PUT' : 'POST';
    const url    = esEdicion.value ? '/api/inscripciones/' + f.value.idInscripcion : '/api/inscripciones';
    const r = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(f.value)
    });
    const data = await r.json();
    if (!r.ok) { window.alertify.error('Error: ' + (data.message || 'Desconocido')); return; }
    window.alertify.success('Inscripción guardada correctamente');
    emit('saved');
  } catch (e) {
    window.alertify.error('Error de conexión');
  } finally {
    guardando.value = false;
  }
}
</script>
