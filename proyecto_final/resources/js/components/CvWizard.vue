<template>
  <div class="cv-wizard-overlay">
    <div class="cv-wizard-wrapper">

      <!-- ===== HEADER ===== -->
      <div class="wiz-header">
        <span v-if="paso === 0">Bienvenido a Genesis profesional! completa tu perfil profesional</span>
        <span v-else-if="paso === 1">Paso1: Configuración del diseño</span>
        <span v-else-if="paso === 2">Paso 2: Información de Perfil</span>
        <span v-else-if="paso === 3">Paso 3: Objetivos y valores</span>
        <span v-else-if="paso === 4">Paso 4: Logros</span>
        <span v-else>Vista previa de tu CV</span>
      </div>

      <!-- ===== STEPPER (pasos 1-4) ===== -->
      <div class="wiz-stepper" v-if="paso >= 1 && paso <= 4">
        <div v-for="n in 4" :key="n" class="step-item">
          <div class="step-circle" :class="{ active: paso >= n }">{{ n }}</div>
          <div class="step-line" v-if="n < 4" :class="{ active: paso > n }"></div>
        </div>
      </div>

      <!-- ===== PASO 0: INTRO ===== -->
      <div class="wiz-body" v-if="paso === 0">
        <p class="intro-text">Inicia la creación de tu perfil profesional<br>siguiendo los 3 sencillos pasos</p>
        <div class="intro-img-wrap">
          <img src="https://images.unsplash.com/photo-1586281380349-632531db7ed4?w=600&q=80" alt="CV" class="intro-img" />
        </div>
      </div>

      <!-- ===== PASO 1: DISEÑO ===== -->
      <div class="wiz-body paso1-body" v-else-if="paso === 1">
        <div class="paso1-left">
          <p class="label-section">Color de la Plantilla</p>
          <div class="color-options">
            <div v-for="c in colores" :key="c.val"
              class="color-circle"
              :style="{ backgroundColor: c.val }"
              :class="{ selected: diseno.color === c.val }"
              @click="diseno.color = c.val">
            </div>
          </div>
          <p class="label-section" style="margin-top:28px">Tipo de letra</p>
          <select class="font-select" v-model="diseno.fuente">
            <option v-for="f in fuentes" :key="f" :value="f">{{ f }}</option>
          </select>
        </div>
        <div class="paso1-right">
          <div class="cv-mini-preview" :style="{ fontFamily: diseno.fuente, '--cv-color': diseno.color }">
            <div class="mini-header" :style="{ backgroundColor: diseno.color }">
              <div class="mini-avatar"></div>
              <div>
                <div class="mini-name">YOUR NAME</div>
                <div class="mini-job">JOB TITLE</div>
              </div>
            </div>
            <div class="mini-body">
              <div class="mini-col left-col">
                <div class="mini-section-title" :style="{ borderColor: diseno.color }">CONTACT</div>
                <div class="mini-line"></div><div class="mini-line short"></div><div class="mini-line"></div>
                <div class="mini-section-title mt" :style="{ borderColor: diseno.color }">ABOUT ME</div>
                <div class="mini-line"></div><div class="mini-line"></div><div class="mini-line short"></div>
                <div class="mini-section-title mt" :style="{ borderColor: diseno.color }">SKILLS</div>
                <div class="mini-skill"><div class="skill-bar" :style="{ backgroundColor: diseno.color, width: '70%' }"></div></div>
                <div class="mini-skill"><div class="skill-bar" :style="{ backgroundColor: diseno.color, width: '50%' }"></div></div>
              </div>
              <div class="mini-col right-col">
                <div class="mini-section-title" :style="{ borderColor: diseno.color }">EDUCATION</div>
                <div class="mini-line"></div><div class="mini-line short"></div>
                <div class="mini-section-title mt" :style="{ borderColor: diseno.color }">EXPERIENCE</div>
                <div class="mini-line"></div><div class="mini-line short"></div><div class="mini-line"></div>
                <div class="mini-section-title mt" :style="{ borderColor: diseno.color }">LANGUAGES</div>
                <div class="mini-line short"></div><div class="mini-line short"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== PASO 2: PERFIL ===== -->
      <div class="wiz-body paso2-body" v-else-if="paso === 2">
        <div class="paso2-left">
          <div class="foto-wrap" @click="$refs.fotoInput.click()">
            <img v-if="perfil.fotoUrl" :src="perfil.fotoUrl" class="foto-preview" alt="foto" />
            <div v-else class="foto-placeholder"><i class="bi bi-person-fill"></i></div>
            <div class="foto-camara"><i class="bi bi-camera-fill"></i></div>
          </div>
          <p class="foto-label">Subir foto</p>
          <input ref="fotoInput" type="file" accept="image/*" style="display:none" @change="cargarFoto" />

          <div class="field-group">
            <label><i class="bi bi-person"></i> Nombre completo:</label>
            <input class="wiz-input" v-model="perfil.nombre" placeholder="Tu nombre completo" />
          </div>
          <div class="field-group">
            <label><i class="bi bi-geo-alt"></i> Dirección:</label>
            <input class="wiz-input" v-model="perfil.direccion" placeholder="Ciudad, País" />
          </div>
          <div class="field-group">
            <label><i class="bi bi-envelope"></i> Email</label>
            <input class="wiz-input" v-model="perfil.email" placeholder="correo@ugb.edu.sv" />
          </div>
          <div class="field-group">
            <label><i class="bi bi-telephone"></i> Teléfono:</label>
            <input class="wiz-input" v-model="perfil.telefono" placeholder="7000-0000" />
          </div>
        </div>
        <div class="paso2-right">
          <div class="field-group">
            <label><i class="bi bi-chat-text"></i> Sobre mi:</label>
            <textarea class="wiz-textarea" v-model="perfil.sobreMi" placeholder="ej. Soy una persona apasionada por la tecnología..."></textarea>
          </div>
          <div class="field-group">
            <label><i class="bi bi-mortarboard"></i> Educación:</label>
            <textarea class="wiz-textarea" v-model="perfil.educacion" placeholder="Universidad, carrera, año..."></textarea>
          </div>
        </div>
      </div>

      <!-- ===== PASO 3: OBJETIVOS ===== -->
      <div class="wiz-body paso3-body" v-else-if="paso === 3">
        <div class="paso3-grid">
          <div class="field-group">
            <label><i class="bi bi-bullseye"></i> Mi objetivo:</label>
            <textarea class="wiz-textarea tall" v-model="objetivos.objetivo" placeholder="Incorporarme y consolidarme en un equipo..."></textarea>
          </div>
          <div class="field-group">
            <label><i class="bi bi-patch-check"></i> Valores profesionales</label>
            <textarea class="wiz-textarea tall" v-model="objetivos.valores" placeholder="– Liderazgo.&#10;– Compromiso.&#10;– Trabajo en equipo."></textarea>
          </div>
          <div class="field-group">
            <label><i class="bi bi-briefcase"></i> Conocimientos</label>
            <textarea class="wiz-textarea tall" v-model="objetivos.conocimientos" placeholder="Conocimientos en redes, seguridad..."></textarea>
          </div>
          <div class="field-group">
            <label><i class="bi bi-flag"></i> Idiomas</label>
            <textarea class="wiz-textarea tall" v-model="objetivos.idiomas" placeholder="– Español nativo&#10;– Inglés básico"></textarea>
          </div>
        </div>
      </div>

      <!-- ===== PASO 4: LOGROS ===== -->
      <div class="wiz-body paso3-body" v-else-if="paso === 4">
        <div class="paso3-grid">
          <div class="field-group">
            <label><i class="bi bi-award"></i> Certificados:</label>
            <textarea class="wiz-textarea tall" v-model="logros.certificados" placeholder="– CCNAv7: Introduction to Networks.&#10;– ..."></textarea>
          </div>
          <div class="field-group">
            <label><i class="bi bi-tools"></i> Habilidades:</label>
            <textarea class="wiz-textarea tall" v-model="logros.habilidades" placeholder="– Facilidad para expresarme..."></textarea>
          </div>
          <div class="field-group">
            <label><i class="bi bi-trophy"></i> Logros:</label>
            <textarea class="wiz-textarea tall" v-model="logros.logros" placeholder="Primer lugar en innovación..."></textarea>
          </div>
          <div class="field-group">
            <label><i class="bi bi-heart"></i> Proyectos de beneficio social:</label>
            <textarea class="wiz-textarea tall" v-model="logros.proyectos" placeholder="Asociación Juvenil..."></textarea>
          </div>
        </div>
      </div>

      <!-- ===== PASO 5: PREVIEW ===== -->
      <div class="wiz-body preview-body" v-else-if="paso === 5">
        <div id="cv-preview-render" class="cv-full-preview" :style="{ fontFamily: diseno.fuente, '--cv-color': diseno.color }">
          <!-- Sidebar izquierdo -->
          <div class="cvp-left" :style="{ backgroundColor: diseno.color }">
            <div class="cvp-foto-wrap">
              <img v-if="perfil.fotoUrl" :src="perfil.fotoUrl" class="cvp-foto" />
              <div v-else class="cvp-foto-placeholder"><i class="bi bi-person-fill"></i></div>
            </div>
            <div class="cvp-name">{{ perfil.nombre || 'Tu Nombre' }}</div>

            <div class="cvp-section-title">CONTACTO</div>
            <div class="cvp-item" v-if="perfil.direccion"><i class="bi bi-geo-alt"></i> {{ perfil.direccion }}</div>
            <div class="cvp-item" v-if="perfil.telefono"><i class="bi bi-telephone"></i> {{ perfil.telefono }}</div>
            <div class="cvp-item" v-if="perfil.email"><i class="bi bi-envelope"></i> {{ perfil.email }}</div>

            <div class="cvp-section-title mt">IDIOMAS</div>
            <div class="cvp-pre">{{ objetivos.idiomas }}</div>

            <div class="cvp-section-title mt">HABILIDADES</div>
            <div class="cvp-pre">{{ logros.habilidades }}</div>
          </div>

          <!-- Contenido derecho -->
          <div class="cvp-right">
            <div class="cvp-section-title-right" :style="{ color: diseno.color }">SOBRE MÍ</div>
            <p class="cvp-text">{{ perfil.sobreMi }}</p>

            <div class="cvp-section-title-right" :style="{ color: diseno.color }">EDUCACIÓN</div>
            <p class="cvp-text">{{ perfil.educacion }}</p>

            <div class="cvp-section-title-right" :style="{ color: diseno.color }">OBJETIVO</div>
            <p class="cvp-text">{{ objetivos.objetivo }}</p>

            <div class="cvp-section-title-right" :style="{ color: diseno.color }">VALORES PROFESIONALES</div>
            <p class="cvp-text pre">{{ objetivos.valores }}</p>

            <div class="cvp-section-title-right" :style="{ color: diseno.color }">CONOCIMIENTOS</div>
            <p class="cvp-text">{{ objetivos.conocimientos }}</p>

            <div class="cvp-section-title-right" :style="{ color: diseno.color }">CERTIFICADOS</div>
            <p class="cvp-text pre">{{ logros.certificados }}</p>

            <div class="cvp-section-title-right" :style="{ color: diseno.color }">LOGROS</div>
            <p class="cvp-text">{{ logros.logros }}</p>

            <div class="cvp-section-title-right" :style="{ color: diseno.color }">PROYECTOS DE BENEFICIO SOCIAL</div>
            <p class="cvp-text">{{ logros.proyectos }}</p>
          </div>
        </div>
        <p v-if="generando" class="gen-msg"><span class="spinner-border spinner-border-sm me-2"></span> Generando PDF...</p>
      </div>

      <!-- ===== BOTONES INFERIORES ===== -->
      <div class="wiz-footer">
        <button class="btn-wiz btn-cancelar" @click="handleAtras">
          {{ paso === 0 ? 'Cancelar' : 'Atrás' }}
        </button>
        <button class="btn-wiz btn-siguiente" @click="handleSiguiente" :disabled="generando">
          <span v-if="generando" class="spinner-border spinner-border-sm me-2"></span>
          {{ paso === 5 ? 'Descargar PDF' : (paso === 4 ? 'Vista Previa' : 'Siguiente') }}
          <span v-if="paso === 0"> &nbsp; Iniciar</span>
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import jsPDF from 'jspdf';
import html2canvas from 'html2canvas';
import axios from 'axios';

const emit = defineEmits(['cerrar']);

const paso = ref(0);
const generando = ref(false);

// Datos del wizard
const colores = [
  { val: '#010C67' },
  { val: '#67000F' },
  { val: '#2E6A8E' },
];

const fuentes = ['Montserrat', 'Inter', 'Lora', 'Roboto', 'Georgia'];

const diseno = ref({ color: '#67000F', fuente: 'Montserrat' });
const perfil = ref({ nombre: '', direccion: '', email: '', telefono: '', sobreMi: '', educacion: '', fotoUrl: '' });
const objetivos = ref({ objetivo: '', valores: '', conocimientos: '', idiomas: '' });
const logros = ref({ certificados: '', habilidades: '', logros: '', proyectos: '' });

const cargarFoto = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = (ev) => { perfil.value.fotoUrl = ev.target.result; };
  reader.readAsDataURL(file);
};

const handleAtras = () => {
  if (paso.value === 0) { emit('cerrar'); return; }
  paso.value--;
};

const handleSiguiente = async () => {
  if (paso.value < 5) { paso.value++; return; }
  // Descargar PDF
  await generarPDF();
};

const generarPDF = async () => {
  generando.value = true;
  try {
    const el = document.getElementById('cv-preview-render');
    const canvas = await html2canvas(el, { scale: 2, useCORS: true });
    const imgData = canvas.toDataURL('image/png');
    const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
    const pdfW = pdf.internal.pageSize.getWidth();
    const pdfH = (canvas.height * pdfW) / canvas.width;
    pdf.addImage(imgData, 'PNG', 0, 0, pdfW, pdfH);
    const nombre = perfil.value.nombre.replace(/\s+/g, '_') || 'CV';

    // 1. Descargar en el navegador
    pdf.save(`CV_${nombre}.pdf`);

    // 2. Guardar en el servidor vinculado al usuario
    const pdfBase64 = pdf.output('datauristring'); // base64
    const usuarioStr = localStorage.getItem('usuario');
    if (usuarioStr) {
      const usuario = JSON.parse(usuarioStr);
      await axios.post('/api/cv/guardar', {
        usuario_id: usuario.id,
        pdf_base64: pdfBase64,
        perfil: perfil.value,
        objetivos: objetivos.value,
        logros: logros.value,
        diseno: diseno.value,
      });
      alert('✅ CV guardado correctamente en el sistema.');
    }
  } catch (err) {
    console.error(err);
    alert('Error al generar el PDF.');
  } finally {
    generando.value = false;
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Inter:wght@400;500&family=Lora:wght@400;600&family=Roboto:wght@400;500&display=swap');

.cv-wizard-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.55);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cv-wizard-wrapper {
  background: #fff;
  width: 90%;
  max-width: 900px;
  max-height: 92vh;
  border-radius: 4px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,0.4);
}

/* Header */
.wiz-header {
  background: #010C67;
  color: #fff;
  font-family: 'Lora', serif;
  font-size: 20px;
  text-align: center;
  padding: 18px 30px;
  flex-shrink: 0;
}

/* Stepper */
.wiz-stepper {
  display: flex;
  align-items: center;
  padding: 18px 40px 8px;
  flex-shrink: 0;
}
.step-item { display: flex; align-items: center; }
.step-circle {
  width: 36px; height: 36px;
  border-radius: 50%;
  background: #ccc;
  color: #fff;
  font-weight: 700;
  font-size: 15px;
  display: flex; align-items: center; justify-content: center;
  transition: background 0.3s;
}
.step-circle.active { background: #010C67; }
.step-line { flex: 1; height: 3px; background: #ccc; min-width: 60px; transition: background 0.3s; }
.step-line.active { background: #010C67; }

/* Body */
.wiz-body {
  flex: 1;
  overflow-y: auto;
  padding: 24px 40px;
}

/* Paso 0 */
.intro-text { font-family: 'Lora', serif; font-size: 18px; text-align: center; margin-bottom: 20px; color: #222; }
.intro-img-wrap { display: flex; justify-content: center; }
.intro-img { width: 100%; max-width: 500px; border-radius: 6px; object-fit: cover; max-height: 260px; }

/* Paso 1 */
.paso1-body { display: flex; gap: 40px; align-items: flex-start; }
.paso1-left { flex: 0 0 220px; }
.paso1-right { flex: 1; display: flex; justify-content: center; }
.label-section { font-family: 'Lora', serif; font-size: 18px; font-weight: 700; margin-bottom: 12px; color: #111; }
.color-options { display: flex; gap: 12px; flex-wrap: wrap; }
.color-circle {
  width: 38px; height: 38px; border-radius: 50%;
  cursor: pointer; border: 3px solid transparent;
  transition: transform 0.2s, border-color 0.2s;
}
.color-circle:hover { transform: scale(1.1); }
.color-circle.selected { border-color: #aaa; transform: scale(1.15); }
.font-select {
  width: 100%; padding: 10px 14px;
  border: 1px solid #ccc; border-radius: 6px;
  font-size: 15px; cursor: pointer; background: #f5f5f5;
}

/* Mini preview */
.cv-mini-preview {
  width: 260px; height: 360px;
  border: 1px solid #ddd; border-radius: 4px;
  overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}
.mini-header {
  padding: 12px 10px; display: flex; align-items: center; gap: 10px; color: #fff;
}
.mini-avatar {
  width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.3);
}
.mini-name { font-weight: 700; font-size: 11px; }
.mini-job { font-size: 8px; opacity: 0.8; }
.mini-body { display: flex; padding: 8px; gap: 8px; height: calc(100% - 60px); }
.mini-col { flex: 1; display: flex; flex-direction: column; gap: 4px; }
.mini-section-title { font-size: 7px; font-weight: 700; border-bottom: 1px solid; padding-bottom: 2px; margin-bottom: 2px; }
.mt { margin-top: 6px; }
.mini-line { height: 4px; background: #ddd; border-radius: 2px; margin: 2px 0; }
.mini-line.short { width: 65%; }
.mini-skill { height: 5px; background: #eee; border-radius: 3px; margin: 3px 0; overflow: hidden; }
.skill-bar { height: 100%; border-radius: 3px; }

/* Paso 2 */
.paso2-body { display: flex; gap: 40px; }
.paso2-left { flex: 0 0 260px; }
.paso2-right { flex: 1; }
.foto-wrap {
  width: 90px; height: 90px; border-radius: 50%;
  background: #ddd; cursor: pointer; position: relative;
  overflow: visible; display: inline-flex; align-items: center; justify-content: center;
}
.foto-preview { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; }
.foto-placeholder { font-size: 50px; color: #555; }
.foto-camara {
  position: absolute; bottom: -2px; right: -6px;
  background: #010C67; color: #fff;
  border-radius: 50%; width: 28px; height: 28px;
  display: flex; align-items: center; justify-content: center; font-size: 14px;
}
.foto-label { font-size: 13px; color: #555; margin-top: 8px; }

/* Paso 3 / 4 */
.paso3-body { }
.paso3-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

/* Common inputs */
.field-group { margin-bottom: 16px; }
.field-group label { display: block; font-size: 14px; font-weight: 600; color: #222; margin-bottom: 5px; }
.wiz-input {
  width: 100%; padding: 9px 12px; border: 1px solid #bbb;
  border-radius: 6px; font-size: 14px; color: #333;
  background: #fff; box-sizing: border-box;
}
.wiz-textarea {
  width: 100%; padding: 9px 12px; border: 1px solid #bbb;
  border-radius: 6px; font-size: 14px; color: #333;
  resize: vertical; min-height: 90px; box-sizing: border-box;
}
.wiz-textarea.tall { min-height: 120px; }
.wiz-input:focus, .wiz-textarea:focus { outline: none; border-color: #010C67; }

/* Preview */
.preview-body { padding: 20px; background: #f0f0f0; overflow-y: auto; }
.cv-full-preview {
  display: flex; background: #fff;
  box-shadow: 0 4px 20px rgba(0,0,0,0.2);
  min-height: 800px; border-radius: 4px; overflow: hidden;
}
.cvp-left {
  width: 220px; flex-shrink: 0;
  padding: 28px 18px;
  color: #fff; display: flex; flex-direction: column; align-items: center;
}
.cvp-foto-wrap { margin-bottom: 12px; }
.cvp-foto { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid rgba(255,255,255,0.5); }
.cvp-foto-placeholder {
  width: 90px; height: 90px; border-radius: 50%;
  background: rgba(255,255,255,0.25); font-size: 48px;
  display: flex; align-items: center; justify-content: center;
}
.cvp-name { font-size: 15px; font-weight: 700; text-align: center; margin-bottom: 16px; }
.cvp-section-title {
  width: 100%; font-size: 11px; font-weight: 700; letter-spacing: 1px;
  text-align: center; border-top: 1px solid rgba(255,255,255,0.4);
  padding-top: 8px; margin-bottom: 8px;
}
.cvp-section-title.mt { margin-top: 12px; }
.cvp-item { font-size: 11px; margin-bottom: 5px; word-break: break-all; text-align: center; }
.cvp-pre { font-size: 10px; white-space: pre-wrap; text-align: left; width: 100%; }

.cvp-right { flex: 1; padding: 28px 24px; }
.cvp-section-title-right {
  font-size: 12px; font-weight: 700; letter-spacing: 1px;
  border-bottom: 2px solid currentColor; padding-bottom: 4px; margin-bottom: 8px; margin-top: 16px;
}
.cvp-section-title-right:first-child { margin-top: 0; }
.cvp-text { font-size: 12px; color: #333; margin: 0 0 4px; line-height: 1.5; }
.cvp-text.pre { white-space: pre-wrap; }

.gen-msg { text-align: center; color: #555; margin-top: 16px; font-size: 14px; }

/* Footer */
.wiz-footer {
  display: flex; justify-content: space-between; align-items: center;
  padding: 16px 40px; border-top: 1px solid #e0e0e0; flex-shrink: 0;
  background: #fff;
}
.btn-wiz {
  font-family: 'Lora', serif; font-size: 18px; font-weight: 600;
  border: none; border-radius: 10px; padding: 10px 40px; cursor: pointer;
  transition: all 0.25s;
}
.btn-cancelar { background: #67000F; color: #fff; }
.btn-cancelar:hover { background: #4a0009; }
.btn-siguiente { background: #010C67; color: #fff; }
.btn-siguiente:hover { background: #01084a; }
.btn-siguiente:disabled { opacity: 0.7; cursor: not-allowed; }
</style>
