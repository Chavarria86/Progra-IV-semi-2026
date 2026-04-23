<template>
  <div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0">LISTA DE DOCENTES</h4>
      <button @click="$emit('nuevo')" class="btn btn-sm btn-outline-light">+ Nuevo Docente</button>
    </div>
    <div class="card-body">
      <BusquedaComponent placeholder="Buscar docente..." @search="onSearch" />
      <div class="table-responsive mt-2">
        <table class="table table-hover table-bordered align-middle">
          <thead class="table-dark">
            <tr>
              <th>CÓDIGO</th><th>NOMBRE</th><th>ESCALAFÓN</th><th>TELÉFONO</th><th>EMAIL</th><th class="text-center">ACCIONES</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="cargando"><td colspan="6" class="text-center py-3"><div class="spinner-border spinner-border-sm"></div> Cargando...</td></tr>
            <tr v-else-if="docentes.length === 0"><td colspan="6" class="text-center text-muted py-4">No se encontraron registros.</td></tr>
            <tr v-else v-for="doc in docentes" :key="doc.id">
              <td><strong>{{ doc.codigo }}</strong></td>
              <td>{{ doc.nombre }}</td>
              <td>{{ doc.escalafon }}</td>
              <td>{{ doc.telefono }}</td>
              <td>{{ doc.email }}</td>
              <td class="text-center">
                <button @click="$emit('modificar', doc)" class="btn btn-sm btn-outline-primary me-1">✎ Editar</button>
                <button @click="eliminar(doc)" class="btn btn-sm btn-outline-danger">✖ Eliminar</button>
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
const docentes = ref([]);
const cargando = ref(false);
const busqueda = ref('');

async function cargar() {
  cargando.value = true;
  try {
    let url = '/api/docentes';
    if (busqueda.value.trim()) url += '?buscar=' + encodeURIComponent(busqueda.value);
    const r = await fetch(url, { headers: { Accept: 'application/json' } });
    docentes.value = r.ok ? await r.json() : [];
  } catch (e) {
    docentes.value = [];
  } finally {
    cargando.value = false;
  }
}

function onSearch(q) { busqueda.value = q; cargar(); }

async function eliminar(doc) {
  if (!confirm('¿Eliminar al docente ' + doc.nombre + '?')) return;
  const r = await fetch('/api/docentes/' + (doc.Id_Docentes || doc.id), { method: 'DELETE' });
  if (r.ok) { window.alertify.success('Docente eliminado'); cargar(); }
  else window.alertify.error('Error al eliminar');
}

onMounted(cargar);
defineExpose({ cargar });
</script>
