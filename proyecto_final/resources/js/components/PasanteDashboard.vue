<template>
  <div class="pasante-dashboard">

    <!-- Wizard de CV (modal) -->
    <CvWizard 
      v-if="mostrarWizard" 
      :cvAEditar="cvSeleccionadoParaEditar" 
      @cerrar="cerrarWizard" 
      @guardado="recargarCvs" 
    />

    <!-- Componente activo según la sección activa -->
    <PasanteOverview
      v-if="seccionActiva === 'dashboard'"
      :usuario="usuario"
      :perfilCompleto="perfilCompleto"
      @cambiarSeccion="$emit('cambiarSeccion', $event)"
      @abrirWizard="abrirWizard"
    />

    <PasantePerfilCvs
      v-else-if="seccionActiva === 'perfil' || seccionActiva === 'cv'"
      :usuario="usuario"
      :cvs="cvs"
      :cargandoCvs="cargandoCvs"
      @abrirWizard="abrirWizard"
      @editarCv="editarCv"
      @eliminarCv="eliminarCv"
    />

    <PasanteInformes
      v-else-if="seccionActiva === 'informes'"
      :usuario="usuario"
      :informes="informes"
      :cargandoInformes="cargandoInformes"
      @informeEnviado="recargarInformes"
    />

    <PasanteProgreso
      v-else-if="seccionActiva === 'progreso'"
      :perfilCompleto="perfilCompleto"
    />

    <PasanteVacantes
      v-else-if="seccionActiva === 'vacantes'"
      :usuario="usuario"
      @postulado="cargarPerfilCompleto"
    />

  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import CvWizard from './CvWizard.vue';

// Importación de subcomponentes refactorizados
import PasanteOverview from './PasanteOverview.vue';
import PasantePerfilCvs from './PasantePerfilCvs.vue';
import PasanteInformes from './PasanteInformes.vue';
import PasanteProgreso from './PasanteProgreso.vue';

import PasanteVacantes from './PasanteVacantes.vue';

const props = defineProps({ seccionActiva: String });
const emit = defineEmits(['cambiarSeccion']);

const usuario = ref({});
const mostrarWizard = ref(false);
const cvSeleccionadoParaEditar = ref(null);
const cvs = ref([]);
const cargandoCvs = ref(false);

const perfilCompleto = ref({
  fase_actual: 'F1',
  estado: 'pendiente',
  horas_aprobadas: 0,
  horas_pendientes: 0
});

const informes = ref([]);
const cargandoInformes = ref(false);

onMounted(() => {
  const userJson = localStorage.getItem('usuario');
  if (userJson) {
    usuario.value = JSON.parse(userJson);
    cargarCvs();
    cargarPerfilCompleto();
    cargarInformes();
  }
});

// Abrir wizard cuando se haga clic en "Mi CV" del sidebar
watch(() => props.seccionActiva, (val) => {
  if (val === 'cv') {
    cvSeleccionadoParaEditar.value = null;
    mostrarWizard.value = true;
  }
});

const cargarCvs = async () => {
  if (!usuario.value.id) return;
  cargandoCvs.value = true;
  try {
    const res = await axios.get(`/api/cv/${usuario.value.id}`);
    cvs.value = res.data.cvs || [];
  } catch (err) {
    cvs.value = [];
  } finally {
    cargandoCvs.value = false;
  }
};

const cargarPerfilCompleto = async () => {
  if (!usuario.value.id) return;
  try {
    const res = await axios.get('/api/pasante/perfil', {
      headers: {
        'X-User-Id': usuario.value.id
      }
    });
    if (res.data && res.data.perfil) {
      perfilCompleto.value = res.data.perfil;
    }
  } catch (err) {
    console.error('Error al cargar perfil completo:', err);
  }
};

const cargarInformes = async () => {
  if (!usuario.value.id) return;
  cargandoInformes.value = true;
  try {
    const res = await axios.get('/api/pasante/informes', {
      headers: { 'X-User-Id': usuario.value.id }
    });
    informes.value = res.data.informes || [];
  } catch (err) {
    console.error('Error al cargar informes:', err);
  } finally {
    cargandoInformes.value = false;
  }
};

const recargarCvs = async () => {
  await cargarCvs();
};

const recargarInformes = async () => {
  await cargarInformes();
  await cargarPerfilCompleto();
};

const abrirWizard = () => {
  cvSeleccionadoParaEditar.value = null;
  mostrarWizard.value = true;
};

const editarCv = (cv) => {
  cvSeleccionadoParaEditar.value = cv;
  mostrarWizard.value = true;
};

const cerrarWizard = () => {
  cvSeleccionadoParaEditar.value = null;
  mostrarWizard.value = false;
};

const eliminarCv = (cvId) => {
  alertify.confirm(
    'Eliminar Currículum',
    '¿Estás seguro de que deseas eliminar este CV? Esta acción no se puede deshacer.',
    async () => {
      try {
        await axios.delete(`/api/cv/${cvId}`);
        cvs.value = cvs.value.filter(c => c.id !== cvId);
        alertify.success('CV eliminado correctamente.');
      } catch (err) {
        alertify.error('Error al eliminar el CV. Inténtalo de nuevo.');
      }
    },
    () => {}
  ).set({ labels: { ok: 'Sí, eliminar', cancel: 'Cancelar' } });
};
</script>

<style scoped>
.pasante-dashboard {
  max-width: 1200px;
  margin: 0 auto;
}
</style>
