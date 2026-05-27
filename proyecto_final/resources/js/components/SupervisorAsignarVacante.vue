<template>
  <div class="dashboard-section-card animate-fade-in">
    <h5 class="card-title"><i class="bi bi-building-fill-add text-primary"></i> Crear Nueva Vacante</h5>
    <form @submit.prevent="crearVacante" class="mt-3">
      <div class="form-group-custom">
        <label>Empresa</label>
        <input type="text" class="form-select-custom" v-model="nuevaVacante.empresa" placeholder="Ej. TechCorp S.A." required />
      </div>
      <div class="form-group-custom">
        <label>Área de Especialización</label>
        <select class="form-select-custom" v-model="nuevaVacante.area" required>
          <option value="" disabled>Seleccione el área</option>
          <option value="desarrollo">Desarrollo de Software</option>
          <option value="infraestructura">Infraestructura y Redes</option>
          <option value="seguridad">Seguridad Informática</option>
          <option value="diseño">Diseño UI/UX</option>
        </select>
      </div>
      <div class="form-group-custom">
        <label>Descripción del Perfil Buscado</label>
        <textarea class="form-select-custom" v-model="nuevaVacante.descripcion" rows="3" placeholder="Se busca pasante proactivo con conocimientos en..." required></textarea>
      </div>
      <button type="submit" class="btn-success-custom w-100 mt-2" :disabled="creando">
        <span v-if="creando" class="spinner-border spinner-border-sm me-2"></span>
        <i v-else class="bi bi-plus-circle-fill me-1"></i>
        Publicar Vacante
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const creando = ref(false);
const nuevaVacante = ref({ empresa: '', area: '', descripcion: '' });

const crearVacante = async () => {
  creando.value = true;
  try {
    // Usar el ID estático simulado 2 para el supervisor
    await axios.post('/api/supervisor/vacantes', nuevaVacante.value, {
      headers: { 'X-User-Id': 2 }
    });
    alertify.success('Vacante creada y publicada exitosamente.');
    nuevaVacante.value = { empresa: '', area: '', descripcion: '' };
  } catch (err) {
    alertify.error('Error al crear la vacante.');
  } finally {
    creando.value = false;
  }
};
</script>

<style scoped>
.dashboard-section-card {
  background-color: #ffffff;
  border-radius: 12px;
  padding: 28px;
  box-shadow: 0 4px 18px rgba(0,0,0,0.03);
  border: 1px solid #e2e8f0;
  margin-bottom: 24px;
}
.card-title {
  font-family: 'Lora', serif;
  font-size: 18px;
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}
.text-primary {
  color: #001374 !important;
}

/* Form Controls */
.form-group-custom {
  margin-bottom: 18px;
}
.form-group-custom label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #475569;
  margin-bottom: 7px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.form-select-custom {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 14px;
  color: #0f172a;
  background: #f8fafc;
  transition: border-color 0.2s, box-shadow 0.2s;
  appearance: none;
}
.form-select-custom:focus {
  outline: none;
  border-color: #001374;
  box-shadow: 0 0 0 3px rgba(0, 19, 116, 0.1);
  background: #ffffff;
}

/* Buttons */
.btn-success-custom {
  background-color: #059669;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  padding: 11px 20px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: background-color 0.2s;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}
.btn-success-custom:hover:not(:disabled) { background-color: #047857; }
.btn-success-custom:disabled { opacity: 0.65; cursor: not-allowed; }

.w-100 { width: 100%; }
.mt-2 { margin-top: 8px; }
.mt-3 { margin-top: 16px; }
</style>
