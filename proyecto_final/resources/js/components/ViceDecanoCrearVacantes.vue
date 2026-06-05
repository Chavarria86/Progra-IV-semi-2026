<template>
  <div class="crear-vacantes-view animate-fade-in">
    <div class="dashboard-section-card max-w-700">
      <h5 class="card-title"><i class="bi bi-plus-square-dotted text-accent"></i> Publicar Nueva Vacante</h5>
      <p class="text-muted mb-4">Llena el formulario para crear una oportunidad laboral que estará disponible inmediatamente para todos los pasantes registrados en la plataforma.</p>

      <form @submit.prevent="crearVacante">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">Nombre de la Empresa</label>
            <input type="text" class="form-control" v-model="form.empresa" placeholder="Ej: TechCorp S.A." required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">Área o Categoría</label>
            <select class="form-select" v-model="form.area" required>
              <option value="" disabled>Selecciona un área...</option>
              <option value="desarrollo">Desarrollo de Software</option>
              <option value="redes">Redes e Infraestructura</option>
              <option value="seguridad">Seguridad Informática</option>
              <option value="diseño">Diseño UI/UX</option>
              <option value="analisis">Análisis de Datos</option>
              <option v-for="area in customAreas" :key="area" :value="area">{{ area }}</option>
              <option value="otro">Otro</option>
            </select>
          </div>

          <!-- Cuadro de texto para custom area -->
          <div class="col-12 mb-3 animate-fade-in" v-if="form.area === 'otro'">
            <label class="form-label fw-semibold text-accent"><i class="bi bi-pencil-square"></i> Especificar Otra Área</label>
            <div class="input-group">
              <input type="text" class="form-control" v-model="nuevaArea" placeholder="Ej: Inteligencia Artificial / Machine Learning" required>
              <button class="btn btn-accent px-4" type="button" @click="guardarAreaLocal">
                <i class="bi bi-save2-fill me-1"></i> Guardar
              </button>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Descripción del Puesto</label>
          <textarea class="form-control" v-model="form.descripcion" rows="4" placeholder="Describe los requisitos, el rol y los beneficios de la vacante..." required></textarea>
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold">Estado de Publicación</label>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" v-model="form.activa" id="estadoSwitch">
            <label class="form-check-label" for="estadoSwitch">
              {{ form.activa ? 'Activa (Visible para pasantes)' : 'Inactiva (Oculta)' }}
            </label>
          </div>
        </div>

        <button type="submit" class="btn btn-accent w-100 py-2" :disabled="cargando">
          <span v-if="cargando" class="spinner-border spinner-border-sm me-2"></span>
          <i v-else class="bi bi-cloud-arrow-up-fill me-2"></i>
          {{ cargando ? 'Publicando...' : 'Publicar Vacante' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const cargando = ref(false);
const form = ref({
  empresa: '',
  area: '',
  descripcion: '',
  activa: true
});

const nuevaArea = ref('');
const customAreas = ref([]);

onMounted(() => {
  const saved = localStorage.getItem('vicedecano_custom_areas');
  if (saved) {
    try {
      customAreas.value = JSON.parse(saved) || [];
    } catch (e) {
      customAreas.value = [];
    }
  }
});

const guardarAreaLocal = () => {
  const val = nuevaArea.value.trim();
  if (!val) {
    alertify.error('Por favor escribe el nombre de la nueva área.');
    return;
  }
  
  // Evitar duplicar las opciones nativas
  const nativas = ['desarrollo', 'redes', 'seguridad', 'diseño', 'analisis', 'otro'];
  if (nativas.includes(val.toLowerCase())) {
    alertify.error('Esta área ya existe como opción predeterminada.');
    return;
  }

  // Evitar duplicados en customAreas
  if (!customAreas.value.includes(val)) {
    customAreas.value.push(val);
    localStorage.setItem('vicedecano_custom_areas', JSON.stringify(customAreas.value));
  }
  
  form.value.area = val;
  nuevaArea.value = '';
  alertify.success(`Área "${val}" guardada en localstore.`);
};

const crearVacante = async () => {
  cargando.value = true;
  try {
    await axios.post('/api/vicedecano/vacantes', { 
      ...form.value, 
      estado: form.value.activa ? 'activa' : 'inactiva' 
    });
    
    alertify.success('Vacante publicada exitosamente en la plataforma.');
    form.value = { empresa: '', area: '', descripcion: '', activa: true };
  } catch (error) {
    console.error(error);
    alertify.error('Error al intentar crear la vacante.');
  } finally {
    cargando.value = false;
  }
};
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.max-w-700 { max-width: 700px; margin: 0 auto; }
.dashboard-section-card {
  background: var(--surface, #fff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: var(--radius, 12px);
  padding: 30px;
  box-shadow: 0 4px 18px rgba(0,0,0,0.03);
}
.card-title { font-family: 'Lora', serif; font-weight: 600; font-size: 1.25rem; display: flex; align-items: center; gap: 10px; }
.text-accent { color: var(--accent, #67000F); }
.btn-accent {
  background-color: var(--accent, #67000F);
  color: white;
  font-weight: 600;
  border: none;
  transition: opacity 0.2s;
}
.btn-accent:hover:not(:disabled) { background-color: color-mix(in srgb, var(--accent, #67000F) 85%, black); color: white; }
.form-control:focus, .form-select:focus {
  border-color: var(--accent, #67000F);
  box-shadow: 0 0 0 0.25rem color-mix(in srgb, var(--accent, #67000F) 25%, transparent);
}
</style>
