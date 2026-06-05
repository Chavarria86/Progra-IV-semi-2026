<template>
  <div class="solicitudes-view animate-fade-in">
    <div class="dashboard-section-card">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 border-bottom pb-3 header-border">
        <h5 class="card-title m-0"><i class="bi bi-person-plus-fill text-primary-custom"></i> Postulaciones a Vacantes</h5>
      </div>
      
      <p class="text-muted small mb-4">Revisa las postulaciones de tus pasantes a diferentes vacantes. Debes aprobarlas para que lleguen al Vicedecano.</p>

      <div v-if="cargandoPostulaciones" class="text-center py-5">
        <span class="spinner-border text-primary-custom" style="width: 2.5rem; height: 2.5rem;"></span>
      </div>
      
      <div v-else-if="postulacionesPendientes.length === 0" class="text-center py-5 empty-box rounded border text-muted">
        <i class="bi bi-inbox d-block fs-1 mb-3 text-secondary-custom"></i>
        <span class="fw-semibold">No tienes postulaciones pendientes de revisión.</span>
      </div>
      
      <div v-else class="solicitudes-grid">
        <div v-for="post in postulacionesPendientes" :key="post.id" class="solicitud-card border rounded p-4 mb-3">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
              <h6 class="fw-bold mb-2 text-dark text-main-custom">
                <i class="bi bi-person-circle me-2 text-primary-custom"></i>{{ post.pasante }}
              </h6>
              <p class="mb-0 text-primary-custom fw-semibold font-serif-custom">
                <i class="bi bi-building me-2"></i>{{ post.vacante }}
              </p>
            </div>
            
            <a 
              v-if="post.cv_url" 
              :href="post.cv_url" 
              target="_blank" 
              class="btn btn-sm btn-outline-pdf-custom"
            >
              <i class="bi bi-file-earmark-pdf-fill me-1"></i>Ver CV
            </a>
            <span v-else class="text-muted small italic">Sin CV Adjunto</span>
          </div>

          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 pt-3 border-top card-footer-border">
            <small class="text-muted"><i class="bi bi-calendar3 me-1"></i> Postulado: {{ post.fecha }}</small>
            
            <div v-if="usuario.rol === 'vice_decano'" class="badge bg-info text-dark px-3 py-2 small">
              <i class="bi bi-info-circle-fill me-1"></i> Modo Vista
            </div>
            
            <div v-else class="d-flex gap-2">
              <button 
                class="btn btn-sm btn-outline-danger" 
                @click="responderPostulacion(post, 'rechazar')" 
                :disabled="procesando === post.id"
              >
                <i class="bi bi-x-circle me-1"></i> Rechazar
              </button>
              <button 
                class="btn btn-sm btn-success-custom" 
                @click="responderPostulacion(post, 'aceptar')" 
                :disabled="procesando === post.id"
              >
                <span v-if="procesando === post.id" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-check-circle me-1"></i> Aprobar para Vicedecano
              </button>
            </div>
          </div>
        </div>
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

const procesando = ref(null);
const cargandoPostulaciones = ref(true);
const postulacionesPendientes = ref([]);

onMounted(() => {
  cargarPostulaciones();
});

const cargarPostulaciones = async () => {
  cargandoPostulaciones.value = true;
  try {
    const res = await axios.get('/api/supervisor/postulaciones', {
      headers: { 'X-User-Id': props.usuario?.id || 2 }
    });
    postulacionesPendientes.value = (res.data.postulaciones || []).filter(p => p.estado === 'pendiente');
  } catch (err) {
    console.error('Error al cargar postulaciones:', err);
    if (window.alertify) {
      alertify.error('Error al cargar las postulaciones.');
    }
  } finally {
    cargandoPostulaciones.value = false;
  }
};

const responderPostulacion = async (post, decision) => {
  procesando.value = post.id;
  try {
    await axios.put(`/api/supervisor/postulaciones/${post.id}/responder`, { decision }, {
      headers: { 'X-User-Id': props.usuario?.id || 2 }
    });
    postulacionesPendientes.value = postulacionesPendientes.value.filter(p => p.id !== post.id);
    if (decision === 'aceptar') {
      if (window.alertify) {
        alertify.success('Postulación aprobada y enviada al Vicedecano.');
      }
    } else {
      if (window.alertify) {
        alertify.warning('Postulación rechazada.');
      }
    }
  } catch (err) {
    console.error('Error al responder postulación:', err);
    if (window.alertify) {
      alertify.error('Error al responder la postulación.');
    }
  } finally {
    procesando.value = null;
  }
};
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.solicitudes-view {
  font-family: 'Inter', sans-serif;
}

.dashboard-section-card {
  background: #fff;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 4px 18px rgba(0,0,0,0.03);
  border: 1px solid #e2e8f0;
}

.card-title {
  font-family: 'Lora', serif;
  font-size: 20px;
  font-weight: 600;
  color: #0f172a;
}

.text-primary-custom {
  color: #001374 !important;
}
.text-secondary-custom {
  color: #64748b;
}

/* Solicitud Card */
.solicitud-card {
  background: #fafcff;
  border-color: #e2e8f0 !important;
  transition: transform 0.2s, box-shadow 0.2s;
}
.solicitud-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

/* PDF Button styling */
.btn-outline-pdf-custom {
  border: 1px solid #dc3545;
  color: #dc3545;
  font-size: 0.8rem;
  font-weight: 600;
  border-radius: 6px;
  padding: 5px 12px;
  background-color: transparent;
  transition: background 0.2s, color 0.2s;
}
.btn-outline-pdf-custom:hover {
  background-color: #dc3545;
  color: #FFFFFF !important;
}

.btn-success-custom {
  background-color: #198754;
  color: #FFFFFF;
  border: none;
  font-weight: 600;
  border-radius: 6px;
  padding: 6px 14px;
  transition: opacity 0.2s, transform 0.1s;
}
.btn-success-custom:hover {
  opacity: 0.9;
}
.btn-success-custom:active {
  transform: scale(0.97);
}

/* Dark Mode Overrides */
:deep(.dark-mode) .dashboard-section-card {
  background: #15151F !important;
  border-color: #232332 !important;
}
:deep(.dark-mode) .card-title {
  color: #FFFFFF !important;
}
:deep(.dark-mode) .header-border {
  border-color: #232332 !important;
}
:deep(.dark-mode) .text-primary-custom {
  color: #FF750F !important;
}
:deep(.dark-mode) .text-main-custom {
  color: #FFFFFF !important;
}
:deep(.dark-mode) .font-serif-custom {
  color: #FFB75B !important;
}
:deep(.dark-mode) .solicitud-card {
  background: #1C1C28 !important;
  border-color: #2D2D3E !important;
}
:deep(.dark-mode) .card-footer-border {
  border-color: #2D2D3E !important;
}
:deep(.dark-mode) .empty-box {
  background: #1C1C28 !important;
  border-color: #2D2D3E !important;
  color: #A1A09A !important;
}
</style>
