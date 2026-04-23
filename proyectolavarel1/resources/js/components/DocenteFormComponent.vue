<template>
  <div class="row justify-content-center mt-3">
    <div class="col-md-10">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <h4 class="mb-0">{{ esEdicion ? 'MODIFICAR DOCENTE' : 'REGISTRO DE DOCENTES' }}</h4>
          <button type="button" @click="$emit('cerrar')" class="btn btn-sm btn-outline-light">✕ Cerrar</button>
        </div>
        <div class="card-body">
          <form @submit.prevent="guardar">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label fw-bold">CÓDIGO:</label>
                <input v-model="f.codigo" type="text" required class="form-control" placeholder="DO-001" />
              </div>
              <div class="col-md-8">
                <label class="form-label fw-bold">NOMBRE COMPLETO:</label>
                <input v-model="f.nombre" type="text" required class="form-control" placeholder="Nombre docente" />
              </div>
              <div class="col-md-12">
                <label class="form-label fw-bold">DIRECCIÓN:</label>
                <input v-model="f.direccion" type="text" required class="form-control" placeholder="Dirección domiciliar" />
              </div>
              <div class="col-md-4">
                <label class="form-label fw-bold">TELÉFONO:</label>
                <input v-model="f.telefono" type="text" required class="form-control" placeholder="0000-0000" />
              </div>
              <div class="col-md-4">
                <label class="form-label fw-bold">EMAIL:</label>
                <input v-model="f.email" type="email" required class="form-control" placeholder="correo@ejemplo.com" />
              </div>
              <div class="col-md-4">
                <label class="form-label fw-bold">ESCALAFÓN:</label>
                <select v-model="f.escalafon" required class="form-select">
                  <option value="" disabled>Seleccione...</option>
                  <option>Profesorado</option>
                  <option>Licenciatura</option>
                  <option>Ingeniería</option>
                  <option>Maestría</option>
                  <option>Doctorado</option>
                  <option>Otra</option>
                </select>
              </div>
            </div>
            <hr class="my-3">
            <button type="submit" :disabled="guardando" class="btn btn-success w-100 fw-bold py-2">
              <span v-if="guardando" class="spinner-border spinner-border-sm me-2"></span>
              {{ esEdicion ? 'ACTUALIZAR DOCENTE' : 'GUARDAR DOCENTE' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({ docenteEdit: Object });
const emit = defineEmits(['saved', 'cerrar']);

const esEdicion = computed(() => !!props.docenteEdit);
const guardando = ref(false);

const f = ref({
  Id_Docentes: props.docenteEdit ? (props.docenteEdit.Id_Docentes || props.docenteEdit.id) : null,
  codigo:      props.docenteEdit ? props.docenteEdit.codigo    : '',
  nombre:      props.docenteEdit ? props.docenteEdit.nombre    : '',
  direccion:   props.docenteEdit ? props.docenteEdit.direccion : '',
  telefono:    props.docenteEdit ? props.docenteEdit.telefono  : '',
  email:       props.docenteEdit ? props.docenteEdit.email     : '',
  escalafon:   props.docenteEdit ? props.docenteEdit.escalafon : '',
});

async function guardar() {
  guardando.value = true;
  try {
    if (!f.value.Id_Docentes) f.value.Id_Docentes = crypto.randomUUID();
    const method = esEdicion.value ? 'PUT' : 'POST';
    const url    = esEdicion.value ? '/api/docentes/' + f.value.Id_Docentes : '/api/docentes';
    const r = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(f.value)
    });
    const data = await r.json();
    if (!r.ok) { window.alertify.error('Error: ' + (data.message || 'Desconocido')); return; }
    window.alertify.success('Docente guardado correctamente');
    emit('saved');
  } catch (e) {
    window.alertify.error('Error de conexión');
  } finally {
    guardando.value = false;
  }
}
</script>
