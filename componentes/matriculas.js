/**
 * componentes/matriculas.js
 * Componente Vue para el formulario de registro/edición de Matrículas
 * Relaciona un Alumno con un ciclo académico
 */

'use strict';

const matriculas = {
    name: 'matriculas',
    props: ['forms'],
    emits: ['buscar'],
    template: `
    <div v-draggable class="card shadow" style="width:500px; top:80px; left:30px;">
        <div class="card-header d-flex justify-content-between align-items-center bg-info text-white">
            <span>📝 Gestión de Matrículas</span>
            <button class="btn btn-sm btn-light" @click="$emit('buscar')">
                🔍 Buscar
            </button>
        </div>
        <div class="card-body">
            <form @submit.prevent>
                <div class="mb-2">
                    <label class="form-label fw-semibold">Alumno</label>
                    <select class="form-select" v-model="form.id_Alumno">
                        <option value="">-- Seleccione alumno --</option>
                        <option v-for="a in alumnos" :key="a.id_Alumno" :value="a.id_Alumno">
                            {{ a.codigo }} - {{ a.nombre }}
                        </option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold">Ciclo académico</label>
                    <input type="text" class="form-control" v-model="form.ciclo"
                           placeholder="Ej: 2026-I" maxlength="20" />
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Fecha de matrícula</label>
                    <input type="date" class="form-control" v-model="form.fecha" />
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-info text-white w-100" @click="guardarMatricula">
                        💾 {{ form.id_Matricula ? 'Actualizar' : 'Guardar' }}
                    </button>
                    <button class="btn btn-secondary w-100" @click="limpiar">
                        🗑️ Limpiar
                    </button>
                </div>
            </form>
        </div>
    </div>
    `,
    data() {
        return {
            alumnos: [],
            form: {
                id_Matricula: '',
                id_Alumno:    '',
                ciclo:        '',
                fecha:        new Date().toISOString().slice(0, 10)
            }
        };
    },
    async mounted() {
        await this.cargarAlumnos();
    },
    methods: {
        limpiar() {
            this.form = {
                id_Matricula: '',
                id_Alumno:    '',
                ciclo:        '',
                fecha:        new Date().toISOString().slice(0, 10)
            };
        },

        async cargarAlumnos() {
            try {
                this.alumnos = await dbQuery(`SELECT id_Alumno, codigo, nombre FROM alumnos ORDER BY nombre ASC`);
            } catch (e) {
                console.error(e);
            }
        },

        validar() {
            const { id_Alumno, ciclo, fecha } = this.form;
            if (!id_Alumno) { alertify.warning('Seleccione un alumno.');          return false; }
            if (!ciclo.trim()) { alertify.warning('El ciclo es requerido.');      return false; }
            if (!fecha)      { alertify.warning('La fecha es requerida.');        return false; }
            return true;
        },

        async guardarMatricula() {
            if (!this.validar()) return;

            try {
                if (this.form.id_Matricula) {
                    await dbExec(
                        `UPDATE matriculas SET id_Alumno=?, ciclo=?, fecha=? WHERE id_Matricula=?`,
                        [this.form.id_Alumno, this.form.ciclo, this.form.fecha, this.form.id_Matricula]
                    );
                    alertify.success('Matrícula actualizada correctamente.');
                } else {
                    const id_Matricula = uuid.v4();
                    await dbExec(
                        `INSERT INTO matriculas (id_Matricula, id_Alumno, ciclo, fecha) VALUES (?, ?, ?, ?)`,
                        [id_Matricula, this.form.id_Alumno, this.form.ciclo, this.form.fecha]
                    );
                    alertify.success('Matrícula guardada correctamente.');
                }
                this.limpiar();
                this.$emit('buscar');
            } catch (e) {
                console.error(e);
                alertify.error('Error al guardar matrícula: ' + e.message);
            }
        },

        modificarMatricula(data) {
            this.form = { ...data };
        }
    }
};
