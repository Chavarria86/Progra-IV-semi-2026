<template>
  <div class="row justify-content-center mt-3">
    <div class="col-md-10">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <h4 class="mb-0">{{ esEdicion ? 'MODIFICAR ALUMNO' : 'REGISTRO DE ALUMNOS' }}</h4>
          <button type="button" @click="$emit('cerrar')" class="btn btn-sm btn-outline-light">✕ Cerrar</button>
        </div>
        <div class="card-body">
          <form @submit.prevent="guardar">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label fw-bold">CÓDIGO:</label>
                <input v-model="f.codigo" type="text" required class="form-control" placeholder="AL-001" />
              </div>
              <div class="col-md-8">
                <label class="form-label fw-bold">NOMBRE COMPLETO:</label>
                <input v-model="f.nombre" type="text" required class="form-control" placeholder="Nombre completo" />
              </div>
              <div class="col-md-12">
                <label class="form-label fw-bold">DIRECCIÓN:</label>
                <input v-model="f.direccion" type="text" required class="form-control" placeholder="Dirección domiciliar" />
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">EMAIL:</label>
                <input v-model="f.email" type="email" required class="form-control" placeholder="correo@ejemplo.com" />
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">TELÉFONO:</label>
                <input v-model="f.telefono" type="text" required class="form-control" placeholder="0000-0000" />
              </div>
            </div>
            <hr class="my-3">
            <button type="submit" :disabled="guardando" class="btn btn-success w-100 fw-bold py-2">
              <span v-if="guardando" class="spinner-border spinner-border-sm me-2"></span>
              {{ esEdicion ? 'ACTUALIZAR ALUMNO' : 'GUARDAR ALUMNO' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({ alumnoEdit: Object });
const emit = defineEmits(['saved', 'cerrar']);

const esEdicion = computed(() => !!props.alumnoEdit);
const guardando = ref(false);

// Inicializar directamente desde props - SIN watch para evitar TDZ en minificación
const f = ref({
  idAlumno: props.alumnoEdit ? (props.alumnoEdit.idAlumno || props.alumnoEdit.id) : null,
  codigo:   props.alumnoEdit ? props.alumnoEdit.codigo   : '',
  nombre:   props.alumnoEdit ? props.alumnoEdit.nombre   : '',
  direccion:props.alumnoEdit ? props.alumnoEdit.direccion: '',
  email:    props.alumnoEdit ? props.alumnoEdit.email    : '',
  telefono: props.alumnoEdit ? props.alumnoEdit.telefono : '',
});

async function guardar() {
  guardando.value = true;
  try {
    if (!f.value.idAlumno) f.value.idAlumno = crypto.randomUUID();
    const method = esEdicion.value ? 'PUT' : 'POST';
    const url    = esEdicion.value ? '/api/alumnos/' + f.value.idAlumno : '/api/alumnos';
    const r = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(f.value)
    });
    const data = await r.json();
    if (!r.ok) { window.alertify.error('Error: ' + (data.message || 'Desconocido')); return; }
    window.alertify.success('Alumno guardado correctamente');
    emit('saved');
  } catch (e) {
    window.alertify.error('Error de conexión');
  } finally {
    guardando.value = false;
  }
}
</script>
