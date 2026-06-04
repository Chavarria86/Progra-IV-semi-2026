<template>
  <div class="recomendaciones-view animate-fade-in">

    <!-- VISTA: Lista de Recomendaciones -->
    <div v-if="!modoEscritura">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <h4 class="fw-bold m-0"><i class="bi bi-award-fill text-primary"></i> Recomendaciones para Pasantes</h4>
        <button v-if="usuario.rol !== 'vice_decano'" class="btn btn-accent px-4" @click="modoEscritura = true; limpiarForm()">
          <i class="bi bi-plus-circle me-2"></i> Nueva Recomendación
        </button>
      </div>

      <div class="dashboard-section-card">
        <p class="text-muted small mb-4">Redacta y emite cartas de recomendación personalizadas para los pasantes bajo tu supervisión. Estas quedarán registradas en el sistema y podrán ser consultadas por el Vicedecano.</p>

        <div v-if="cargando" class="text-center py-4">
          <span class="spinner-border text-primary"></span>
          <p class="text-muted mt-2">Cargando historial...</p>
        </div>

        <div v-else-if="recomendaciones.length === 0" class="text-center py-5 bg-light rounded border text-muted">
          <i class="bi bi-award d-block fs-1 mb-3 text-secondary"></i>
          <span v-if="usuario.rol === 'vice_decano'">No se encontraron recomendaciones registradas.</span>
          <span v-else>No has emitido recomendaciones todavía. Haz clic en "Nueva Recomendación" para empezar.</span>
        </div>

        <div v-else class="recs-grid">
          <div v-for="rec in recomendaciones" :key="rec.id" class="rec-card border rounded p-4 mb-3">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
              <div>
                <h6 class="fw-bold text-dark mb-1"><i class="bi bi-person-circle text-primary me-2"></i>{{ rec.pasante }}</h6>
                <p class="text-muted small mb-2">{{ rec.titulo }}</p>
                <span :class="['badge', rec.tipo === 'destacado' ? 'bg-success' : rec.tipo === 'satisfactorio' ? 'bg-primary' : 'bg-warning text-dark']">
                  {{ { 'destacado': '⭐ Desempeño Destacado', 'satisfactorio': '✅ Satisfactorio', 'mejora': '📈 Área de Mejora' }[rec.tipo] }}
                </span>
              </div>
              <small class="text-muted">{{ rec.fecha }}</small>
            </div>
            <p class="small text-muted mt-3 mb-0 fst-italic border-top pt-3">"{{ rec.contenido.substring(0, 200) }}..."</p>
          </div>
        </div>
      </div>
    </div>

    <!-- VISTA: Formulario de Escritura -->
    <div v-else class="animate-fade-in">
      <div class="dashboard-section-card max-w-800 mx-auto">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 border-bottom pb-3">
          <h4 class="fw-bold m-0"><i class="bi bi-pencil-square text-accent"></i> Redactar Recomendación</h4>
          <div class="d-flex gap-2">
            <button class="btn-ds-back-outline" @click="modoEscritura = false"><i class="bi bi-arrow-left me-1"></i> Atrás</button>
            <button class="btn btn-outline-danger" @click="modoEscritura = false"><i class="bi bi-x-circle me-1"></i> Cerrar</button>
          </div>
        </div>

        <form @submit.prevent="guardarRecomendacion">
          <div class="row">
            <div class="col-md-6 mb-4">
              <label class="form-label fw-bold">Pasante a Recomendar</label>
              <select class="form-select form-select-lg" v-model="form.pasanteId" required>
                <option value="" disabled>Selecciona un pasante...</option>
                <option v-for="p in pasantes" :key="p.id" :value="p.id">
                  {{ p.nombre }} {{ p.apellido }} — {{ p.area }}
                </option>
              </select>
            </div>
            <div class="col-md-6 mb-4">
              <label class="form-label fw-bold">Tipo de Evaluación</label>
              <select class="form-select form-select-lg" v-model="form.tipo" required>
                <option value="destacado">⭐ Desempeño Destacado</option>
                <option value="satisfactorio">✅ Desempeño Satisfactorio</option>
                <option value="mejora">📈 Área de Mejora</option>
              </select>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-bold">Título de la Recomendación</label>
            <input type="text" class="form-control form-control-lg" v-model="form.titulo" placeholder="Ej: Carta de Recomendación — Período Enero-Mayo 2026" required>
          </div>

          <div class="mb-4">
            <label class="form-label fw-bold">Contenido de la Recomendación</label>
            <p class="small text-muted mb-2">Detalla las habilidades, actitudes y logros del pasante durante el período de supervisión.</p>
            <textarea class="form-control" rows="8" v-model="form.contenido" placeholder="Por medio de la presente, me permito recomendar ampliamente al estudiante... Durante el período de prácticas profesionales, demostró..." required></textarea>
          </div>

          <div class="border-top pt-4 d-flex justify-content-end">
            <button type="submit" class="btn btn-accent px-5 py-2 fs-5" :disabled="guardando">
              <span v-if="guardando" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="bi bi-save2 me-2"></i>
              {{ guardando ? 'Guardando...' : 'Guardar Recomendación' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  usuario: { type: Object, default: () => ({}) }
});

const modoEscritura = ref(false);
const guardando = ref(false);
const cargando = ref(true);

const form = ref({ pasanteId: '', tipo: 'satisfactorio', titulo: '', contenido: '' });
const limpiarForm = () => {
  form.value = { pasanteId: '', tipo: 'satisfactorio', titulo: '', contenido: '' };
};

const pasantes = ref([]);
const recomendaciones = ref([]);

onMounted(() => {
  cargarRecomendaciones();
  cargarPasantes();
});

const cargarRecomendaciones = async () => {
  cargando.value = true;
  try {
    const res = await axios.get('/api/supervisor/recomendaciones', {
      headers: { 'X-User-Id': props.usuario?.id || 2 }
    });
    recomendaciones.value = res.data.recomendaciones || [];
  } catch (err) {
    console.error('Error cargando recomendaciones', err);
  } finally {
    cargando.value = false;
  }
};

const cargarPasantes = async () => {
  try {
    const res = await axios.get('/api/supervisor/pasantes', {
      headers: { 'X-User-Id': props.usuario?.id || 2 }
    });
    pasantes.value = res.data.pasantes.map(p => ({
      id: p.pasante_id,
      nombre: p.nombre,
      apellido: p.apellido,
      area: p.area
    }));
  } catch (err) {
    console.error('Error cargando pasantes', err);
  }
};

const guardarRecomendacion = async () => {
  guardando.value = true;
  try {
    await axios.post('/api/supervisor/recomendaciones', {
      pasante_id: form.value.pasanteId,
      tipo: form.value.tipo,
      titulo: form.value.titulo,
      contenido: form.value.contenido
    }, {
      headers: { 'X-User-Id': props.usuario?.id || 2 }
    });
    alertify.success('Recomendación guardada y registrada en el sistema.');
    await cargarRecomendaciones();
    modoEscritura.value = false;
  } catch (err) {
    alertify.error('Error al guardar la recomendación.');
  } finally {
    guardando.value = false;
  }
};
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.dashboard-section-card {
  background: #fff; border-radius: 12px; padding: 30px;
  box-shadow: 0 4px 18px rgba(0,0,0,0.03); border: 1px solid #e2e8f0;
}
.max-w-800 { max-width: 800px; }
.card-title { font-family: 'Lora', serif; font-size: 20px; font-weight: 600; }
.text-primary { color: #001374 !important; }
.text-accent { color: var(--accent, #67000F) !important; }

.btn-accent {
  background-color: var(--accent, #67000F); color: white; font-weight: 600;
  border: none; transition: all 0.2s;
}
.btn-accent:hover:not(:disabled) {
  background-color: color-mix(in srgb, var(--accent, #67000F) 85%, black);
  color: white; transform: translateY(-2px);
}

.rec-card { background: #fafcff; transition: transform 0.2s, box-shadow 0.2s; }
.rec-card:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }

.form-control:focus, .form-select:focus {
  border-color: var(--accent, #67000F);
  box-shadow: 0 0 0 0.25rem color-mix(in srgb, var(--accent, #67000F) 25%, transparent);
}
textarea { resize: vertical; }
</style>
