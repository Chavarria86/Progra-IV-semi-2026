<template>
  <div class="eval-card animate-fade-in">
    <div class="eval-header">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
        <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>
        <line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
      </svg>
      <span>Evaluación de Informes Finales</span>
    </div>

    <div class="eval-body">

      <!-- Cargando informes -->
      <div v-if="cargandoInformes" class="text-center py-4">
        <span class="spinner"></span>
        <p class="text-muted mt-2" style="font-size:0.9rem;">Cargando informes pendientes...</p>
      </div>

      <!-- Sin informes -->
      <div v-else-if="informesDisponibles.length === 0" class="empty-state">
        <div style="font-size:2.5rem;">✅</div>
        <p>No hay informes finales pendientes de evaluación.</p>
        <small>Los pasantes deben enviar su informe de tipo <strong>final</strong> para que aparezcan aquí.</small>
      </div>

      <!-- Formulario de evaluación -->
      <form v-else @submit.prevent="submitEvaluacion">

        <!-- Selector de informe -->
        <div class="field-group">
          <label>Seleccionar Informe Final Pendiente</label>
          <div class="select-wrapper">
            <select v-model="evaluacion.informe_id" required>
              <option value="" disabled>Seleccione un informe final para revisar</option>
              <option v-for="inf in informesDisponibles" :key="inf.id" :value="inf.id">
                {{ inf.pasante_nombre }} {{ inf.pasante_apellido }} — {{ inf.area }} ({{ inf.estado }})
              </option>
            </select>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="select-arrow">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </div>
        </div>

        <!-- Detalle del Informe Seleccionado -->
        <div v-if="informeSeleccionado" class="informe-detalle-box animate-fade-in">
          <div class="detalle-title-container">
            <h5 class="detalle-title">{{ informeSeleccionado.nombre || 'Informe Final de Pasantía' }}</h5>
            <span class="detalle-badge">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" style="margin-right: 4px;">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
              </svg>
              {{ informeSeleccionado.horas || 0 }} Horas Reportadas
            </span>
          </div>

          <div class="detalle-seccion" v-if="informeSeleccionado.objetivos">
            <div class="detalle-seccion-title">Objetivos</div>
            <div class="detalle-seccion-content">{{ informeSeleccionado.objetivos }}</div>
          </div>

          <div class="detalle-seccion" v-if="informeSeleccionado.actividades">
            <div class="detalle-seccion-title">Actividades Realizadas</div>
            <div class="detalle-seccion-content">{{ informeSeleccionado.actividades }}</div>
          </div>

          <div class="detalle-seccion" v-if="informeSeleccionado.conclusiones">
            <div class="detalle-seccion-title">Conclusiones</div>
            <div class="detalle-seccion-content">{{ informeSeleccionado.conclusiones }}</div>
          </div>

          <div class="detalle-seccion" v-if="informeSeleccionado.archivo_url">
            <div class="detalle-seccion-title d-flex justify-content-between align-items-center flex-wrap gap-2">
              <span>Documento Oficial Autogenerado (PDF)</span>
              <a :href="informeSeleccionado.archivo_url" target="_blank" class="btn-open-pdf-tab">
                <i class="bi bi-box-arrow-up-right me-1"></i> Abrir en pestaña nueva
              </a>
            </div>
            <div class="pdf-preview-container" style="height: 480px;">
              <iframe :src="informeSeleccionado.archivo_url" width="100%" height="100%" style="border: none;"></iframe>
            </div>
          </div>
        </div>

        <!-- Veredicto -->
        <div class="field-group">
          <label>Veredicto</label>
          <div class="radio-group">
            <label class="radio-option" :class="{ active: evaluacion.veredicto === 'aprobado' }">
              <input type="radio" v-model="evaluacion.veredicto" value="aprobado">
              <span class="radio-dot"></span>
              ✅ Aprobar — Generar Carta de Finalización
            </label>
            <label class="radio-option" :class="{ active: evaluacion.veredicto === 'rechazado' }">
              <input type="radio" v-model="evaluacion.veredicto" value="rechazado">
              <span class="radio-dot"></span>
              🔄 Solicitar correcciones
            </label>
          </div>
        </div>

        <!-- Observaciones (solo si rechazado) -->
        <div class="field-group" v-if="evaluacion.veredicto === 'rechazado'">
          <label>Observaciones requeridas</label>
          <textarea v-model="evaluacion.observaciones" rows="4"
            placeholder="Especifique qué debe corregir el estudiante en el informe..." required></textarea>
        </div>

        <!-- Botón submit -->
        <button type="submit" class="btn-submit" :disabled="cargando || !evaluacion.informe_id">
          <span v-if="cargando" class="spinner"></span>
          <span v-else>Guardar Evaluación</span>
        </button>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  cargando: { type: Boolean, default: false }
});

const emit = defineEmits(['evaluar']);

const evaluacion = ref({ informe_id: '', veredicto: 'aprobado', observaciones: '' });
const informesDisponibles = ref([]);
const cargandoInformes = ref(true);

const informeSeleccionado = computed(() => {
  return informesDisponibles.value.find(inf => inf.id === evaluacion.value.informe_id) || null;
});

onMounted(async () => {
  await cargarInformes();
});

const cargarInformes = async () => {
  cargandoInformes.value = true;
  try {
    const res = await axios.get('/api/vicedecano/informes/finales');
    informesDisponibles.value = res.data.informes || [];
  } catch (err) {
    console.error('Error al cargar informes finales:', err);
    informesDisponibles.value = [];
  } finally {
    cargandoInformes.value = false;
  }
};

const submitEvaluacion = () => {
  if (!evaluacion.value.informe_id) return;
  emit('evaluar', { ...evaluacion.value });
  evaluacion.value = { informe_id: '', veredicto: 'aprobado', observaciones: '' };
};
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.eval-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  margin-bottom: 24px;
  overflow: hidden;
}
.eval-header {
  display: flex; align-items: center; gap: 10px;
  padding: 16px 24px;
  border-bottom: 1px solid var(--border);
  font-weight: 600; font-size: 0.95rem;
  background: var(--bg);
  color: var(--accent);
}
.eval-body { padding: 24px; }

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: var(--sub);
}
.empty-state p { margin: 12px 0 4px; font-size: 0.95rem; }
.empty-state small { font-size: 0.82rem; }

/* Fields */
.field-group { margin-bottom: 20px; }
.field-group label {
  display: block; font-size: 0.82rem;
  font-weight: 600; color: var(--sub);
  margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;
}
.select-wrapper { position: relative; }
.select-wrapper select, textarea {
  width: 100%; padding: 10px 14px;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 8px;
  color: var(--text);
  font-size: 0.9rem;
  transition: border-color 0.2s;
  appearance: none;
}
.select-wrapper select:focus, textarea:focus {
  outline: none; border-color: var(--accent);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 15%, transparent);
}
.select-arrow {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  width: 16px; pointer-events: none; color: var(--sub);
}
textarea { resize: vertical; min-height: 100px; }

/* Radio Options */
.radio-group { display: flex; flex-direction: column; gap: 10px; }
.radio-option {
  display: flex; align-items: center; gap: 10px;
  padding: 12px 16px;
  border: 1.5px solid var(--border);
  border-radius: 8px; cursor: pointer;
  font-size: 0.9rem; transition: all 0.2s;
}
.radio-option input { display: none; }
.radio-dot {
  width: 16px; height: 16px; border-radius: 50%;
  border: 2px solid var(--border); flex-shrink: 0;
  transition: all 0.2s;
}
.radio-option.active {
  border-color: var(--accent);
  background: color-mix(in srgb, var(--accent) 8%, transparent);
}
.radio-option.active .radio-dot {
  border-color: var(--accent);
  background: var(--accent);
}

/* Submit Button */
.btn-submit {
  width: 100%; padding: 12px;
  background: var(--accent);
  color: #fff; border: none;
  border-radius: 8px; font-size: 0.95rem;
  font-weight: 600; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: opacity 0.2s, transform 0.1s;
  margin-top: 8px;
}
.btn-submit:hover:not(:disabled) { opacity: 0.9; transform: translateY(-1px); }
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
.spinner {
  width: 18px; height: 18px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Informe Detalle Box */
.informe-detalle-box {
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 24px;
  margin-top: 16px;
  margin-bottom: 24px;
}
.detalle-title-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 2px solid var(--border);
  padding-bottom: 12px;
  margin-bottom: 16px;
  flex-wrap: wrap;
  gap: 12px;
}
.detalle-title {
  font-family: 'Lora', serif;
  font-size: 1.15rem;
  font-weight: 600;
  color: var(--accent);
  margin: 0;
}
.detalle-badge {
  background: var(--accent);
  color: white;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  display: flex;
  align-items: center;
}
.detalle-seccion {
  margin-bottom: 16px;
}
.detalle-seccion-title {
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--sub);
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.detalle-seccion-content {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 12px 16px;
  font-size: 0.9rem;
  color: var(--text);
  line-height: 1.5;
  white-space: pre-line;
}
.pdf-preview-container {
  border: 1px solid var(--border);
  border-radius: 8px;
  overflow: hidden;
  background: white;
}
.btn-open-pdf-tab {
  font-size: 0.82rem;
  color: var(--accent);
  text-decoration: underline;
  cursor: pointer;
}
.btn-open-pdf-tab:hover {
  opacity: 0.85;
}
</style>
