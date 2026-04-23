<template>
  <div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0">LISTA DE INSCRIPCIONES</h4>
      <button @click="$emit('nuevo')" class="btn btn-sm btn-outline-light">+ Nueva Inscripción</button>
    </div>
    <div class="card-body">
      <BusquedaComponent placeholder="Buscar inscripción..." @search="onSearch" />
      <div class="table-responsive mt-2">
        <table class="table table-hover table-bordered align-middle">
          <thead class="table-dark">
            <tr>
              <th>#</th><th>ALUMNO</th><th>MATERIA</th><th>FECHA</th><th class="text-center">ACCIONES</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="cargando"><td colspan="5" class="text-center py-3"><div class="spinner-border spinner-border-sm"></div> Cargando...</td></tr>
            <tr v-else-if="inscripciones.length === 0"><td colspan="5" class="text-center text-muted py-4">No se encontraron registros.</td></tr>
            <tr v-else v-for="ins in inscripciones" :key="ins.idInscripcion">
              <td><strong>{{ ins.idInscripcion }}</strong></td>
              <td>{{ nombreAlumno(ins.idAlumno) }}</td>
              <td>{{ nombreMateria(ins.idMateria) }}</td>
              <td>{{ ins.fecha }}</td>
              <td class="text-center">
                <button @click="$emit('modificar', ins)" class="btn btn-sm btn-outline-primary me-1">✎ Editar</button>
                <button @click="eliminar(ins)" class="btn btn-sm btn-outline-danger">✖ Eliminar</button>
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
const inscripciones = ref([]);
const alumnos = ref([]);
const materias = ref([]);
const cargando = ref(false);
const busqueda = ref('');

function nombreAlumno(id) {
  const a = alumnos.value.find(function(x) { return (x.idAlumno || x.id) === id; });
  return a ? a.nombre : id;
}
function nombreMateria(id) {
  const m = materias.value.find(function(x) { return (x.idMateria || x.id) === id; });
  return m ? m.nombre : id;
}

async function cargar() {
  cargando.value = true;
  try {
    let url = '/api/inscripciones';
    if (busqueda.value.trim()) url += '?buscar=' + encodeURIComponent(busqueda.value);
    const r = await fetch(url, { headers: { Accept: 'application/json' } });
    inscripciones.value = r.ok ? await r.json() : [];
  } catch (e) {
    inscripciones.value = [];
  } finally {
    cargando.value = false;
  }
}

async function cargarDeps() {
  try {
    const ra = await fetch('/api/alumnos', { headers: { Accept: 'application/json' } });
    const rm = await fetch('/api/materias', { headers: { Accept: 'application/json' } });
    if (ra.ok) alumnos.value = await ra.json();
    if (rm.ok) materias.value = await rm.json();
  } catch (e) { console.error(e); }
}

function onSearch(q) { busqueda.value = q; cargar(); }

async function eliminar(ins) {
  if (!confirm('¿Eliminar la inscripción #' + ins.idInscripcion + '?')) return;
  const r = await fetch('/api/inscripciones/' + ins.idInscripcion, { method: 'DELETE' });
  if (r.ok) { window.alertify.success('Inscripción eliminada'); cargar(); }
  else window.alertify.error('Error al eliminar');
}

onMounted(async function() { await cargarDeps(); await cargar(); });
defineExpose({ cargar });
</script>
