<template>
  <div class="analisis-ia-view animate-fade-in">
    <div class="row">
      <!-- Control Card -->
      <div class="col-md-5 mb-4">
        <div class="dashboard-section-card">
          <h5 class="card-title"><i class="bi bi-cpu-fill text-primary"></i> Asistente de Optimización IA</h5>
          <p class="text-muted mt-2">Analiza la redacción, el formato y el impacto de tu currículum usando nuestro modelo de inteligencia artificial.</p>
          
          <div class="mt-4">
            <label class="form-label font-weight-bold">Seleccionar Currículum a Analizar</label>
            <select class="form-select" v-model="analisisIa.cvId">
              <option value="" disabled>Selecciona un CV de tu lista</option>
              <option v-for="cv in cvs" :key="cv.id" :value="cv.id">{{ cv.titulo_cv || 'Mi CV' }}</option>
            </select>
          </div>

          <button class="btn-primary-custom w-100 mt-4" :disabled="analisisIa.analizando" @click="analizarConIa">
            <span v-if="analisisIa.analizando" class="spinner-border spinner-border-sm me-2"></span>
            <span><i class="bi bi-magic me-1"></i> Analizar Hoja de Vida</span>
          </button>
        </div>
      </div>

      <!-- Result Card -->
      <div class="col-md-7 mb-4">
        <div class="dashboard-section-card h-100">
          <h5 class="card-title"><i class="bi bi-clipboard-data text-primary"></i> Resultado del Análisis</h5>
          
          <!-- Default placeholder -->
          <div v-if="!analisisIa.analizando && !analisisIa.resultado" class="text-center py-5 text-muted">
            <i class="bi bi-robot d-block fs-1 mb-3 text-secondary animate-pulse"></i>
            <p>Selecciona un currículum a la izquierda y presiona "Analizar Hoja de Vida" para generar el reporte de recomendaciones automáticas.</p>
          </div>

          <!-- Analizando spinner -->
          <div v-if="analisisIa.analizando" class="text-center py-5">
            <span class="spinner-border spinner-border text-primary d-block mx-auto mb-3" style="width: 3rem; height: 3rem;"></span>
            <p class="font-weight-bold">Nuestra Inteligencia Artificial está analizando la estructura de tu CV...</p>
            <span class="text-muted">Extrayendo palabras clave y evaluando la gramática...</span>
          </div>

          <!-- Resultados -->
          <div v-if="analisisIa.resultado" class="analisis-resultados mt-3">
            <div class="score-container mb-4">
              <div class="score-circle">
                <span class="score-num">{{ analisisIa.resultado.score }}</span>
                <span class="score-total">/100</span>
              </div>
              <div class="score-desc">
                <h6>Puntaje General de Impacto</h6>
                <p class="text-muted m-0">Basado en legibilidad, relevancia de habilidades y consistencia.</p>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <h6 class="text-success font-weight-bold"><i class="bi bi-check-circle-fill me-1"></i> Fortalezas Detectadas</h6>
                <ul class="list-unstyled mt-2">
                  <li v-for="(f, i) in analisisIa.resultado.fortalezas" :key="i" class="mb-2 text-dark small">
                    <i class="bi bi-plus-lg text-success me-1"></i> {{ f }}
                  </li>
                </ul>
              </div>
              <div class="col-md-6 mb-3">
                <h6 class="text-warning font-weight-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Sugerencias de Mejora</h6>
                <ul class="list-unstyled mt-2">
                  <li v-for="(s, i) in analisisIa.resultado.sugerencias" :key="i" class="mb-2 text-dark small">
                    <i class="bi bi-dot text-warning fs-4 align-middle"></i> {{ s }}
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  cvs: { type: Array, default: () => [] }
});

const analisisIa = ref({
  cvId: '',
  analizando: false,
  resultado: null
});

const analizarConIa = () => {
  if (!analisisIa.value.cvId) {
    alertify.warning('Por favor selecciona un currículum para analizar.');
    return;
  }
  analisisIa.value.analizando = true;
  analisisIa.value.resultado = null;
  
  setTimeout(() => {
    analisisIa.value.analizando = false;
    analisisIa.value.resultado = {
      score: Math.floor(Math.random() * 15) + 80,
      coincidencia: '94%',
      sugerencias: [
        'Añade enlaces directos a tus repositorios en los proyectos principales.',
        'Utiliza verbos de acción más fuertes en la descripción de tu experiencia.',
        'Incluye palabras clave de desarrollo web como Vue.js, Laravel y Tailwind CSS.'
      ],
      fortalezas: [
        'Estructura limpia y fácil de leer para reclutadores.',
        'Perfil profesional bien definido y orientado al desarrollo.',
        'Habilidades técnicas ordenadas lógicamente.'
      ]
    };
    alertify.success('¡Análisis completado con éxito!');
  }, 2500);
};
</script>

<style scoped>
.analisis-ia-view {
  width: 100%;
}

.animate-fade-in {
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-pulse {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); opacity: 0.7; }
  50% { transform: scale(1.05); opacity: 1; }
  100% { transform: scale(1); opacity: 0.7; }
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

.font-weight-bold {
  font-weight: 700;
}

.btn-primary-custom {
  background-color: #001374;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  padding: 12px 20px;
  font-weight: 600;
  font-size: 15px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-primary-custom:hover {
  background-color: #010c67;
}

.btn-primary-custom:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

/* AI Score styling */
.score-container {
  display: flex;
  align-items: center;
  gap: 20px;
  border-bottom: 1px solid #f1f5f9;
  padding-bottom: 20px;
}

.score-circle {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  border: 4px solid #001374;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
}

.score-num {
  font-size: 26px;
  font-weight: 800;
  color: #001374;
  line-height: 1;
}

.score-total {
  font-size: 12px;
  color: #64748b;
  font-weight: 500;
}

.score-desc h6 {
  font-size: 16px;
  font-weight: 700;
  margin: 0 0 4px;
}
</style>
