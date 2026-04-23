<template>
  <div class="row justify-content-center mt-3">
    <div class="col-md-10">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <h4 class="mb-0">{{ esEdicion ? 'MODIFICAR MATERIA' : 'REGISTRO DE MATERIAS' }}</h4>
          <button type="button" @click="$emit('cerrar')" class="btn btn-sm btn-outline-light">✕ Cerrar</button>
        </div>
        <div class="card-body">
          <form @submit.prevent="guardar">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label fw-bold">CÓDIGO:</label>
                <input v-model="f.codigo" type="text" required class="form-control" placeholder="MT-001" />
              </div>
              <div class="col-md-8">
                <label class="form-label fw-bold">NOMBRE MATERIA:</label>
                <input v-model="f.nombre" type="text" required class="form-control" placeholder="Nombre de la materia" />
              </div>
              <div class="col-md-4">
                <label class="form-label fw-bold">UNIDADES VALORATIVAS (UV):</label>
                <input v-model="f.uv" type="text" required class="form-control" placeholder="Ej: 4" />
              </div>
            </div>
            <hr class="my-3">
            <button type="submit" :disabled="guardando" class="btn btn-success w-100 fw-bold py-2">
              <span v-if="guardando" class="spinner-border spinner-border-sm me-2"></span>
              {{ esEdicion ? 'ACTUALIZAR MATERIA' : 'GUARDAR MATERIA' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({ materiaEdit: Object });
const emit = defineEmits(['saved', 'cerrar']);

const esEdicion = computed(() => !!props.materiaEdit);
const guardando = ref(false);

const f = ref({
  idMateria: props.materiaEdit ? (props.materiaEdit.idMateria || props.materiaEdit.id) : null,
  codigo:    props.materiaEdit ? props.materiaEdit.codigo : '',
  nombre:    props.materiaEdit ? props.materiaEdit.nombre : '',
  uv:        props.materiaEdit ? props.materiaEdit.uv     : '',
});

async function guardar() {
  guardando.value = true;
  try {
    if (!f.value.idMateria) f.value.idMateria = crypto.randomUUID();
    const method = esEdicion.value ? 'PUT' : 'POST';
    const url    = esEdicion.value ? '/api/materias/' + f.value.idMateria : '/api/materias';
    const r = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(f.value)
    });
    const data = await r.json();
    if (!r.ok) { window.alertify.error('Error: ' + (data.message || 'Desconocido')); return; }
    window.alertify.success('Materia guardada correctamente');
    emit('saved');
  } catch (e) {
    window.alertify.error('Error de conexión');
  } finally {
    guardando.value = false;
  }
}
</script>
