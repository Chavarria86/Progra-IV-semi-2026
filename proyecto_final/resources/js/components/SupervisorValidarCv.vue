<template>
  <div class="dashboard-section-card animate-fade-in">
    <h5 class="card-title"><i class="bi bi-check2-square text-warning"></i> Validar CV de Pasante</h5>
    <form @submit.prevent="submitValidacion" class="mt-3">
      <div class="form-group-custom">
        <label>Seleccionar Pasante</label>
        <select class="form-select-custom" v-model="cvForm.pasante_id" required :disabled="cargandoCvs">
          <option value="" disabled>{{ cargandoCvs ? 'Cargando CVs pendientes...' : (cvsPendientes.length === 0 ? 'No hay CVs pendientes' : 'Seleccione un pasante pendiente') }}</option>
          <option v-for="cv in cvsPendientes" :key="cv.id" :value="cv.id">
            {{ cv.nombre_pasante }} — {{ cv.titulo }} ({{ cv.fecha_subida }})
          </option>
        </select>
      </div>
      <div class="form-group-custom">
        <label>Decisión de Validación</label>
        <div class="radio-group">
          <label class="radio-option" :class="{ active: cvForm.estado === 'aprobado' }">
            <input type="radio" v-model="cvForm.estado" value="aprobado" />
            <span class="radio-dot"></span>
            <i class="bi bi-check-circle-fill text-success me-1"></i> Aprobar — Avanza a Fase 2
          </label>
          <label class="radio-option" :class="{ active: cvForm.estado === 'rechazado' }">
            <input type="radio" v-model="cvForm.estado" value="rechazado" />
            <span class="radio-dot"></span>
            <i class="bi bi-x-circle-fill text-danger me-1"></i> Rechazar — Solicitar correcciones
          </label>
        </div>
      </div>
      <div class="form-group-custom" v-if="cvForm.estado === 'rechazado'">
        <label>Comentarios / Correcciones requeridas</label>
        <textarea class="form-textarea-custom" rows="3" v-model="cvForm.comentarios"
          placeholder="Especifique qué debe corregir el pasante en su CV..." required></textarea>
      </div>
      <button type="submit" class="btn-primary-custom w-100 mt-2" :disabled="cargandoCV">
        <span v-if="cargandoCV" class="spinner-border spinner-border-sm me-2"></span>
        <i v-else class="bi bi-send-check me-1"></i>
        Procesar Validación
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  cargandoCV: { type: Boolean, default: false },
  usuario: { type: Object, default: () => ({}) }
});

const emit = defineEmits(['validar']);

const cvForm = ref({ pasante_id: '', estado: 'aprobado', comentarios: '' });
const cvsPendientes = ref([]);
const cargandoCvs = ref(true);

onMounted(() => {
  cargarCvsPendientes();
});

const cargarCvsPendientes = async () => {
  cargandoCvs.value = true;
  try {
    const res = await axios.get('/api/supervisor/cvs-pendientes', {
      headers: { 'X-User-Id': props.usuario?.id || 2 }
    });
    cvsPendientes.value = res.data.cvs || [];
  } catch (err) {
    console.error('Error cargando CVs pendientes:', err);
  } finally {
    cargandoCvs.value = false;
  }
};

const submitValidacion = async () => {
  if (!cvForm.value.pasante_id) return;
  
  // En lugar de emitir directamente a local, podríamos llamar al endpoint aquí mismo,
  // pero mantendremos la emisión para que DashboardApp (o su parent) lo maneje, 
  // o podemos manejar el axios call aquí. Ya que estamos integrando, hagamos axios aquí para independizarlo.
  
  const cvElegido = cvsPendientes.value.find(cv => cv.id === cvForm.value.pasante_id);
  
  try {
    if (cvForm.value.estado === 'aprobado') {
      await axios.put(`/api/supervisor/cvs/${cvForm.value.pasante_id}/validar`, {}, {
        headers: { 'X-User-Id': props.usuario?.id || 2 }
      });
      alertify.success(`CV de ${cvElegido?.nombre_pasante} aprobado.`);
    } else {
      await axios.put(`/api/supervisor/cvs/${cvForm.value.pasante_id}/rechazar`, {
        observaciones: cvForm.value.comentarios
      }, {
        headers: { 'X-User-Id': props.usuario?.id || 2 }
      });
      alertify.warning('CV rechazado con observaciones.');
    }
    
    // Recargar lista y limpiar form
    await cargarCvsPendientes();
    cvForm.value = { pasante_id: '', estado: 'aprobado', comentarios: '' };
    emit('validar'); // Solo por si el padre necesita saber
  } catch (err) {
    alertify.error('Error al procesar la validación.');
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
.text-warning {
  color: #d97706 !important;
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
.form-textarea-custom {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 14px;
  color: #0f172a;
  background: #f8fafc;
  resize: vertical;
  min-height: 80px;
  transition: border-color 0.2s;
}
.form-textarea-custom:focus {
  outline: none;
  border-color: #001374;
  box-shadow: 0 0 0 3px rgba(0, 19, 116, 0.1);
}

/* Radio Options */
.radio-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.radio-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 11px 16px;
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  color: #334155;
  transition: all 0.2s;
}
.radio-option input { display: none; }
.radio-dot {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 2px solid #cbd5e1;
  flex-shrink: 0;
  transition: all 0.2s;
}
.radio-option.active {
  border-color: #001374;
  background: rgba(0, 19, 116, 0.04);
}
.radio-option.active .radio-dot {
  border-color: #001374;
  background: #001374;
}

/* Buttons */
.btn-primary-custom {
  background-color: #001374;
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
.btn-primary-custom:hover:not(:disabled) { background-color: #010c67; }
.btn-primary-custom:disabled { opacity: 0.65; cursor: not-allowed; }

.w-100 { width: 100%; }
.mt-2 { margin-top: 8px; }
.mt-3 { margin-top: 16px; }
</style>
