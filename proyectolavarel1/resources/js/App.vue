<template>
  <div>
    <nav class="navbar navbar-expand-lg bg-dark navbar-dark shadow-sm mb-4">
      <div class="container-fluid">
        <a class="navbar-brand fw-bold text-warning" href="#" @click.prevent="ir('alumnos')">SISTEMA ACADÉMICO</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <div class="navbar-nav ms-auto">
            <a class="nav-link" :class="{active: modulo==='alumnos'}"       href="#" @click.prevent="ir('alumnos')">Alumnos</a>
            <a class="nav-link" :class="{active: modulo==='materias'}"      href="#" @click.prevent="ir('materias')">Materias</a>
            <a class="nav-link" :class="{active: modulo==='docentes'}"      href="#" @click.prevent="ir('docentes')">Docentes</a>
            <a class="nav-link" :class="{active: modulo==='matriculas'}"    href="#" @click.prevent="ir('matriculas')">Matrículas</a>
            <a class="nav-link" :class="{active: modulo==='inscripciones'}" href="#" @click.prevent="ir('inscripciones')">Inscripciones</a>
          </div>
        </div>
      </div>
    </nav>

    <div class="container-fluid px-4">

      <!-- DASHBOARD -->
      <template v-if="modulo==='dashboard'">
        <DashboardComponent @ir="ir" />
      </template>

      <!-- ALUMNOS -->
      <template v-if="modulo==='alumnos'">
        <AlumnoFormComponent
          v-if="modoForm"
          :alumnoEdit="editData"
          @cerrar="cerrar"
          @saved="guardado"
        />
        <AlumnoComponent v-else ref="listaRef" @nuevo="nuevo" @modificar="modificar" />
      </template>

      <!-- DOCENTES -->
      <template v-else-if="modulo==='docentes'">
        <DocenteFormComponent
          v-if="modoForm"
          :docenteEdit="editData"
          @cerrar="cerrar"
          @saved="guardado"
        />
        <DocenteComponent v-else ref="listaRef" @nuevo="nuevo" @modificar="modificar" />
      </template>

      <!-- MATERIAS -->
      <template v-else-if="modulo==='materias'">
        <MateriaFormComponent
          v-if="modoForm"
          :materiaEdit="editData"
          @cerrar="cerrar"
          @saved="guardado"
        />
        <MateriaComponent v-else ref="listaRef" @nuevo="nuevo" @modificar="modificar" />
      </template>

      <!-- MATRÍCULAS -->
      <template v-else-if="modulo==='matriculas'">
        <MatriculaFormComponent
          v-if="modoForm"
          :matriculaEdit="editData"
          @cerrar="cerrar"
          @saved="guardado"
        />
        <MatriculaComponent v-else ref="listaRef" @nuevo="nuevo" @modificar="modificar" />
      </template>

      <!-- INSCRIPCIONES -->
      <template v-else-if="modulo==='inscripciones'">
        <InscripcionFormComponent
          v-if="modoForm"
          :inscripcionEdit="editData"
          @cerrar="cerrar"
          @saved="guardado"
        />
        <InscripcionComponent v-else ref="listaRef" @nuevo="nuevo" @modificar="modificar" />
      </template>

    </div>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue';

import DashboardComponent  from './components/DashboardComponent.vue';
import AlumnoComponent     from './components/AlumnoComponent.vue';
import AlumnoFormComponent from './components/AlumnoFormComponent.vue';
import DocenteComponent     from './components/DocenteComponent.vue';
import DocenteFormComponent from './components/DocenteFormComponent.vue';
import MateriaComponent     from './components/MateriaComponent.vue';
import MateriaFormComponent from './components/MateriaFormComponent.vue';
import MatriculaComponent     from './components/MatriculaComponent.vue';
import MatriculaFormComponent from './components/MatriculaFormComponent.vue';
import InscripcionComponent     from './components/InscripcionComponent.vue';
import InscripcionFormComponent from './components/InscripcionFormComponent.vue';

const modulo  = ref('alumnos');
const modoForm = ref(false);
const editData = ref(null);
const listaRef = ref(null);

function ir(m) {
  modulo.value  = m;
  modoForm.value = false;
  editData.value = null;
}

function nuevo() {
  editData.value = null;
  modoForm.value = true;
}

function modificar(data) {
  editData.value = data;
  modoForm.value = true;
}

function cerrar() {
  modoForm.value = false;
  editData.value = null;
}

async function guardado() {
  modoForm.value = false;
  editData.value = null;
  // Esperar a que Vue monte el componente lista y luego recargar
  await nextTick();
  if (listaRef.value && listaRef.value.cargar) {
    listaRef.value.cargar();
  }
}
ir('alumnos');
</script>
