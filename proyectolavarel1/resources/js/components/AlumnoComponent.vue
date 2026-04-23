<template>
  <div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0">LISTA DE ALUMNOS</h4>
      <button @click="$emit('nuevo')" class="btn btn-sm btn-outline-light">+ Nuevo Alumno</button>
    </div>
    <div class="card-body">
      <BusquedaComponent placeholder="Buscar alumno..." @search="onSearch" />
      <div class="table-responsive mt-2">
        <table class="table table-hover table-bordered align-middle">
          <thead class="table-dark">
            <tr>
              <th>CÓDIGO</th><th>NOMBRE</th><th>DIRECCIÓN</th><th>TELÉFONO</th><th>EMAIL</th><th class="text-center">ACCIONES</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="cargando"><td colspan="6" class="text-center py-3"><div class="spinner-border spinner-border-sm"></div> Cargando...</td></tr>
            <tr v-else-if="alumnos.length === 0"><td colspan="6" class="text-center text-muted py-4">No se encontraron registros.</td></tr>
            <tr v-else v-for="al in alumnos" :key="al.id">
              <td><strong>{{ al.codigo }}</strong></td>
              <td>{{ al.nombre }}</td>
              <td>{{ al.direccion }}</td>
              <td>{{ al.telefono }}</td>
              <td>{{ al.email }}</td>
              <td class="text-center">
                <button @click="$emit('modificar', al)" class="btn btn-sm btn-outline-primary me-1">✎ Editar</button>
                <button @click="eliminar(al)" class="btn btn-sm btn-outline-danger">✖ Eliminar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import BusquedaComponent from './BusquedaComponent.vue';

const emit = defineEmits(['nuevo', 'modificar']);
const alumnos = ref([]);
const cargando = ref(false);
const busqueda = ref('');

async function cargar() {
  cargando.value = true;
  try {
    let url = '/api/alumnos';
    if (busqueda.value.trim()) url += '?buscar=' + encodeURIComponent(busqueda.value);
    const r = await fetch(url, { headers: { Accept: 'application/json' } });
    alumnos.value = r.ok ? await r.json() : [];
  } catch (e) {
    alumnos.value = [];
  } finally {
    cargando.value = false;
  }
}

function onSearch(q) { busqueda.value = q; cargar(); }

async function eliminar(al) {
  if (!confirm('¿Eliminar a ' + al.nombre + '?')) return;
  const r = await fetch('/api/alumnos/' + (al.idAlumno || al.id), { method: 'DELETE' });
  if (r.ok) { window.alertify.success('Alumno eliminado'); cargar(); }
  else window.alertify.error('Error al eliminar');
}

onMounted(cargar);
defineExpose({ cargar });
</script>
