<template>
  <div class="perfil-card">

    <!-- Wizard de CV (modal) -->
    <CvWizard v-if="mostrarWizard" @cerrar="mostrarWizard = false" />

    <div class="perfil-form">
      <div class="form-group-custom">
        <label>Nombres:</label>
        <div class="input-display">{{ usuario.nombres || 'Cargando...' }}</div>
      </div>
      <div class="form-group-custom">
        <label>Apellidos:</label>
        <div class="input-display">{{ usuario.apellidos || 'Cargando...' }}</div>
      </div>
      <div class="form-group-custom">
        <label>Correo Institucional:</label>
        <div class="input-display">{{ usuario.correo || 'Cargando...' }}</div>
      </div>
      <div class="form-group-custom">
        <label><i class="bi bi-link-45deg"></i> Link de Portafolio Digital o Repositorio:</label>
        <div class="input-display"></div>
      </div>
    </div>

    <!-- Sección de CV -->
    <div class="cv-section">
      <p class="cv-status-text">Aun no se ha creado su currículum</p>
      <button class="btn-crear-cv" @click="mostrarWizard = true">
        <i class="bi bi-file-earmark-person me-2"></i> Crear CV
      </button>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import CvWizard from './CvWizard.vue';

const props = defineProps({ seccionActiva: String });

const usuario = ref({});
const mostrarWizard = ref(false);

onMounted(() => {
  const userJson = localStorage.getItem('usuario');
  if (userJson) usuario.value = JSON.parse(userJson);
});

// Abrir wizard cuando se haga clic en "Mi CV" del sidebar
watch(() => props.seccionActiva, (val) => {
  if (val === 'cv') mostrarWizard.value = true;
});
</script>

<style scoped>
.perfil-card {
  background-color: white;
  border-radius: 8px;
  padding: 40px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.05);
  max-width: 800px;
}
.perfil-form { margin-bottom: 50px; max-width: 450px; }
.form-group-custom { margin-bottom: 20px; }
.form-group-custom label {
  display: block; font-family: 'Inter', sans-serif;
  font-weight: 500; font-size: 16px; color: #000; margin-bottom: 5px;
}
.input-display {
  background-color: #DEDCDC; border: 1px solid #B1ABAB;
  border-radius: 6px; padding: 10px 15px; min-height: 42px;
  font-family: 'Inter', sans-serif; font-size: 16px; color: #333;
}
.cv-section {
  background-color: #DEDCDC; border-radius: 12px;
  padding: 50px 20px; text-align: center; max-width: 650px; margin: 0 auto;
}
.cv-status-text {
  color: #67000F; font-family: 'Inter', sans-serif;
  font-size: 18px; font-weight: 500; margin-bottom: 30px;
}
.btn-crear-cv {
  background-color: #010C67; color: white; border: none;
  border-radius: 6px; padding: 12px 36px;
  font-family: 'Inter', sans-serif; font-size: 16px; font-weight: bold;
  cursor: pointer; transition: background-color 0.3s;
}
.btn-crear-cv:hover { background-color: #00589B; }
</style>
