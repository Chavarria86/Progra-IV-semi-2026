<template>
  <div class="solicitudes-view animate-fade-in">
    <div class="dashboard-section-card">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="card-title m-0"><i class="bi bi-person-plus-fill text-primary"></i> Gestión de Solicitudes</h5>
      </div>
      
      <!-- Main Tabs -->
      <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
          <a class="nav-link fw-bold" :class="{ active: mainTab === 'asignaciones' }" @click.prevent="mainTab = 'asignaciones'" href="#">Asignación de Pasantes</a>
        </li>
        <li class="nav-item">
          <a class="nav-link fw-bold" :class="{ active: mainTab === 'postulaciones' }" @click.prevent="mainTab = 'postulaciones'" href="#">Postulaciones a Vacantes</a>
        </li>
      </ul>

      <!-- ================= ASIGNACIONES ================= -->
      <div v-if="mainTab === 'asignaciones'">
        <p class="text-muted small mb-4">Aquí aparecen los pasantes que han solicitado ser asignados a tu supervisión. Puedes aceptar o rechazar cada solicitud.</p>

        <div v-if="cargandoSolicitudes" class="text-center py-4">
          <span class="spinner-border text-primary"></span>
        </div>
        <div v-else-if="solicitudesPendientes.length === 0" class="text-center py-5 bg-light rounded border text-muted">
          <i class="bi bi-inbox d-block fs-1 mb-3 text-secondary"></i>
          <span>No tienes solicitudes de asignación pendientes.</span>
        </div>
        <div v-else class="solicitudes-grid">
          <div v-for="sol in solicitudesPendientes" :key="sol.id" class="solicitud-card border rounded p-4 mb-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="d-flex align-items-center gap-3">
                <div class="pasante-avatar">{{ sol.nombre.charAt(0) }}{{ sol.apellido.charAt(0) }}</div>
                <div>
                  <h6 class="fw-bold mb-0 text-dark">{{ sol.nombre }} {{ sol.apellido }}</h6>
                  <small class="text-muted"><i class="bi bi-envelope me-1"></i>{{ sol.correo }}</small>
                </div>
              </div>
            </div>

            <div class="solicitud-mensaje p-3 bg-light rounded border-start border-primary border-3 mb-3">
              <p class="small text-muted mb-0 fst-italic">"{{ sol.mensaje }}"</p>
            </div>

            <div class="d-flex justify-content-between align-items-center">
              <small class="text-muted"><i class="bi bi-calendar3 me-1"></i> Enviado: {{ sol.fecha }}</small>
              <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-danger" @click="responderAsignacion(sol, 'rechazar')" :disabled="procesando === sol.id">
                  <i class="bi bi-x-circle me-1"></i> Rechazar
                </button>
                <button class="btn btn-sm btn-success" @click="responderAsignacion(sol, 'aceptar')" :disabled="procesando === sol.id">
                  <span v-if="procesando === sol.id" class="spinner-border spinner-border-sm me-1"></span>
                  <i v-else class="bi bi-check-circle me-1"></i> Aceptar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ================= POSTULACIONES ================= -->
      <div v-if="mainTab === 'postulaciones'">
        <p class="text-muted small mb-4">Revisa las postulaciones de tus pasantes a diferentes vacantes. Debes aprobarlas para que lleguen al Vicedecano.</p>

        <div v-if="cargandoPostulaciones" class="text-center py-4">
          <span class="spinner-border text-primary"></span>
        </div>
        <div v-else-if="postulacionesPendientes.length === 0" class="text-center py-5 bg-light rounded border text-muted">
          <i class="bi bi-inbox d-block fs-1 mb-3 text-secondary"></i>
          <span>No tienes postulaciones pendientes de revisión.</span>
        </div>
        <div v-else class="solicitudes-grid">
          <div v-for="post in postulacionesPendientes" :key="post.id" class="solicitud-card border rounded p-4 mb-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h6 class="fw-bold mb-1 text-dark"><i class="bi bi-person-circle me-2"></i>{{ post.pasante }}</h6>
                <p class="mb-0 text-primary fw-semibold"><i class="bi bi-building me-2"></i>{{ post.vacante }}</p>
              </div>
              <a v-if="post.cv_url" :href="post.cv_url" target="_blank" class="btn btn-sm btn-outline-primary">Ver CV</a>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
              <small class="text-muted"><i class="bi bi-calendar3 me-1"></i> Postulado: {{ post.fecha }}</small>
              <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-danger" @click="responderPostulacion(post, 'rechazar')" :disabled="procesando === post.id">
                  <i class="bi bi-x-circle me-1"></i> Rechazar
                </button>
                <button class="btn btn-sm btn-success" @click="responderPostulacion(post, 'aceptar')" :disabled="procesando === post.id">
                  <span v-if="procesando === post.id" class="spinner-border spinner-border-sm me-1"></span>
                  <i v-else class="bi bi-check-circle me-1"></i> Aprobar para Vicedecano
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  usuario: { type: Object, default: () => ({}) }
});

const mainTab = ref('asignaciones');
const procesando = ref(null);
const cargandoSolicitudes = ref(true);
const cargandoPostulaciones = ref(true);

const solicitudesPendientes = ref([]);
const postulacionesPendientes = ref([]);

onMounted(() => {
  cargarSolicitudes();
  cargarPostulaciones();
});

const cargarSolicitudes = async () => {
  cargandoSolicitudes.value = true;
  try {
    const res = await axios.get('/api/supervisor/solicitudes', {
      headers: { 'X-User-Id': props.usuario?.id || 2 }
    });
    solicitudesPendientes.value = (res.data.solicitudes || []).filter(s => s.estado === 'pendiente');
  } catch (err) {
    console.error('Error al cargar solicitudes:', err);
  } finally {
    cargandoSolicitudes.value = false;
  }
};

const cargarPostulaciones = async () => {
  cargandoPostulaciones.value = true;
  try {
    const res = await axios.get('/api/supervisor/postulaciones', {
      headers: { 'X-User-Id': props.usuario?.id || 2 }
    });
    postulacionesPendientes.value = (res.data.postulaciones || []).filter(p => p.estado === 'pendiente');
  } catch (err) {
    console.error('Error al cargar postulaciones:', err);
  } finally {
    cargandoPostulaciones.value = false;
  }
};

const responderAsignacion = async (sol, decision) => {
  procesando.value = sol.id;
  try {
    await axios.put(`/api/supervisor/solicitudes/${sol.id}/responder`, { decision }, {
      headers: { 'X-User-Id': props.usuario?.id || 2 }
    });
    solicitudesPendientes.value = solicitudesPendientes.value.filter(s => s.id !== sol.id);
    if (decision === 'aceptar') alertify.success('Solicitud aceptada.');
    else alertify.warning('Solicitud rechazada.');
  } catch (err) {
    alertify.error('Error al procesar solicitud.');
  } finally {
    procesando.value = null;
  }
};

const responderPostulacion = async (post, decision) => {
  procesando.value = post.id;
  try {
    await axios.put(`/api/supervisor/postulaciones/${post.id}/responder`, { decision }, {
      headers: { 'X-User-Id': props.usuario?.id || 2 }
    });
    postulacionesPendientes.value = postulacionesPendientes.value.filter(p => p.id !== post.id);
    if (decision === 'aceptar') alertify.success('Postulación aprobada y enviada a Vicedecano.');
    else alertify.warning('Postulación rechazada.');
  } catch (err) {
    alertify.error('Error al procesar postulación.');
  } finally {
    procesando.value = null;
  }
};
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.dashboard-section-card {
  background: #fff;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 4px 18px rgba(0,0,0,0.03);
  border: 1px solid #e2e8f0;
}
.card-title { font-family: 'Lora', serif; font-size: 20px; font-weight: 600; color: #0f172a; }
.text-primary { color: #001374 !important; }
.bg-primary-soft { background-color: #eff6ff; }
.text-primary { color: #001374 !important; }

/* Tabs */
.tab-buttons { display: flex; gap: 10px; }
.tab-btn {
  background: #f1f5f9; border: none; border-radius: 8px;
  padding: 10px 20px; font-weight: 600; color: #64748b; transition: all 0.2s; cursor: pointer;
}
.tab-btn:hover { background: #e2e8f0; }
.tab-btn.active { background: #001374; color: white; }

/* Solicitud Card */
.solicitud-card { background: #fafcff; transition: transform 0.2s, box-shadow 0.2s; }
.solicitud-card:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }

.pasante-avatar {
  width: 48px; height: 48px; border-radius: 50%;
  background: linear-gradient(135deg, #001374, #1d4ed8);
  color: white; font-weight: 700; font-size: 16px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}

.area-chip-sm {
  display: inline-block; font-size: 11px; font-weight: 700;
  padding: 3px 10px; border-radius: 20px;
  background: #f0fdf4; color: #166534; text-transform: capitalize;
}

.custom-table th {
  font-size: 0.85rem; font-weight: 600; text-transform: uppercase;
  letter-spacing: 0.5px; color: #64748b; padding: 14px 16px;
}
.custom-table td { padding: 14px 16px; }
</style>
