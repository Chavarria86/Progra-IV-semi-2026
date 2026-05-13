<template>
  <div>
    <h2>Panel del Supervisor</h2>
    <hr>
    
    <div class="row">
      <!-- Formulario de Validación de CV -->
      <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-check2-square"></i> Validar CV de Pasante</h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="validarCV">
              <div class="mb-3">
                <label class="form-label">Seleccionar Pasante</label>
                <select class="form-select" v-model="cvForm.pasante_id" required>
                  <option value="" disabled>Seleccione un pasante pendiente</option>
                  <option value="1">Juan Pérez (Enviado hace 2 días)</option>
                  <option value="2">María Gómez (Enviado hace 5 horas)</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Estado de Validación</label>
                <select class="form-select" v-model="cvForm.estado" required>
                  <option value="aprobado">Aprobado - Avanza a Fase 2</option>
                  <option value="rechazado">Rechazado - Necesita correcciones</option>
                </select>
              </div>
              <div class="mb-3" v-if="cvForm.estado === 'rechazado'">
                <label class="form-label">Comentarios / Correcciones</label>
                <textarea class="form-control" rows="3" v-model="cvForm.comentarios" required></textarea>
              </div>
              <button type="submit" class="btn btn-warning w-100 text-dark font-weight-bold" :disabled="cargando">
                Procesar Validación
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Formulario de Asignación de Vacante -->
      <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-building"></i> Asignar Vacante</h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="asignarVacante">
              <div class="mb-3">
                <label class="form-label">Seleccionar Pasante (Fase 2)</label>
                <select class="form-select" v-model="asignacion.pasante_id" required>
                  <option value="" disabled>Seleccione un pasante elegible</option>
                  <option value="3">Carlos López (Aprobado F1)</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Seleccionar Empresa / Vacante</label>
                <select class="form-select" v-model="asignacion.vacante_id" required>
                  <option value="" disabled>Seleccione la empresa destino</option>
                  <option value="101">TechCorp - Desarrollador Frontend</option>
                  <option value="102">DesignStudio - UI/UX</option>
                </select>
              </div>
              <button type="submit" class="btn btn-primary w-100" :disabled="cargando">
                Confirmar Asignación
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const cargando = ref(false);
const cvForm = ref({ pasante_id: '', estado: 'aprobado', comentarios: '' });
const asignacion = ref({ pasante_id: '', vacante_id: '' });

const validarCV = async () => {
  cargando.value = true;
  // Llamada al backend
  setTimeout(() => {
    cargando.value = false;
    alertify.success('El CV ha sido procesado exitosamente.');
    cvForm.value = { pasante_id: '', estado: 'aprobado', comentarios: '' };
  }, 1000);
};

const asignarVacante = async () => {
  cargando.value = true;
  // Llamada al backend
  setTimeout(() => {
    cargando.value = false;
    alertify.success('El pasante ha sido asignado a la vacante correctamente.');
    asignacion.value = { pasante_id: '', vacante_id: '' };
  }, 1000);
};
</script>
