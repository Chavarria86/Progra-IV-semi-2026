/**
 * componentes/inscripciones.js
 * Componente Vue para el formulario de registro/edición de Inscripciones
 * Relaciona una Matrícula con una Materia y un Docente
 */

'use strict';

const inscripciones = {
    name: 'inscripciones',
    props: ['forms'],
    emits: ['buscar'],
    template: `
    <div v-draggable class="card shadow" style="width:500px; top:80px; left:30px;">
        <div class="card-header d-flex justify-content-between align-items-center bg-danger text-white">
            <span>📌 Gestión de Inscripciones</span>
            <button class="btn btn-sm btn-light" @click="$emit('buscar')">
                🔍 Buscar
            </button>
        </div>
        <div class="card-body">
            <form @submit.prevent>
                <div class="mb-2">
                    <label class="form-label fw-semibold">Matrícula (Alumno / Ciclo)</label>
                    <select class="form-select" v-model="form.id_Matricula">
                        <option value="">-- Seleccione matrícula --</option>
                        <option v-for="m in matriculas" :key="m.id_Matricula" :value="m.id_Matricula">
                            {{ m.nombre_alumno }} — {{ m.ciclo }}
                        </option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold">Materia</label>
                    <select class="form-select" v-model="form.id_Materia">
                        <option value="">-- Seleccione materia --</option>
                        <option v-for="mat in materias" :key="mat.id_Materia" :value="mat.id_Materia">
                            {{ mat.codigo }} - {{ mat.nombre }}
                        </option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Docente</label>
                    <select class="form-select" v-model="form.id_Docente">
                        <option value="">-- Seleccione docente --</option>
                        <option v-for="d in docentes_lista" :key="d.id_Docente" :value="d.id_Docente">
                            {{ d.codigo }} - {{ d.nombre }}
                        </option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-danger w-100" @click="guardarInscripcion">
                        💾 {{ form.id_Inscripcion ? 'Actualizar' : 'Inscribir' }}
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
            matriculas:    [],
            materias:      [],
            docentes_lista:[],
            form: {
                id_Inscripcion: '',
                id_Matricula:   '',
                id_Materia:     '',
                id_Docente:     ''
            }
        };
    },
    async mounted() {
        await this.cargarDependencias();
    },
    methods: {
        limpiar() {
            this.form = { id_Inscripcion: '', id_Matricula: '', id_Materia: '', id_Docente: '' };
        },

        async cargarDependencias() {
            try {
                [this.matriculas, this.materias, this.docentes_lista] = await Promise.all([
                    dbQuery(`
                        SELECT m.id_Matricula, a.nombre AS nombre_alumno, m.ciclo
                        FROM matriculas m
                        INNER JOIN alumnos a ON m.id_Alumno = a.id_Alumno
                        ORDER BY a.nombre ASC, m.ciclo DESC
                    `),
                    dbQuery(`SELECT id_Materia, codigo, nombre FROM materias ORDER BY nombre ASC`),
                    dbQuery(`SELECT id_Docente, codigo, nombre FROM docentes ORDER BY nombre ASC`)
                ]);
            } catch (e) {
                console.error(e);
                alertify.error('Error al cargar datos: ' + e.message);
            }
        },

        validar() {
            const { id_Matricula, id_Materia, id_Docente } = this.form;
            if (!id_Matricula) { alertify.warning('Seleccione una matrícula.'); return false; }
            if (!id_Materia)   { alertify.warning('Seleccione una materia.');   return false; }
            if (!id_Docente)   { alertify.warning('Seleccione un docente.');    return false; }
            return true;
        },

        async guardarInscripcion() {
            if (!this.validar()) return;

            try {
                if (this.form.id_Inscripcion) {
                    await dbExec(
                        `UPDATE inscripciones SET id_Matricula=?, id_Materia=?, id_Docente=? WHERE id_Inscripcion=?`,
                        [this.form.id_Matricula, this.form.id_Materia,
                         this.form.id_Docente,   this.form.id_Inscripcion]
                    );
                    alertify.success('Inscripción actualizada correctamente.');
                } else {
                    // Verificar duplicado
                    const existe = await dbQuery(
                        `SELECT id_Inscripcion FROM inscripciones
                         WHERE id_Matricula=? AND id_Materia=? AND id_Docente=?`,
                        [this.form.id_Matricula, this.form.id_Materia, this.form.id_Docente]
                    );
                    if (existe.length > 0) {
                        alertify.warning('Esta inscripción ya existe.');
                        return;
                    }
                    const id_Inscripcion = uuid.v4();
                    await dbExec(
                        `INSERT INTO inscripciones (id_Inscripcion, id_Matricula, id_Materia, id_Docente)
                         VALUES (?, ?, ?, ?)`,
                        [id_Inscripcion, this.form.id_Matricula, this.form.id_Materia, this.form.id_Docente]
                    );
                    alertify.success('Inscripción guardada correctamente.');
                }
                this.limpiar();
                this.$emit('buscar');
            } catch (e) {
                console.error(e);
                alertify.error('Error al guardar inscripción: ' + e.message);
            }
        },

        modificarInscripcion(data) {
            this.form = { ...data };
            this.cargarDependencias();
        }
    }
};
