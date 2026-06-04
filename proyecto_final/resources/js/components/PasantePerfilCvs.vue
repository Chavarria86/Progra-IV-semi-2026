<template>
  <div class="perfil-view animate-fade-in">
    <!-- Currículums creados -->
    <div class="dashboard-section-card">
      <div class="cv-section-header">
        <h5 class="card-title m-0"><i class="bi bi-file-earmark-pdf-fill text-danger"></i> Mis Currículums</h5>
        <button class="btn-crear-cv" @click="$emit('abrirWizard')">
          <i class="bi bi-plus-lg me-1"></i> Crear nuevo
        </button>
      </div>

      <!-- Buscador de CVs (visible si hay CVs creados) -->
      <div v-if="cvs.length > 0" class="cv-search-bar mt-3 mb-2">
        <div class="input-group-custom position-relative">
          <input 
            type="text" 
            class="form-control-custom ps-5" 
            placeholder="Buscar en mis CVs (título, perfil, habilidades, logros...)" 
            v-model="filtroBusqueda"
          />
          <i class="bi bi-search position-absolute" style="left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 16px;"></i>
        </div>
      </div>

      <!-- Loading cvs -->
      <div v-if="cargandoCvs" class="cv-loading mt-4">
        <span class="spinner-border spinner-border-sm me-2 text-primary"></span> Cargando documentos...
      </div>

      <!-- Lista vacía por falta de CVs -->
      <div v-else-if="cvs.length === 0" class="cv-empty-state mt-4">
        <i class="bi bi-file-earmark-x cv-empty-icon"></i>
        <p class="cv-status-text text-danger font-weight-bold">Aún no has creado ningún currículum</p>
        <p class="cv-hint">Haz clic en "Crear nuevo" para empezar a generar tu hoja de vida profesional.</p>
      </div>

      <!-- Lista vacía por filtro de búsqueda -->
      <div v-else-if="cvsFiltrados.length === 0" class="cv-empty-state mt-4">
        <i class="bi bi-search cv-empty-icon text-muted"></i>
        <p class="cv-status-text text-muted font-weight-bold">No se encontraron resultados</p>
        <p class="cv-hint">Prueba con otros términos o limpia el campo de búsqueda.</p>
      </div>

      <!-- Lista de CVs -->
      <div v-else class="cv-lista mt-3">
        <div v-for="cv in cvsFiltrados" :key="cv.id" class="cv-card">
          <div class="cv-card-icon">
            <i class="bi bi-file-earmark-pdf-fill"></i>
          </div>
          <div class="cv-card-info">
            <p class="cv-card-titulo">{{ cv.titulo_cv || 'Mi CV' }}</p>
            <p class="cv-card-fecha">Creado: {{ formatearFecha(cv.created_at) }}</p>
          </div>
          <div class="cv-card-acciones">
            <a v-if="cv.url_publica" :href="cv.url_publica" target="_blank" class="btn-accion btn-ver" title="Ver PDF">
              <i class="bi bi-eye"></i>
            </a>
            <a v-if="cv.url_publica" :href="cv.url_publica" download class="btn-accion btn-descargar" title="Descargar PDF">
              <i class="bi bi-download"></i>
            </a>
            <button class="btn-accion btn-editar" title="Editar CV" @click="$emit('editarCv', cv)">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="btn-accion btn-eliminar" title="Eliminar" @click="$emit('eliminarCv', cv.id)">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  usuario: { type: Object, default: () => ({}) },
  cvs: { type: Array, default: () => [] },
  cargandoCvs: { type: Boolean, default: false }
});

defineEmits(['abrirWizard', 'editarCv', 'eliminarCv']);

const filtroBusqueda = ref('');

const cvsFiltrados = computed(() => {
  if (!filtroBusqueda.value.trim()) return props.cvs;
  
  const query = filtroBusqueda.value.toLowerCase().trim();
  
  return props.cvs.filter(cv => {
    return (
      (cv.titulo_cv && cv.titulo_cv.toLowerCase().includes(query)) ||
      (cv.nombre_completo && cv.nombre_completo.toLowerCase().includes(query)) ||
      (cv.sobre_mi && cv.sobre_mi.toLowerCase().includes(query)) ||
      (cv.educacion && cv.educacion.toLowerCase().includes(query)) ||
      (cv.objetivo && cv.objetivo.toLowerCase().includes(query)) ||
      (cv.valores && cv.valores.toLowerCase().includes(query)) ||
      (cv.conocimientos && cv.conocimientos.toLowerCase().includes(query)) ||
      (cv.idiomas && cv.idiomas.toLowerCase().includes(query)) ||
      (cv.certificados && cv.certificados.toLowerCase().includes(query)) ||
      (cv.habilidades && cv.habilidades.toLowerCase().includes(query)) ||
      (cv.logros && cv.logros.toLowerCase().includes(query)) ||
      (cv.proyectos_sociales && cv.proyectos_sociales.toLowerCase().includes(query))
    );
  });
});

const formatearFecha = (fecha) => {
  if (!fecha) return '';
  return new Date(fecha).toLocaleDateString('es-ES', {
    year: 'numeric', month: 'long', day: 'numeric'
  });
};
</script>

<style scoped>
.perfil-view {
  width: 100%;
}

.animate-fade-in {
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Cards global styling */
.dashboard-section-card {
  background-color: #ffffff;
  border-radius: 12px;
  padding: 28px;
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
  border: 1px solid #e2e8f0;
  margin-bottom: 24px;
}

.card-title {
  font-family: 'Lora', serif;
  font-size: 20px;
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.text-primary {
  color: #001374 !important;
}

/* Form Styles */
.form-group-custom {
  margin-bottom: 20px;
}

.form-group-custom label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #475569;
  margin-bottom: 6px;
}

.input-display {
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 10px 14px;
  min-height: 42px;
  font-size: 15px;
  color: #0f172a;
  font-weight: 500;
}

.form-control-custom {
  background-color: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 10px 14px;
  width: 100%;
  font-size: 15px;
  color: #0f172a;
  transition: border-color 0.2s;
}

.form-control-custom:focus {
  outline: none;
  border-color: #001374;
}

/* CV Section Styling */
.cv-section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #f1f5f9;
  padding-bottom: 15px;
}

.btn-crear-cv {
  background-color: #001374;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-crear-cv:hover {
  background-color: #010c67;
}

.cv-loading {
  text-align: center;
  color: #64748b;
  font-size: 14px;
}

.cv-empty-state {
  text-align: center;
  padding: 40px 20px;
}

.cv-empty-icon {
  font-size: 55px;
  color: #cbd5e1;
  display: block;
  margin-bottom: 12px;
}

.cv-status-text {
  font-size: 16px;
  margin-bottom: 4px;
}

.cv-hint {
  font-size: 13px;
  color: #94a3b8;
  max-width: 320px;
  margin: 0 auto;
}

.cv-lista {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.cv-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 14px 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  transition: all 0.2s;
}

.cv-card:hover {
  box-shadow: 0 4px 12px rgba(0, 19, 116, 0.06);
  border-color: #cbd5e1;
  transform: translateY(-1px);
}

.cv-card-icon {
  font-size: 32px;
  color: #ef4444;
  flex-shrink: 0;
}

.cv-card-info {
  flex: 1;
  min-width: 0;
}

.cv-card-titulo {
  font-family: 'Lora', serif;
  font-size: 15px;
  font-weight: 600;
  color: #0f172a;
  margin: 0 0 3px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.cv-card-fecha {
  font-size: 12px;
  color: #64748b;
  margin: 0;
}

.cv-card-acciones {
  display: flex;
  gap: 6px;
}

.btn-accion {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
}

.btn-ver { background: #eff6ff; color: #1d4ed8; }
.btn-descargar { background: #ecfdf5; color: #047857; }
.btn-editar { background: #fffbeb; color: #d97706; }
.btn-eliminar { background: #fef2f2; color: #b91c1c; }

.btn-accion:hover {
  transform: scale(1.05);
}

.position-relative {
  position: relative;
}
.position-absolute {
  position: absolute;
}
.ps-5 {
  padding-left: 42px !important;
}

@media (max-width: 576px) {
  .cv-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  .cv-card-acciones {
    width: 100%;
    justify-content: flex-start;
  }
}
</style>
