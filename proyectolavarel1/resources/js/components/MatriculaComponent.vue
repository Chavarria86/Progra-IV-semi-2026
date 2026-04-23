<template>
  <div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0">LISTA DE MATRÍCULAS</h4>
      <button @click="$emit('nuevo')" class="btn btn-sm btn-outline-light">+ Nueva Matrícula</button>
    </div>
    <div class="card-body">
      <BusquedaComponent placeholder="Buscar matrícula..." @search="onSearch" />
      <div class="table-responsive mt-2">
        <table class="table table-hover table-bordered align-middle">
          <thead class="table-dark">
            <tr>
              <th>ALUMNO</th><th>MATERIA</th><th>DOCENTE</th><th>PERIODO / AÑO</th><th>ESTADO</th><th class="text-center">ACCIONES</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="cargando"><td colspan="6" class="text-center py-3"><div class="spinner-border spinner-border-sm"></div> Cargando...</td></tr>
            <tr v-else-if="matriculas.length === 0"><td colspan="6" class="text-center text-muted py-4">No se encontraron registros.</td></tr>
            <tr v-else v-for="m in matriculas" :key="m.id">
              <td>{{ nombre(alumnos, m.idAlumno, 'idAlumno') }}</td>
              <td>{{ nombre(materias, m.idMateria, 'idMateria') }}</td>
              <td>{{ nombre(docentes, m.idDocente, 'Id_Docentes') }}</td>
              <td>{{ m.periodo }} / {{ m.gestion }}</td>
              <td><span :class="m.estado==='Activo' ? 'badge bg-success' : 'badge bg-danger'">{{ m.estado }}</span></td>
              <td class="text-center">
                <button @click="$emit('modificar', m)" class="btn btn-sm btn-outline-primary me-1">✎ Editar</button>
                <button @click="eliminar(m)" class="btn btn-sm btn-outline-danger">✖ Eliminar</button>
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
const matriculas = ref([]);
const alumnos = ref([]);
const materias = ref([]);
const docentes = ref([]);
const cargando = ref(false);
const busqueda = ref('');

function nombre(lista, id, campo) {
  // En el template de Vue 3, los refs se desempaquetan automáticamente.
  // Por lo tanto, 'lista' puede ser el array directamente.
  const arr = Array.isArray(lista) ? lista : (lista ? lista.value : []);
  if (!arr) return id;
  const item = arr.find(function(x) { return (x[campo] || x.id) === id; });
  return item ? item.nombre : id;
}

async function cargar() {
  cargando.value = true;
  try {
    let url = '/api/matriculas';
    if (busqueda.value.trim()) url += '?buscar=' + encodeURIComponent(busqueda.value);
    const r = await fetch(url, { headers: { Accept: 'application/json' } });
    matriculas.value = r.ok ? await r.json() : [];
  } catch (e) {
    matriculas.value = [];
  } finally {
    cargando.value = false;
  }
}

async function cargarDeps() {
  try {
    const ra = await fetch('/api/alumnos', { headers: { Accept: 'application/json' } });
    const rm = await fetch('/api/materias', { headers: { Accept: 'application/json' } });
    const rd = await fetch('/api/docentes', { headers: { Accept: 'application/json' } });
    if (ra.ok) alumnos.value = await ra.json();
    if (rm.ok) materias.value = await rm.json();
    if (rd.ok) docentes.value = await rd.json();
  } catch (e) { console.error(e); }
}

function onSearch(q) { busqueda.value = q; cargar(); }

async function eliminar(m) {
  if (!confirm('¿Eliminar esta matrícula?')) return;
  const r = await fetch('/api/matriculas/' + (m.idMatricula || m.id), { method: 'DELETE' });
  if (r.ok) { window.alertify.success('Matrícula eliminada'); cargar(); }
  else window.alertify.error('Error al eliminar');
}

onMounted(async function() { await cargarDeps(); await cargar(); });
defineExpose({ cargar });
</script>
