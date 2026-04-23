<template>
  <div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0">LISTA DE MATERIAS</h4>
      <button @click="$emit('nuevo')" class="btn btn-sm btn-outline-light">+ Nueva Materia</button>
    </div>
    <div class="card-body">
      <BusquedaComponent placeholder="Buscar materia..." @search="onSearch" />
      <div class="table-responsive mt-2">
        <table class="table table-hover table-bordered align-middle">
          <thead class="table-dark">
            <tr>
              <th>CÓDIGO</th><th>NOMBRE</th><th>UV</th><th class="text-center">ACCIONES</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="cargando"><td colspan="4" class="text-center py-3"><div class="spinner-border spinner-border-sm"></div> Cargando...</td></tr>
            <tr v-else-if="materias.length === 0"><td colspan="4" class="text-center text-muted py-4">No se encontraron registros.</td></tr>
            <tr v-else v-for="mat in materias" :key="mat.id">
              <td><strong>{{ mat.codigo }}</strong></td>
              <td>{{ mat.nombre }}</td>
              <td>{{ mat.uv }}</td>
              <td class="text-center">
                <button @click="$emit('modificar', mat)" class="btn btn-sm btn-outline-primary me-1">✎ Editar</button>
                <button @click="eliminar(mat)" class="btn btn-sm btn-outline-danger">✖ Eliminar</button>
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
const materias = ref([]);
const cargando = ref(false);
const busqueda = ref('');

async function cargar() {
  cargando.value = true;
  try {
    let url = '/api/materias';
    if (busqueda.value.trim()) url += '?buscar=' + encodeURIComponent(busqueda.value);
    const r = await fetch(url, { headers: { Accept: 'application/json' } });
    materias.value = r.ok ? await r.json() : [];
  } catch (e) {
    materias.value = [];
  } finally {
    cargando.value = false;
  }
}

function onSearch(q) { busqueda.value = q; cargar(); }

async function eliminar(mat) {
  if (!confirm('¿Eliminar la materia ' + mat.nombre + '?')) return;
  const r = await fetch('/api/materias/' + (mat.idMateria || mat.id), { method: 'DELETE' });
  if (r.ok) { window.alertify.success('Materia eliminada'); cargar(); }
  else window.alertify.error('Error al eliminar');
}

onMounted(cargar);
defineExpose({ cargar });
</script>
